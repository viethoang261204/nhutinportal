<?php
declare(strict_types=1);

require_once __DIR__ . '/../../admin/config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
session_name('nhutin_portal_session');
session_set_cookie_params([
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function respondJson(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function getRequestBody(): array
{
    $contentType = (string) ($_SERVER['CONTENT_TYPE'] ?? '');
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw ?: '', true);
        return is_array($decoded) ? $decoded : [];
    }
    return is_array($_POST) ? $_POST : [];
}

function ensureUsersSchema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NULL,
            phone VARCHAR(20) NULL,
            avatar_url VARCHAR(500) NULL,
            role ENUM('admin', 'staff', 'customer') DEFAULT 'customer',
            customer_id INT NULL,
            is_active TINYINT(1) DEFAULT 1,
            last_login_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ($method !== 'POST') {
    respondJson(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$input = getRequestBody();
$email = strtolower(trim((string) ($input['email'] ?? '')));
$password = (string) ($input['password'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respondJson(400, ['success' => false, 'message' => 'Email không hợp lệ.']);
}
if ($password === '') {
    respondJson(400, ['success' => false, 'message' => 'Vui lòng nhập mật khẩu.']);
}

try {
    $pdo = getDbConnection();
    ensureUsersSchema($pdo);
} catch (Throwable $e) {
    error_log('Portal login DB: ' . $e->getMessage());
    respondJson(500, ['success' => false, 'message' => 'Lỗi kết nối. Vui lòng thử lại sau.']);
}

// 1. Tìm customer theo email (chỉ active hoặc pending)
$stmt = $pdo->prepare(
    "SELECT id, customer_code, company_name, email, phone, contact_person, logo_url, status, assigned_staff_id 
     FROM customers 
     WHERE LOWER(TRIM(email)) = :email AND status IN ('active', 'pending') 
     LIMIT 1"
);
$stmt->execute(['email' => $email]);
$customer = $stmt->fetch();

if (!$customer) {
    respondJson(401, ['success' => false, 'message' => 'Email chưa được đăng ký trong hệ thống hoặc tài khoản chưa được kích hoạt.']);
}

$customerId = (int) $customer['id'];
$companyName = (string) ($customer['company_name'] ?? '');
$contactPerson = trim((string) ($customer['contact_person'] ?? ''));
$displayName = $contactPerson !== '' ? $contactPerson : $companyName;
$logoUrl = trim((string) ($customer['logo_url'] ?? ''));
$customerCode = trim((string) ($customer['customer_code'] ?? ''));

// 2. Tìm user (email + role=customer)
$stmt = $pdo->prepare(
    "SELECT id, username, email, password_hash, full_name, customer_id, avatar_url 
     FROM users 
     WHERE LOWER(TRIM(email)) = :email AND role = 'customer' AND (is_active = 1 OR is_active IS NULL) 
     LIMIT 1"
);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if ($user) {
    // User đã tồn tại → verify password
    if (!password_verify($password, (string) ($user['password_hash'] ?? ''))) {
        respondJson(401, ['success' => false, 'message' => 'Sai mật khẩu.']);
    }
    $userId = (int) $user['id'];
} else {
    // Chưa có user → tạo mới (dùng email từ customers làm tài khoản, mật khẩu lần đầu = đặt luôn)
    $username = $email;
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $ins = $pdo->prepare(
            "INSERT INTO users (username, email, password_hash, full_name, phone, role, customer_id, is_active) 
             VALUES (:username, :email, :password_hash, :full_name, :phone, 'customer', :customer_id, 1)"
        );
        $ins->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'full_name' => $displayName,
            'phone' => (string) ($customer['phone'] ?? ''),
            'customer_id' => $customerId,
        ]);
        $userId = (int) $pdo->lastInsertId();
    } catch (Throwable $e) {
        if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
            respondJson(409, ['success' => false, 'message' => 'Email đã được sử dụng bởi tài khoản khác.']);
        }
        error_log('Portal create user: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể tạo tài khoản. Vui lòng thử lại.']);
    }
}

// Cập nhật last_login
try {
    $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id")->execute(['id' => $userId]);
} catch (Throwable $ignored) {}

// Lấy thông tin CSKH nếu có assigned_staff_id
$staffName = '';
$staffPhone = '';
$staffEmail = '';
$staffAvatar = '';
$assignedStaffId = (int) ($customer['assigned_staff_id'] ?? 0);
if ($assignedStaffId > 0) {
    try {
        $staffStmt = $pdo->prepare(
            "SELECT full_name, phone, email, avatar_url FROM users WHERE id = :id AND role = 'staff' LIMIT 1"
        );
        $staffStmt->execute(['id' => $assignedStaffId]);
        $staff = $staffStmt->fetch();
        if ($staff) {
            $staffName = trim((string) ($staff['full_name'] ?? $staff['email'] ?? ''));
            $staffPhone = (string) ($staff['phone'] ?? '');
            $staffEmail = (string) ($staff['email'] ?? '');
            $staffAvatar = trim((string) ($staff['avatar_url'] ?? ''));
        }
    } catch (Throwable $ignored) {}
}

session_regenerate_id(true);
$_SESSION['nhutin_portal_logged_in'] = true;
$_SESSION['nhutin_portal_user'] = [
    'id' => $userId,
    'email' => $email,
    'name' => $displayName,
    'company_name' => $companyName,
    'customer_code' => $customerCode,
    'customer_id' => $customerId,
    'avatar_url' => $logoUrl,
    'assigned_staff' => [
        'name' => $staffName,
        'phone' => $staffPhone,
        'email' => $staffEmail,
        'avatar' => $staffAvatar,
    ],
];

respondJson(200, [
    'success' => true,
    'message' => 'Đăng nhập thành công.',
    'user' => $_SESSION['nhutin_portal_user'],
]);
