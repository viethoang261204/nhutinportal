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

const USER_ROLE = 'staff';
const DEFAULT_AVATAR = '../img/default.png';
const MAX_AVATAR_SIZE_BYTES = 5 * 1024 * 1024;

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

function stringLength(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }

    return strlen($value);
}

function normalizeRole(string $role): string
{
    $normalized = strtolower(trim($role));
    if (!in_array($normalized, ['staff', 'admin', 'customer'], true)) {
        return USER_ROLE;
    }
    return $normalized;
}

function ensureUsersSchema(PDO $pdo): void
{
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                full_name VARCHAR(100) NULL,
                phone VARCHAR(20) NULL,
                avatar_url VARCHAR(500) NULL,
                role VARCHAR(20) DEFAULT 'customer',
                customer_id INT NULL,
                is_active SMALLINT DEFAULT 1,
                last_login_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        );
    } catch (Throwable $e) {}
}

function ensureAvatarUploadDirectory(): string
{
    $uploadDirectory = realpath(__DIR__ . '/../../img');
    if ($uploadDirectory === false) {
        throw new RuntimeException('Không tìm thấy thư mục img.');
    }

    $avatarDirectory = $uploadDirectory . DIRECTORY_SEPARATOR . 'staff-avatars';
    if (!is_dir($avatarDirectory)) {
        if (!mkdir($avatarDirectory, 0775, true) && !is_dir($avatarDirectory)) {
            throw new RuntimeException('Không thể tạo thư mục lưu ảnh nhân viên.');
        }
    }

    return $avatarDirectory;
}

function getAvatarExtension(string $mimeType, string $originalName): string
{
    $normalizedMime = strtolower(trim($mimeType));
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (isset($map[$normalizedMime])) {
        return $map[$normalizedMime];
    }

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
        return $ext === 'jpeg' ? 'jpg' : $ext;
    }

    throw new RuntimeException('Định dạng ảnh không hợp lệ.');
}

function mapUserRow(array $row): array
{
    $avatar = trim((string) ($row['avatar_url'] ?? ''));
    if ($avatar === '') {
        $avatar = DEFAULT_AVATAR;
    }

    return [
        'id' => (int) ($row['id'] ?? 0),
        'username' => (string) ($row['username'] ?? ''),
        'email' => (string) ($row['email'] ?? ''),
        'full_name' => (string) ($row['full_name'] ?? ''),
        'phone' => (string) ($row['phone'] ?? ''),
        'avatar_url' => $avatar,
        'role' => normalizeRole((string) ($row['role'] ?? USER_ROLE)),
        'customer_id' => (int) ($row['customer_id'] ?? 0) ?: null,
        'is_active' => (int) ($row['is_active'] ?? 0) === 1,
        'last_login_at' => (string) ($row['last_login_at'] ?? ''),
        'created_at' => (string) ($row['created_at'] ?? ''),
    ];
}

function validateUserInput(array $input, bool $isUpdate = false): array
{
    $id = (int) ($input['id'] ?? 0);
    $username = trim((string) ($input['username'] ?? ''));
    $email = strtolower(trim((string) ($input['email'] ?? '')));
    $fullName = trim((string) ($input['full_name'] ?? ''));
    $phone = trim((string) ($input['phone'] ?? ''));
    $avatarUrl = trim((string) ($input['avatar_url'] ?? ''));
    $password = (string) ($input['password'] ?? '');
    $isActive = (int) ($input['is_active'] ?? 1) === 1 ? 1 : 0;

    if ($isUpdate && $id <= 0) {
        respondJson(422, [
            'success' => false,
            'message' => 'ID người dùng không hợp lệ.',
        ]);
    }

    if ($username === '' || stringLength($username) > 100) {
        respondJson(422, [
            'success' => false,
            'message' => 'Username không hợp lệ.',
        ]);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respondJson(422, [
            'success' => false,
            'message' => 'Email không hợp lệ.',
        ]);
    }

    if ($fullName === '' || stringLength($fullName) > 100) {
        respondJson(422, [
            'success' => false,
            'message' => 'Họ tên không hợp lệ.',
        ]);
    }

    if ($phone !== '' && stringLength($phone) > 20) {
        respondJson(422, [
            'success' => false,
            'message' => 'Số điện thoại không hợp lệ.',
        ]);
    }

    if ($avatarUrl !== '' && stringLength($avatarUrl) > 500) {
        respondJson(422, [
            'success' => false,
            'message' => 'Đường dẫn ảnh đại diện không hợp lệ.',
        ]);
    }

    if (!$isUpdate && (stringLength($password) < 6 || stringLength($password) > 72)) {
        respondJson(422, [
            'success' => false,
            'message' => 'Mật khẩu phải từ 6 đến 72 ký tự.',
        ]);
    }

    if ($isUpdate && $password !== '' && (stringLength($password) < 6 || stringLength($password) > 72)) {
        respondJson(422, [
            'success' => false,
            'message' => 'Mật khẩu mới phải từ 6 đến 72 ký tự.',
        ]);
    }

    return [
        'id' => $id,
        'username' => $username,
        'email' => $email,
        'full_name' => $fullName,
        'phone' => $phone,
        'avatar_url' => $avatarUrl !== '' ? $avatarUrl : DEFAULT_AVATAR,
        'password' => $password,
        'is_active' => $isActive,
    ];
}

ensureAdminOnly();

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$action = (string) ($_GET['action'] ?? '');

if ($method === 'POST' && $action === 'upload_avatar') {
    try {
        if (!isset($_FILES['avatar']) || !is_array($_FILES['avatar'])) {
            respondJson(422, [
                'success' => false,
                'message' => 'Vui lòng chọn ảnh để tải lên.',
            ]);
        }

        $file = $_FILES['avatar'];
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode !== UPLOAD_ERR_OK) {
            respondJson(422, [
                'success' => false,
                'message' => 'Upload ảnh thất bại.',
            ]);
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            respondJson(422, [
                'success' => false,
                'message' => 'File upload không hợp lệ.',
            ]);
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > MAX_AVATAR_SIZE_BYTES) {
            respondJson(422, [
                'success' => false,
                'message' => 'Ảnh phải nhỏ hơn hoặc bằng 5MB.',
            ]);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? (string) finfo_file($finfo, $tmpPath) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        $extension = getAvatarExtension($mimeType, (string) ($file['name'] ?? ''));
        $targetDir = ensureAvatarUploadDirectory();
        $filename = 'staff_' . date('YmdHis') . '_' . random_int(1000, 9999) . '.' . $extension;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($tmpPath, $targetPath)) {
            throw new RuntimeException('Không thể lưu file ảnh.');
        }

        respondJson(200, [
            'success' => true,
            'message' => 'Tải ảnh thành công.',
            'avatar_url' => '../img/staff-avatars/' . $filename,
        ]);
    } catch (Throwable $e) {
        error_log('Upload staff avatar failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể tải ảnh lên: ' . $e->getMessage(),
        ]);
    }
}

try {
    $pdo = getDbConnection();
    ensureUsersSchema($pdo);
} catch (Throwable $e) {
    error_log('Users DB init failed: ' . $e->getMessage());
    respondJson(500, [
        'success' => false,
        'message' => 'Lỗi khởi tạo cơ sở dữ liệu: ' . $e->getMessage(),
    ]);
}

// Admin đổi mật khẩu cho user (không cần mật khẩu cũ)
if ($method === 'POST' && $action === 'change_password') {
    $body = getRequestBody();
    $userId = (int) ($body['id'] ?? $body['user_id'] ?? 0);
    $newPassword = (string) ($body['new_password'] ?? $body['password'] ?? '');

    if ($userId <= 0) {
        respondJson(422, ['success' => false, 'message' => 'ID người dùng không hợp lệ.']);
    }
    if (stringLength($newPassword) < 6 || stringLength($newPassword) > 72) {
        respondJson(422, ['success' => false, 'message' => 'Mật khẩu mới phải từ 6 đến 72 ký tự.']);
    }

    try {
        $stmt = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $stmt->execute([
            'hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => $userId,
        ]);
        if ($stmt->rowCount() === 0) {
            respondJson(404, ['success' => false, 'message' => 'Không tìm thấy người dùng.']);
        }
        respondJson(200, ['success' => true, 'message' => 'Đã đổi mật khẩu thành công.']);
    } catch (Throwable $e) {
        error_log('Change password failed: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể đổi mật khẩu.']);
    }
}

if ($method === 'GET') {
    try {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch();
            if (!$user) {
                respondJson(404, [
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.',
                ]);
            }
            respondJson(200, [
                'success' => true,
                'data' => mapUserRow($user),
            ]);
        }

        $search = trim((string) ($_GET['search'] ?? ''));
        $statusParam = trim((string) ($_GET['status'] ?? ''));
        $roleParam = trim((string) ($_GET['role'] ?? ''));
        $statusFilter = '';
        if ($statusParam === 'active') {
            $statusFilter = 1;
        } elseif ($statusParam === 'inactive') {
            $statusFilter = 0;
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = (int) ($_GET['limit'] ?? 10);
        $limit = max(1, min($limit, 50));
        $offset = ($page - 1) * $limit;

        $where = ["role IN ('staff', 'customer')"];
        $params = [];

        if ($roleParam === 'staff') {
            $where[] = "role = 'staff'";
        } elseif ($roleParam === 'customer') {
            $where[] = "role = 'customer'";
        }

        if ($search !== '') {
            $where[] = '(username LIKE :keyword OR full_name LIKE :keyword OR email LIKE :keyword OR phone LIKE :keyword)';
            $params['keyword'] = '%' . $search . '%';
        }

        if ($statusFilter !== '') {
            $where[] = 'is_active = :is_active';
            $params['is_active'] = $statusFilter;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users {$whereSql}");
        foreach ($params as $key => $value) {
            if ($key === 'is_active') {
                $countStmt->bindValue(':' . $key, (int) $value, PDO::PARAM_INT);
                continue;
            }
            $countStmt->bindValue(':' . $key, (string) $value);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $listStmt = $pdo->prepare("SELECT * FROM users {$whereSql} ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            if ($key === 'is_active') {
                $listStmt->bindValue(':' . $key, (int) $value, PDO::PARAM_INT);
                continue;
            }
            $listStmt->bindValue(':' . $key, (string) $value);
        }
        $listStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $listStmt->execute();

        $rows = $listStmt->fetchAll();
        $data = array_map(static fn(array $row): array => mapUserRow($row), $rows);

        respondJson(200, [
            'success' => true,
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => (int) ceil($total / $limit),
            ],
        ]);
    } catch (Throwable $e) {
        error_log('Load users failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể tải danh sách người dùng: ' . $e->getMessage(),
        ]);
    }
}

if ($method === 'POST') {
    $payload = validateUserInput(getRequestBody(), false);

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash, full_name, phone, avatar_url, role, is_active)
             VALUES (:username, :email, :password_hash, :full_name, :phone, :avatar_url, :role, :is_active)'
        );
        $stmt->execute([
            'username' => $payload['username'],
            'email' => $payload['email'],
            'password_hash' => password_hash($payload['password'], PASSWORD_DEFAULT),
            'full_name' => $payload['full_name'],
            'phone' => $payload['phone'],
            'avatar_url' => $payload['avatar_url'],
            'role' => USER_ROLE,
            'is_active' => $payload['is_active'],
        ]);
        $newId = (int) $pdo->lastInsertId();
    } catch (Throwable $e) {
        if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
            respondJson(409, [
                'success' => false,
                'message' => 'Username hoặc email đã tồn tại.',
            ]);
        }
        error_log('Create user failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể tạo nhân viên: ' . $e->getMessage(),
        ]);
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id AND role = :role LIMIT 1');
    $stmt->execute([
        'id' => $newId,
        'role' => USER_ROLE,
    ]);
    $user = $stmt->fetch();

    respondJson(201, [
        'success' => true,
        'message' => 'Tạo nhân viên thành công.',
        'data' => mapUserRow($user ?: []),
    ]);
}

if ($method === 'PUT') {
    $payload = validateUserInput(getRequestBody(), true);

    $existsStmt = $pdo->prepare('SELECT * FROM users WHERE id = :id AND role IN (\'staff\', \'customer\') LIMIT 1');
    $existsStmt->execute(['id' => $payload['id']]);
    $existingUser = $existsStmt->fetch();
    if (!$existingUser) {
        respondJson(404, [
            'success' => false,
            'message' => 'Không tìm thấy nhân viên.',
        ]);
    }

    try {
        $sql = 'UPDATE users
                SET username = :username,
                    email = :email,
                    full_name = :full_name,
                    phone = :phone,
                    avatar_url = :avatar_url,
                    is_active = :is_active';
        $params = [
            'id' => $payload['id'],
            'username' => $payload['username'],
            'email' => $payload['email'],
            'full_name' => $payload['full_name'],
            'phone' => $payload['phone'],
            'avatar_url' => $payload['avatar_url'],
            'is_active' => $payload['is_active'],
        ];

        if ($payload['password'] !== '') {
            $sql .= ', password_hash = :password_hash';
            $params['password_hash'] = password_hash($payload['password'], PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id AND role IN (\'staff\', \'customer\')';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } catch (Throwable $e) {
        if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
            respondJson(409, [
                'success' => false,
                'message' => 'Username hoặc email đã tồn tại.',
            ]);
        }
        error_log('Update user failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể cập nhật nhân viên: ' . $e->getMessage(),
        ]);
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $payload['id']]);
    $user = $stmt->fetch();

    respondJson(200, [
        'success' => true,
        'message' => 'Cập nhật người dùng thành công.',
        'data' => mapUserRow($user ?: []),
    ]);
}

if ($method === 'DELETE') {
    $body = getRequestBody();
    $id = (int) ($body['id'] ?? ($_GET['id'] ?? 0));
    if ($id <= 0) {
        respondJson(422, [
            'success' => false,
            'message' => 'ID nhân viên không hợp lệ.',
        ]);
    }

    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id AND role IN (\'staff\', \'customer\')');
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        respondJson(404, [
            'success' => false,
            'message' => 'Không tìm thấy người dùng.',
        ]);
    }

    respondJson(200, [
        'success' => true,
        'message' => 'Đã xóa người dùng.',
    ]);
}

respondJson(405, [
    'success' => false,
    'message' => 'Method not allowed.',
]);
