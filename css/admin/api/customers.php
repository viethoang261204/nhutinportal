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

const ALLOWED_STATUSES = ['pending', 'active', 'inactive', 'suspended'];
const ALLOWED_TYPES = ['individual', 'business'];
const DEFAULT_CUSTOMER_AVATAR = '../img/default.png';
const DEFAULT_CUSTOMER_PASSWORD = '123456';
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

function ensureAvatarUploadDirectory(): string
{
    $uploadDirectory = realpath(__DIR__ . '/../../img');
    if ($uploadDirectory === false) {
        throw new RuntimeException('Không tìm thấy thư mục img.');
    }

    $avatarDirectory = $uploadDirectory . DIRECTORY_SEPARATOR . 'customer-avatars';
    if (!is_dir($avatarDirectory)) {
        if (!mkdir($avatarDirectory, 0775, true) && !is_dir($avatarDirectory)) {
            throw new RuntimeException('Không thể tạo thư mục lưu ảnh.');
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


function stringLength(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }

    return strlen($value);
}

function normalizeCustomerType(string $type): string
{
    $normalized = strtolower(trim($type));
    if (!in_array($normalized, ALLOWED_TYPES, true)) {
        return 'business';
    }
    return $normalized;
}

function normalizeStatus(string $status): string
{
    $normalized = strtolower(trim($status));
    if (!in_array($normalized, ALLOWED_STATUSES, true)) {
        return 'active';
    }
    return $normalized;
}

function customerTypeToId(string $type): int
{
    return normalizeCustomerType($type) === 'individual' ? 2 : 1;
}

function customerTypeFromId(int $typeId): string
{
    return $typeId === 2 ? 'individual' : 'business';
}

function generateCustomerCode(): string
{
    return 'KH' . date('YmdHis') . random_int(10, 99);
}

function ensureCustomersSchema(PDO $pdo): void
{
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS customers (
                id SERIAL PRIMARY KEY,
                customer_code VARCHAR(50) NULL,
                company_name VARCHAR(255) NOT NULL,
                tax_code VARCHAR(50) NULL,
                customer_type_id INT NOT NULL DEFAULT 1,
                address TEXT NULL,
                phone VARCHAR(20) NULL,
                email VARCHAR(255) NULL,
                contact_person VARCHAR(100) NULL,
                position VARCHAR(100) NULL,
                logo_url VARCHAR(500) NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                assigned_staff_id INT NULL,
                notes TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        );
    } catch (Throwable $e) {}
}

function validateCustomerInput(array $input, bool $isUpdate = false): array
{
    $name = trim((string) ($input['name'] ?? ''));
    $email = strtolower(trim((string) ($input['email'] ?? '')));
    $phone = trim((string) ($input['phone'] ?? ''));
    $customerType = normalizeCustomerType((string) ($input['customer_type'] ?? 'business'));
    $status = normalizeStatus((string) ($input['status'] ?? 'active'));
    $address = trim((string) ($input['address'] ?? ''));
    $note = trim((string) ($input['note'] ?? ''));
    $avatarUrl = trim((string) ($input['avatar_url'] ?? ''));

    if ($isUpdate) {
        $id = (int) ($input['id'] ?? 0);
        if ($id <= 0) {
            respondJson(422, [
                'success' => false,
                'message' => 'ID khách hàng không hợp lệ.',
            ]);
        }
    }

    if ($name === '' || stringLength($name) > 255) {
        respondJson(422, [
            'success' => false,
            'message' => 'Tên khách hàng không hợp lệ.',
        ]);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respondJson(422, [
            'success' => false,
            'message' => 'Email không hợp lệ.',
        ]);
    }

    if ($phone === '' || stringLength($phone) > 20) {
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

    return [
        'id' => (int) ($input['id'] ?? 0),
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'customer_type' => $customerType,
        'status' => $status,
        'address' => $address,
        'note' => $note,
        'avatar_url' => $avatarUrl !== '' ? $avatarUrl : DEFAULT_CUSTOMER_AVATAR,
    ];
}

function mapCustomerRow(array $row): array
{
    $id = (int) ($row['id'] ?? 0);
    $avatar = (string) ($row['logo_url'] ?? '');
    if ($avatar === '') {
        $avatar = DEFAULT_CUSTOMER_AVATAR;
    }

    $rawCode = trim((string) ($row['customer_code'] ?? ''));
    $customerCode = $rawCode !== '' ? $rawCode : ('KH' . str_pad((string) $id, 3, '0', STR_PAD_LEFT));

    return [
        'id' => $id,
        'customer_code' => $customerCode,
        'name' => (string) ($row['company_name'] ?? ''),
        'email' => (string) ($row['email'] ?? ''),
        'phone' => (string) ($row['phone'] ?? ''),
        'customer_type' => customerTypeFromId((int) ($row['customer_type_id'] ?? 1)),
        'status' => normalizeStatus((string) ($row['status'] ?? 'active')),
        'address' => (string) ($row['address'] ?? ''),
        'note' => (string) ($row['notes'] ?? ''),
        'avatar_url' => $avatar,
        'created_at' => (string) ($row['created_at'] ?? ''),
        'updated_at' => (string) ($row['updated_at'] ?? ''),
    ];
}

ensureAdminOrStaff();

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
        $filename = 'avatar_' . date('YmdHis') . '_' . random_int(1000, 9999) . '.' . $extension;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($tmpPath, $targetPath)) {
            throw new RuntimeException('Không thể lưu file ảnh.');
        }

        respondJson(200, [
            'success' => true,
            'message' => 'Tải ảnh thành công.',
            'avatar_url' => '../img/customer-avatars/' . $filename,
        ]);
    } catch (Throwable $e) {
        error_log('Upload avatar failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể tải ảnh lên: ' . $e->getMessage(),
        ]);
    }
}

try {
    $pdo = getDbConnection();
    ensureCustomersSchema($pdo);
} catch (Throwable $e) {
    error_log('Customers DB init failed: ' . $e->getMessage());
    respondJson(500, [
        'success' => false,
        'message' => 'Lỗi khởi tạo cơ sở dữ liệu: ' . $e->getMessage(),
    ]);
}

if ($method === 'GET') {
    try {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('SELECT * FROM customers WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $customer = $stmt->fetch();
            if (!$customer) {
                respondJson(404, [
                    'success' => false,
                    'message' => 'Không tìm thấy khách hàng.',
                ]);
            }

            respondJson(200, [
                'success' => true,
                'data' => mapCustomerRow($customer),
            ]);
        }

        $search = trim((string) ($_GET['search'] ?? ''));
        $status = isset($_GET['status']) && (string) $_GET['status'] !== ''
            ? normalizeStatus((string) $_GET['status'])
            : '';
        $type = isset($_GET['type']) && (string) $_GET['type'] !== ''
            ? normalizeCustomerType((string) $_GET['type'])
            : '';
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = (int) ($_GET['limit'] ?? 10);
        $limit = max(1, min($limit, 50));
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = '(company_name LIKE :keyword OR email LIKE :keyword OR phone LIKE :keyword OR customer_code LIKE :keyword)';
            $params['keyword'] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }

        if ($type !== '') {
            $where[] = 'customer_type_id = :customer_type_id';
            $params['customer_type_id'] = customerTypeToId($type);
        }

        $whereSql = count($where) > 0 ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM customers {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $listSql = "SELECT * FROM customers {$whereSql} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $listStmt = $pdo->prepare($listSql);
        foreach ($params as $key => $value) {
            if ($key === 'customer_type_id') {
                $listStmt->bindValue(':' . $key, (int) $value, PDO::PARAM_INT);
                continue;
            }
            $listStmt->bindValue(':' . $key, (string) $value);
        }
        $listStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $listStmt->execute();

        $rows = $listStmt->fetchAll();
        $data = array_map(static fn(array $row): array => mapCustomerRow($row), $rows);

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
        error_log('Load customers failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể tải danh sách khách hàng: ' . $e->getMessage(),
        ]);
    }
}

if ($method === 'POST') {
    $payload = validateCustomerInput(getRequestBody(), false);

    // Kiểm tra trùng email trong customers
    $chk = $pdo->prepare('SELECT id FROM customers WHERE LOWER(TRIM(email)) = :email LIMIT 1');
    $chk->execute(['email' => strtolower($payload['email'])]);
    if ($chk->fetch()) {
        respondJson(409, [
            'success' => false,
            'message' => 'Email đã tồn tại trong danh sách khách hàng.',
        ]);
    }

    // Kiểm tra trùng email trong users (để tránh conflict khi tạo tài khoản Portal)
    try {
        $chkUser = $pdo->prepare('SELECT id FROM users WHERE LOWER(TRIM(email)) = :email LIMIT 1');
        $chkUser->execute(['email' => strtolower($payload['email'])]);
        if ($chkUser->fetch()) {
            respondJson(409, [
                'success' => false,
                'message' => 'Email đã được sử dụng bởi tài khoản khác. Vui lòng dùng email khác.',
            ]);
        }
    } catch (Throwable $ignore) {}

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO customers (
                customer_code,
                company_name,
                tax_code,
                customer_type_id,
                address,
                phone,
                email,
                contact_person,
                position,
                logo_url,
                status,
                assigned_staff_id,
                notes
            ) VALUES (
                :customer_code,
                :company_name,
                :tax_code,
                :customer_type_id,
                :address,
                :phone,
                :email,
                :contact_person,
                :position,
                :logo_url,
                :status,
                :assigned_staff_id,
                :notes
            )'
        );
        $stmt->execute([
            'customer_code' => generateCustomerCode(),
            'company_name' => $payload['name'],
            'tax_code' => '',
            'customer_type_id' => customerTypeToId($payload['customer_type']),
            'address' => $payload['address'],
            'phone' => $payload['phone'],
            'email' => $payload['email'],
            'contact_person' => $payload['name'],
            'position' => '',
            'logo_url' => $payload['avatar_url'],
            'status' => $payload['status'],
            'assigned_staff_id' => null,
            'notes' => $payload['note'],
        ]);
        $newId = (int) $pdo->lastInsertId();
        logActivity($pdo, 'create', 'customer', $newId, 'Tạo khách hàng: ' . $payload['name']);

        // Tự động tạo tài khoản user (role=customer) cho khách hàng
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
        $userCreated = false;
        try {
            $email = strtolower(trim($payload['email']));
            $userIns = $pdo->prepare(
                "INSERT INTO users (username, email, password_hash, full_name, phone, avatar_url, role, customer_id, is_active)
                 VALUES (:username, :email, :password_hash, :full_name, :phone, :avatar_url, 'customer', :customer_id, 1)"
            );
            $userIns->execute([
                'username' => $email,
                'email' => $email,
                'password_hash' => password_hash(DEFAULT_CUSTOMER_PASSWORD, PASSWORD_DEFAULT),
                'full_name' => $payload['name'],
                'phone' => $payload['phone'],
                'avatar_url' => $payload['avatar_url'] ?? '',
                'customer_id' => $newId,
            ]);
            $userCreated = true;
        } catch (Throwable $ue) {
            if (!str_contains(strtolower($ue->getMessage()), 'duplicate')) {
                error_log('Auto-create user for customer failed: ' . $ue->getMessage());
            }
        }
    } catch (Throwable $e) {
        if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
            respondJson(409, [
                'success' => false,
                'message' => 'Email đã tồn tại.',
            ]);
        }
        error_log('Create customer failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể tạo khách hàng: ' . $e->getMessage(),
        ]);
    }

    $stmt = $pdo->prepare('SELECT * FROM customers WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $newId]);
    $customer = $stmt->fetch();

    $msg = $userCreated
        ? 'Tạo khách hàng thành công. Đã tạo tài khoản Portal: ' . $payload['email'] . ' / MK: ' . DEFAULT_CUSTOMER_PASSWORD
        : 'Tạo khách hàng thành công.';
    respondJson(201, [
        'success' => true,
        'message' => $msg,
        'data' => mapCustomerRow($customer ?: []),
        'portal_password' => $userCreated ? DEFAULT_CUSTOMER_PASSWORD : null,
    ]);
}

if ($method === 'PUT') {
    $payload = validateCustomerInput(getRequestBody(), true);

    $existsStmt = $pdo->prepare('SELECT * FROM customers WHERE id = :id LIMIT 1');
    $existsStmt->execute(['id' => $payload['id']]);
    $existingCustomer = $existsStmt->fetch();
    if (!$existingCustomer) {
        respondJson(404, [
            'success' => false,
            'message' => 'Không tìm thấy khách hàng.',
        ]);
    }

    // Kiểm tra trùng email khi cập nhật (loại trừ chính khách hàng đang sửa)
    $chk = $pdo->prepare('SELECT id FROM customers WHERE LOWER(TRIM(email)) = :email AND id != :id LIMIT 1');
    $chk->execute(['email' => strtolower($payload['email']), 'id' => $payload['id']]);
    if ($chk->fetch()) {
        respondJson(409, [
            'success' => false,
            'message' => 'Email đã tồn tại trong danh sách khách hàng.',
        ]);
    }

    try {
        $chkUser = $pdo->prepare('SELECT id FROM users WHERE LOWER(TRIM(email)) = :email AND (customer_id IS NULL OR customer_id != :cid) LIMIT 1');
        $chkUser->execute(['email' => strtolower($payload['email']), 'cid' => $payload['id']]);
        if ($chkUser->fetch()) {
            respondJson(409, [
                'success' => false,
                'message' => 'Email đã được sử dụng bởi tài khoản khác. Vui lòng dùng email khác.',
            ]);
        }
    } catch (Throwable $ignore) {}

    try {
        $stmt = $pdo->prepare(
            'UPDATE customers
             SET company_name = :company_name,
                 customer_type_id = :customer_type_id,
                 address = :address,
                 phone = :phone,
                 email = :email,
                 contact_person = :contact_person,
                 logo_url = :logo_url,
                 status = :status,
                 notes = :notes
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $payload['id'],
            'company_name' => $payload['name'],
            'customer_type_id' => customerTypeToId($payload['customer_type']),
            'address' => $payload['address'],
            'phone' => $payload['phone'],
            'email' => $payload['email'],
            'contact_person' => (string) ($existingCustomer['contact_person'] ?? $payload['name']),
            'logo_url' => $payload['avatar_url'],
            'status' => $payload['status'],
            'notes' => $payload['note'],
        ]);
        logActivity($pdo, 'update', 'customer', $payload['id'], 'Cập nhật khách hàng: ' . $payload['name']);
    } catch (Throwable $e) {
        if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
            respondJson(409, [
                'success' => false,
                'message' => 'Email đã tồn tại.',
            ]);
        }
        error_log('Update customer failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể cập nhật khách hàng: ' . $e->getMessage(),
        ]);
    }

    $stmt = $pdo->prepare('SELECT * FROM customers WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $payload['id']]);
    $customer = $stmt->fetch();

    respondJson(200, [
        'success' => true,
        'message' => 'Cập nhật khách hàng thành công.',
        'data' => mapCustomerRow($customer ?: []),
    ]);
}

if ($method === 'DELETE') {
    $body = getRequestBody();
    $id = (int) ($body['id'] ?? ($_GET['id'] ?? 0));
    if ($id <= 0) {
        respondJson(422, [
            'success' => false,
            'message' => 'ID khách hàng không hợp lệ.',
        ]);
    }

    $stmt = $pdo->prepare('DELETE FROM customers WHERE id = :id');
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        respondJson(404, [
            'success' => false,
            'message' => 'Không tìm thấy khách hàng.',
        ]);
    }

    logActivity($pdo, 'delete', 'customer', $id, 'Xóa khách hàng #' . $id);
    respondJson(200, [
        'success' => true,
        'message' => 'Đã xóa khách hàng.',
    ]);
}

respondJson(405, [
    'success' => false,
    'message' => 'Method not allowed.',
]);
