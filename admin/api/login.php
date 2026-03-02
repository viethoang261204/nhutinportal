<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

const MAX_FAILED_ATTEMPTS = 5;
const FAILED_WINDOW_MINUTES = 15;
const FAILED_DELAY_MICROSECONDS = 300000;

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_name('nhutin_admin_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

function respondJson(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function getClientIp(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function getRequestBody(): array
{
    $contentType = (string) ($_SERVER['CONTENT_TYPE'] ?? '');
    if (stripos($contentType, 'application/json') !== false) {
        $rawBody = file_get_contents('php://input');
        $decodedBody = json_decode($rawBody ?: '', true);
        return is_array($decodedBody) ? $decodedBody : [];
    }

    return is_array($_POST) ? $_POST : [];
}

function getOrCreateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $providedToken): bool
{
    $sessionToken = isset($_SESSION['csrf_token']) && is_string($_SESSION['csrf_token'])
        ? $_SESSION['csrf_token']
        : '';

    return $sessionToken !== '' && $providedToken !== '' && hash_equals($sessionToken, $providedToken);
}

function ensureAuthSchema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            avatar_url VARCHAR(500) NULL,
            role VARCHAR(50) NOT NULL DEFAULT 'admin',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    try {
        $chk = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admins' AND COLUMN_NAME = 'avatar_url'");
        if ($chk && (int) $chk->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE admins ADD COLUMN avatar_url VARCHAR(500) NULL AFTER password");
        }
    } catch (Throwable $e) {}

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS admin_login_attempts (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            identifier CHAR(64) NOT NULL,
            is_success TINYINT(1) NOT NULL DEFAULT 0,
            attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_identifier_time (identifier, attempted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $seedStmt = $pdo->prepare(
        "INSERT INTO admins (name, email, password, role)
         VALUES (:name, :email, :password, :role)
         ON DUPLICATE KEY UPDATE id = id"
    );
    $seedStmt->execute([
        'name' => 'Administrator',
        'email' => 'admin@gmail.com',
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'role' => 'admin',
    ]);
}

function isRateLimited(PDO $pdo, string $identifier): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS failed_count
         FROM admin_login_attempts
         WHERE identifier = :identifier
           AND is_success = 0
           AND attempted_at >= (NOW() - INTERVAL ' . FAILED_WINDOW_MINUTES . ' MINUTE)'
    );
    $stmt->execute(['identifier' => $identifier]);
    $result = $stmt->fetch();
    $failedCount = (int) ($result['failed_count'] ?? 0);

    return $failedCount >= MAX_FAILED_ATTEMPTS;
}

function saveLoginAttempt(PDO $pdo, string $identifier, bool $isSuccess): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO admin_login_attempts (identifier, is_success) VALUES (:identifier, :is_success)'
    );
    $stmt->execute([
        'identifier' => $identifier,
        'is_success' => $isSuccess ? 1 : 0,
    ]);
}

function clearFailedAttempts(PDO $pdo, string $identifier): void
{
    $stmt = $pdo->prepare(
        'DELETE FROM admin_login_attempts WHERE identifier = :identifier AND is_success = 0'
    );
    $stmt->execute(['identifier' => $identifier]);
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$action = (string) ($_GET['action'] ?? '');

if ($method === 'GET' && $action === 'csrf') {
    respondJson(200, [
        'success' => true,
        'csrf_token' => getOrCreateCsrfToken(),
    ]);
}

if ($method !== 'POST') {
    respondJson(405, [
        'success' => false,
        'message' => 'Method not allowed.',
    ]);
}

$input = getRequestBody();
$email = strtolower(trim((string) ($input['email'] ?? '')));
$password = (string) ($input['password'] ?? '');
$csrfToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($input['csrf_token'] ?? ''));

if (!verifyCsrfToken($csrfToken)) {
    respondJson(419, [
        'success' => false,
        'message' => 'Phiên đăng nhập hết hạn, vui lòng thử lại.',
    ]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || strlen($password) > 255) {
    usleep(FAILED_DELAY_MICROSECONDS);
    respondJson(422, [
        'success' => false,
        'message' => 'Dữ liệu đăng nhập không hợp lệ.',
    ]);
}

try {
    $pdo = getDbConnection();
    ensureAuthSchema($pdo);
} catch (Throwable $e) {
    error_log('Login DB init failed: ' . $e->getMessage());
    respondJson(500, [
        'success' => false,
        'message' => 'Hệ thống tạm thời đứt kết nối, vui lòng thử lại sau.',
    ]);
}

$identifier = hash('sha256', getClientIp() . '|' . $email);
if (isRateLimited($pdo, $identifier)) {
    respondJson(429, [
        'success' => false,
        'message' => 'Bạn đã nhập sai quá nhiều lần, vui lòng thử lại sau.',
    ]);
}

$adminData = null;
$isAdminLogin = false;

try {
    $stmt = $pdo->prepare(
        'SELECT id, name, email, password, avatar_url, role FROM admins WHERE email = :email LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch();
    if ($admin) {
        $storedPassword = (string) ($admin['password'] ?? '');
        $isPasswordValid = password_verify($password, $storedPassword);
        if (!$isPasswordValid && $storedPassword === $password) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE admins SET password = :password WHERE id = :id')->execute(['password' => $newHash, 'id' => $admin['id']]);
            $isPasswordValid = true;
        } elseif ($isPasswordValid && password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE admins SET password = :password WHERE id = :id')->execute(['password' => $newHash, 'id' => $admin['id']]);
        }
        if ($isPasswordValid) {
            $avatar = trim((string) ($admin['avatar_url'] ?? ''));
            $adminData = [
                'id' => (int) $admin['id'],
                'name' => (string) ($admin['name'] ?? 'Administrator'),
                'email' => (string) ($admin['email'] ?? $email),
                'avatar_url' => $avatar !== '' ? $avatar : null,
                'role' => 'admin',
            ];
            $isAdminLogin = true;
        }
    }
} catch (Throwable $e) {
    error_log('Login admin query failed: ' . $e->getMessage());
}

if (!$adminData) {
    try {
        $stmt = $pdo->prepare(
            "SELECT id, username, email, password_hash, full_name, avatar_url, role FROM users WHERE email = :email AND role = 'staff' AND (is_active = 1 OR is_active IS NULL) LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $staff = $stmt->fetch();
        if ($staff && password_verify($password, (string) ($staff['password_hash'] ?? ''))) {
            $name = trim((string) ($staff['full_name'] ?? $staff['username'] ?? ''));
            if ($name === '') $name = (string) ($staff['username'] ?? 'Staff');
            $avatar = trim((string) ($staff['avatar_url'] ?? ''));
            $adminData = [
                'id' => (int) $staff['id'],
                'name' => $name,
                'email' => (string) ($staff['email'] ?? $email),
                'avatar_url' => $avatar !== '' ? $avatar : null,
                'role' => 'staff',
            ];
            try {
                $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id")->execute(['id' => $staff['id']]);
            } catch (Throwable $ignored) {}
        }
    } catch (Throwable $e) {
        error_log('Login staff query failed: ' . $e->getMessage());
    }
}

if (!$adminData) {
    saveLoginAttempt($pdo, $identifier, false);
    usleep(FAILED_DELAY_MICROSECONDS);
    respondJson(401, [
        'success' => false,
        'message' => 'Sai email hoặc mật khẩu.',
    ]);
}

clearFailedAttempts($pdo, $identifier);
saveLoginAttempt($pdo, $identifier, true);

session_regenerate_id(true);

$_SESSION['nhutin_admin_logged_in'] = true;
$_SESSION['nhutin_admin'] = $adminData;
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

respondJson(200, [
    'success' => true,
    'message' => 'Đăng nhập thành công.',
    'admin' => $adminData,
]);

