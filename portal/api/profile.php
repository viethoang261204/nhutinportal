<?php
declare(strict_types=1);

require_once __DIR__ . '/../../admin/config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
session_name('nhutin_portal_session');
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

const MAX_AVATAR_SIZE_BYTES = 5 * 1024 * 1024; // 5MB

if (empty($_SESSION['nhutin_portal_logged_in']) || empty($_SESSION['nhutin_portal_user'])) {
    respondJson(401, ['success' => false, 'message' => 'Vui lòng đăng nhập.']);
}

$user = $_SESSION['nhutin_portal_user'];
$customerId = (int) ($user['customer_id'] ?? 0);
$userId = (int) ($user['id'] ?? 0);

if ($customerId <= 0 || $userId <= 0) {
    respondJson(403, ['success' => false, 'message' => 'Không có quyền truy cập.']);
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$action = (string) ($_GET['action'] ?? '');

// --- Upload avatar ---
if ($method === 'POST' && $action === 'upload_avatar') {
    try {
        if (!isset($_FILES['avatar']) || !is_array($_FILES['avatar'])) {
            respondJson(422, ['success' => false, 'message' => 'Vui lòng chọn ảnh để tải lên.']);
        }
        $file = $_FILES['avatar'];
        if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            respondJson(422, ['success' => false, 'message' => 'Upload ảnh thất bại.']);
        }
        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            respondJson(422, ['success' => false, 'message' => 'File upload không hợp lệ.']);
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > MAX_AVATAR_SIZE_BYTES) {
            respondJson(422, ['success' => false, 'message' => 'Ảnh phải nhỏ hơn hoặc bằng 5MB.']);
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? (string) finfo_file($finfo, $tmpPath) : '';
        if ($finfo) finfo_close($finfo);
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $ext = $extMap[strtolower($mimeType)] ?? null;
        if (!$ext || !in_array(strtolower($mimeType), $allowedMime, true)) {
            respondJson(422, ['success' => false, 'message' => 'Định dạng ảnh không hợp lệ (jpg, png, webp, gif).']);
        }
        $imgDir = realpath(__DIR__ . '/../../img');
        if ($imgDir === false) {
            throw new RuntimeException('Không tìm thấy thư mục img.');
        }
        $avatarDir = $imgDir . DIRECTORY_SEPARATOR . 'customer-avatars';
        if (!is_dir($avatarDir)) {
            if (!mkdir($avatarDir, 0775, true) && !is_dir($avatarDir)) {
                throw new RuntimeException('Không thể tạo thư mục lưu ảnh.');
            }
        }
        $filename = 'avatar_' . date('YmdHis') . '_' . random_int(1000, 9999) . '.' . $ext;
        $targetPath = $avatarDir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($tmpPath, $targetPath)) {
            throw new RuntimeException('Không thể lưu file ảnh.');
        }
        $avatarUrl = '../img/customer-avatars/' . $filename;

        $pdo = getDbConnection();
        $pdo->prepare('UPDATE customers SET logo_url = :url WHERE id = :id')->execute(['url' => $avatarUrl, 'id' => $customerId]);
        $pdo->prepare('UPDATE users SET avatar_url = :url WHERE id = :id')->execute(['url' => $avatarUrl, 'id' => $userId]);

        $_SESSION['nhutin_portal_user']['avatar_url'] = $avatarUrl;
        respondJson(200, ['success' => true, 'message' => 'Cập nhật ảnh đại diện thành công.', 'avatar_url' => $avatarUrl]);
    } catch (Throwable $e) {
        error_log('Portal avatar upload: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể tải ảnh lên.']);
    }
}

// --- Change password ---
if ($method === 'POST' && $action === 'change_password') {
    $body = getRequestBody();
    $currentPassword = (string) ($body['current_password'] ?? '');
    $newPassword = (string) ($body['new_password'] ?? '');
    $confirmPassword = (string) ($body['confirm_password'] ?? '');

    if ($currentPassword === '') {
        respondJson(422, ['success' => false, 'message' => 'Vui lòng nhập mật khẩu hiện tại.']);
    }
    if (strlen($newPassword) < 6) {
        respondJson(422, ['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự.']);
    }
    if ($newPassword !== $confirmPassword) {
        respondJson(422, ['success' => false, 'message' => 'Mật khẩu mới và xác nhận không khớp.']);
    }

    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id AND role = "customer" LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            respondJson(404, ['success' => false, 'message' => 'Không tìm thấy tài khoản.']);
        }
        if (!password_verify($currentPassword, (string) ($row['password_hash'] ?? ''))) {
            respondJson(401, ['success' => false, 'message' => 'Mật khẩu hiện tại không đúng.']);
        }
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id')->execute(['hash' => $hash, 'id' => $userId]);
        respondJson(200, ['success' => true, 'message' => 'Đổi mật khẩu thành công.']);
    } catch (Throwable $e) {
        error_log('Portal change password: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể đổi mật khẩu.']);
    }
}

// --- Update profile ---
if ($method === 'PUT' || ($method === 'POST' && $action === 'update')) {
    $body = $method === 'PUT' ? getRequestBody() : array_merge($_POST, $_FILES ? [] : []);
    $contactPerson = trim((string) ($body['contact_person'] ?? ''));
    $phone = trim((string) ($body['phone'] ?? ''));
    $companyName = trim((string) ($body['company_name'] ?? ''));
    $address = trim((string) ($body['address'] ?? ''));

    try {
        $pdo = getDbConnection();
        $pdo->prepare(
            'UPDATE customers SET contact_person = :cp, phone = :phone, company_name = :cn, address = :addr WHERE id = :id'
        )->execute([
            'cp' => $contactPerson !== '' ? $contactPerson : null,
            'phone' => $phone !== '' ? $phone : null,
            'cn' => $companyName !== '' ? $companyName : null,
            'addr' => $address !== '' ? $address : null,
            'id' => $customerId,
        ]);
        $pdo->prepare(
            'UPDATE users SET full_name = :fn, phone = :phone WHERE id = :id'
        )->execute([
            'fn' => $contactPerson !== '' ? $contactPerson : $companyName,
            'phone' => $phone !== '' ? $phone : null,
            'id' => $userId,
        ]);

        $_SESSION['nhutin_portal_user']['name'] = $contactPerson ?: $companyName;
        $_SESSION['nhutin_portal_user']['company_name'] = $companyName;

        respondJson(200, ['success' => true, 'message' => 'Cập nhật thông tin thành công.']);
    } catch (Throwable $e) {
        error_log('Portal profile update: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể cập nhật.']);
    }
}

// --- GET profile ---
if ($method === 'GET') {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare(
            'SELECT c.id, c.customer_code, c.company_name, c.contact_person, c.email, c.phone, c.address, c.logo_url
             FROM customers c
             WHERE c.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $customerId]);
        $customer = $stmt->fetch();
        if (!$customer) {
            respondJson(404, ['success' => false, 'message' => 'Không tìm thấy thông tin.']);
        }
        $avatar = trim((string) ($customer['logo_url'] ?? ''));
        $profile = [
            'id' => (int) $customer['id'],
            'customer_code' => (string) ($customer['customer_code'] ?? ''),
            'company_name' => (string) ($customer['company_name'] ?? ''),
            'contact_person' => (string) ($customer['contact_person'] ?? ''),
            'email' => (string) ($customer['email'] ?? ''),
            'phone' => (string) ($customer['phone'] ?? ''),
            'address' => (string) ($customer['address'] ?? ''),
            'avatar_url' => $avatar !== '' ? $avatar : null,
        ];
        respondJson(200, ['success' => true, 'data' => $profile]);
    } catch (Throwable $e) {
        error_log('Portal profile get: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể tải thông tin.']);
    }
}

respondJson(405, ['success' => false, 'message' => 'Method not allowed.']);
