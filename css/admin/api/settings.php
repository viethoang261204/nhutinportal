<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth-helper.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
session_name('nhutin_admin_session');
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

function ensureSettingsSchema(PDO $pdo): void
{
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS site_settings (
                id SERIAL PRIMARY KEY,
                setting_key VARCHAR(100) NOT NULL UNIQUE,
                setting_value TEXT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        );
    } catch (Throwable $e) {}
}

const DEFAULT_SETTINGS = [
    'company_name' => 'CÔNG TY CỔ PHẦN NHƯ TÍN',
    'contact_email' => 'contact@nhutin.vn',
    'contact_phone' => '0909 123 456',
    'company_address' => '123 Đường ABC, Quận 1, TP.HCM',
    'email_notifications' => '1',
    'auto_assign_tickets' => '1',
    'customer_registration' => '0',
    'maintenance_mode' => '0',
    'two_factor_auth' => '0',
    'session_timeout' => '1',
];

const BOOL_KEYS = [
    'email_notifications', 'auto_assign_tickets', 'customer_registration', 'maintenance_mode',
    'two_factor_auth', 'session_timeout',
];

const MAX_AVATAR_SIZE_BYTES = 5 * 1024 * 1024;

function getAvatarExtension(string $mimeType, string $originalName): string
{
    $map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $m = strtolower(trim($mimeType));
    if (isset($map[$m])) return $map[$m];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? ($ext === 'jpeg' ? 'jpg' : $ext) : 'jpg';
}

function ensureAvatarDir(string $subdir): string
{
    $base = realpath(__DIR__ . '/../../img');
    if ($base === false) throw new RuntimeException('Không tìm thấy thư mục img.');
    $path = $base . DIRECTORY_SEPARATOR . $subdir;
    if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
        throw new RuntimeException('Không thể tạo thư mục lưu ảnh.');
    }
    return $path;
}

function loadSettings(PDO $pdo): array
{
    $out = DEFAULT_SETTINGS;
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        while ($row = $stmt->fetch()) {
            $k = (string) ($row['setting_key'] ?? '');
            if ($k !== '' && array_key_exists($k, DEFAULT_SETTINGS)) {
                $out[$k] = (string) ($row['setting_value'] ?? '');
            }
        }
    } catch (Throwable $e) {
        // use defaults
    }
    return $out;
}

function saveSetting(PDO $pdo, string $key, string $value): void
{
    if (!array_key_exists($key, DEFAULT_SETTINGS)) {
        return;
    }
    $stmt = $pdo->prepare(
        "INSERT INTO site_settings (setting_key, setting_value) VALUES (:k, :v)
         ON CONFLICT (setting_key) DO UPDATE SET setting_value = EXCLUDED.setting_value, updated_at = CURRENT_TIMESTAMP"
    );
    $stmt->execute(['k' => $key, 'v' => $value]);
}

ensureAdminOrStaff();

try {
    $pdo = getDbConnection();
    ensureSettingsSchema($pdo);
} catch (Throwable $e) {
    error_log('Settings DB init failed: ' . $e->getMessage());
    respondJson(500, [
        'success' => false,
        'message' => 'Lỗi khởi tạo cơ sở dữ liệu.',
    ]);
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$action = (string) ($_GET['action'] ?? '');
$admin = $_SESSION['nhutin_admin'] ?? [];
$adminId = (int) ($admin['id'] ?? 0);
$role = (string) ($admin['role'] ?? 'admin');

if ($method === 'GET' && $action === 'get_profile') {
    try {
        $pdo = getDbConnection();
        $name = (string) ($admin['name'] ?? '');
        $email = (string) ($admin['email'] ?? '');
        $avatarUrl = null;
        if ($role === 'staff' && $adminId > 0) {
            $stmt = $pdo->prepare("SELECT avatar_url FROM users WHERE id = :id AND role = 'staff' LIMIT 1");
            $stmt->execute(['id' => $adminId]);
            $row = $stmt->fetch();
            if ($row) $avatarUrl = trim((string) ($row['avatar_url'] ?? ''));
        } elseif ($role === 'admin' && $adminId > 0) {
            $stmt = $pdo->prepare("SELECT avatar_url FROM admins WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $adminId]);
            $row = $stmt->fetch();
            if ($row) $avatarUrl = trim((string) ($row['avatar_url'] ?? ''));
        }
        respondJson(200, [
            'success' => true,
            'profile' => [
                'id' => $adminId,
                'name' => $name,
                'email' => $email,
                'avatar_url' => $avatarUrl !== '' ? $avatarUrl : null,
                'role' => $role,
            ],
        ]);
    } catch (Throwable $e) {
        error_log('Get profile failed: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể tải thông tin.']);
    }
}

if ($method === 'POST' && $action === 'upload_avatar') {
    if ($adminId <= 0) {
        respondJson(401, ['success' => false, 'message' => 'Phiên đăng nhập không hợp lệ.']);
    }
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
        $ext = getAvatarExtension($mimeType, (string) ($file['name'] ?? ''));

        if ($role === 'admin') {
            $dir = ensureAvatarDir('admin-avatars');
            $filename = 'admin_' . date('YmdHis') . '_' . random_int(1000, 9999) . '.' . $ext;
            $relativeUrl = '../img/admin-avatars/' . $filename;
        } else {
            $dir = ensureAvatarDir('staff-avatars');
            $filename = 'staff_' . date('YmdHis') . '_' . random_int(1000, 9999) . '.' . $ext;
            $relativeUrl = '../img/staff-avatars/' . $filename;
        }
        $targetPath = $dir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($tmpPath, $targetPath)) {
            throw new RuntimeException('Không thể lưu file ảnh.');
        }

        $pdo = getDbConnection();
        if ($role === 'admin') {
            $pdo->prepare("UPDATE admins SET avatar_url = :url WHERE id = :id")->execute(['url' => $relativeUrl, 'id' => $adminId]);
        } else {
            $pdo->prepare("UPDATE users SET avatar_url = :url WHERE id = :id AND role = 'staff'")->execute(['url' => $relativeUrl, 'id' => $adminId]);
        }
        $_SESSION['nhutin_admin']['avatar_url'] = $relativeUrl;
        $admin['avatar_url'] = $relativeUrl;

        respondJson(200, ['success' => true, 'message' => 'Đã cập nhật ảnh đại diện.', 'avatar_url' => $relativeUrl]);
    } catch (Throwable $e) {
        error_log('Upload avatar failed: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể tải ảnh: ' . $e->getMessage()]);
    }
}

if ($method === 'POST' && $action === 'change_password') {
    $input = getRequestBody();
    $currentPassword = (string) ($input['current_password'] ?? '');
    $newPassword = (string) ($input['new_password'] ?? '');

    if ($currentPassword === '' || $newPassword === '') {
        respondJson(422, ['success' => false, 'message' => 'Vui lòng nhập đầy đủ mật khẩu hiện tại và mật khẩu mới.']);
    }
    if (strlen($newPassword) < 6) {
        respondJson(422, ['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự.']);
    }
    if (strlen($newPassword) > 255) {
        respondJson(422, ['success' => false, 'message' => 'Mật khẩu mới quá dài.']);
    }

    if ($adminId <= 0) {
        respondJson(401, ['success' => false, 'message' => 'Phiên đăng nhập không hợp lệ.']);
    }

    try {
        if ($role === 'staff') {
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id AND role = 'staff' LIMIT 1");
            $stmt->execute(['id' => $adminId]);
            $row = $stmt->fetch();
            if (!$row || !password_verify($currentPassword, (string) ($row['password_hash'] ?? ''))) {
                respondJson(401, ['success' => false, 'message' => 'Mật khẩu hiện tại không đúng.']);
            }
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $up = $pdo->prepare("UPDATE users SET password_hash = :pw WHERE id = :id");
            $up->execute(['pw' => $hash, 'id' => $adminId]);
        } else {
            $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $adminId]);
            $row = $stmt->fetch();
            if (!$row || !password_verify($currentPassword, (string) ($row['password'] ?? ''))) {
                respondJson(401, ['success' => false, 'message' => 'Mật khẩu hiện tại không đúng.']);
            }
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $up = $pdo->prepare("UPDATE admins SET password = :pw WHERE id = :id");
            $up->execute(['pw' => $hash, 'id' => $adminId]);
        }
        respondJson(200, ['success' => true, 'message' => 'Đã cập nhật mật khẩu.']);
    } catch (Throwable $e) {
        error_log('Change password failed: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể đổi mật khẩu.']);
    }
}

if ($method === 'GET') {
    try {
        $settings = loadSettings($pdo);
        respondJson(200, ['success' => true, 'data' => $settings]);
    } catch (Throwable $e) {
        respondJson(500, ['success' => false, 'message' => 'Không thể tải cài đặt.']);
    }
}

if ($method === 'PUT' || $method === 'POST') {
    ensureAdminOnly();

    $input = getRequestBody();

    $allowed = [
        'company_name', 'contact_email', 'contact_phone', 'company_address',
        'email_notifications', 'auto_assign_tickets', 'customer_registration', 'maintenance_mode',
        'two_factor_auth', 'session_timeout',
    ];

    try {
        foreach ($allowed as $key) {
            if (!array_key_exists($key, $input)) {
                continue;
            }
            $val = $input[$key];
            if (in_array($key, BOOL_KEYS, true)) {
                $val = ($val === true || $val === '1' || $val === 'true' || $val === 'on') ? '1' : '0';
            } else {
                $val = trim((string) $val);
                if (strlen($val) > 2000) {
                    $val = substr($val, 0, 2000);
                }
            }
            saveSetting($pdo, $key, $val);
        }
        $settings = loadSettings($pdo);
        respondJson(200, ['success' => true, 'message' => 'Đã lưu cài đặt.', 'data' => $settings]);
    } catch (Throwable $e) {
        error_log('Save settings failed: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể lưu cài đặt.']);
    }
}

respondJson(405, ['success' => false, 'message' => 'Method not allowed.']);
