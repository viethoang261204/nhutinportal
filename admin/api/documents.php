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

const ALLOWED_STATUS = ['draft', 'published', 'archived'];
const MAX_FILE_SIZE_BYTES = 20 * 1024 * 1024;

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

function normalizeStatus(string $status): string
{
    $normalized = strtolower(trim($status));
    if (!in_array($normalized, ALLOWED_STATUS, true)) {
        return 'draft';
    }
    return $normalized;
}

function stringLength(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }
    return strlen($value);
}

function ensureDocumentsSchema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS documents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            document_code VARCHAR(50) UNIQUE NOT NULL,
            title VARCHAR(255) NOT NULL,
            document_type_id INT NOT NULL,
            order_id INT NULL,
            folder_id INT NULL,
            customer_id INT NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_size BIGINT NULL,
            mime_type VARCHAR(100) NULL,
            status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
            metadata JSON NULL,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS document_customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            document_id INT NOT NULL,
            customer_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_document_customer (document_id, customer_id),
            INDEX idx_document (document_id),
            INDEX idx_customer (customer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    // Backfill mapping table from legacy one-document-one-customer rows.
    $pdo->exec(
        "INSERT IGNORE INTO document_customers (document_id, customer_id)
         SELECT d.id, d.customer_id
         FROM documents d
         WHERE d.customer_id IS NOT NULL"
    );
}

function mapDocumentRow(array $row): array
{
    $customerIdsRaw = trim((string) ($row['customer_ids_csv'] ?? ''));
    $customerIds = [];
    if ($customerIdsRaw !== '') {
        foreach (explode(',', $customerIdsRaw) as $value) {
            $id = (int) trim($value);
            if ($id > 0) {
                $customerIds[] = $id;
            }
        }
    }

    $customerNames = trim((string) ($row['customer_names'] ?? ''));
    $primaryCustomer = (string) ($row['customer_name'] ?? '');
    if ($customerNames === '' && $primaryCustomer !== '') {
        $customerNames = $primaryCustomer;
    }

    return [
        'id' => (int) ($row['id'] ?? 0),
        'document_code' => (string) ($row['document_code'] ?? ''),
        'title' => (string) ($row['title'] ?? ''),
        'document_type_id' => (int) ($row['document_type_id'] ?? 0),
        'document_type_name' => (string) ($row['document_type_name'] ?? ''),
        'customer_id' => (int) ($row['customer_id'] ?? 0),
        'customer_name' => $primaryCustomer,
        'customer_names' => $customerNames,
        'customer_ids' => $customerIds,
        'file_path' => (string) ($row['file_path'] ?? ''),
        'file_name' => (string) ($row['file_name'] ?? ''),
        'file_size' => (int) ($row['file_size'] ?? 0),
        'mime_type' => (string) ($row['mime_type'] ?? ''),
        'status' => normalizeStatus((string) ($row['status'] ?? 'draft')),
        'created_by' => (int) ($row['created_by'] ?? 0),
        'created_at' => (string) ($row['created_at'] ?? ''),
    ];
}

function validateDocumentInput(array $input, bool $isUpdate = false): array
{
    $id = (int) ($input['id'] ?? 0);
    $title = trim((string) ($input['title'] ?? ''));
    $documentTypeId = (int) ($input['document_type_id'] ?? 0);
    $customerIdsInput = $input['customer_ids'] ?? [];
    if (!is_array($customerIdsInput)) {
        $customerIdsInput = [];
    }
    if (count($customerIdsInput) === 0) {
        $singleCustomerId = (int) ($input['customer_id'] ?? 0);
        if ($singleCustomerId > 0) {
            $customerIdsInput[] = $singleCustomerId;
        }
    }
    $customerIds = [];
    foreach ($customerIdsInput as $value) {
        $idValue = (int) $value;
        if ($idValue > 0) {
            $customerIds[$idValue] = $idValue;
        }
    }
    $customerIds = array_values($customerIds);
    $status = normalizeStatus((string) ($input['status'] ?? 'draft'));
    $filePath = trim((string) ($input['file_path'] ?? ''));
    $fileName = trim((string) ($input['file_name'] ?? ''));
    $fileSize = (int) ($input['file_size'] ?? 0);
    $mimeType = trim((string) ($input['mime_type'] ?? ''));

    if ($isUpdate && $id <= 0) {
        respondJson(422, [
            'success' => false,
            'message' => 'ID tài liệu không hợp lệ.',
        ]);
    }

    if ($title === '' || stringLength($title) > 255) {
        respondJson(422, [
            'success' => false,
            'message' => 'Tiêu đề tài liệu không hợp lệ.',
        ]);
    }

    if ($documentTypeId <= 0) {
        respondJson(422, [
            'success' => false,
            'message' => 'Loại tài liệu không hợp lệ.',
        ]);
    }

    if (count($customerIds) === 0) {
        respondJson(422, [
            'success' => false,
            'message' => 'Vui lòng chọn ít nhất một khách hàng.',
        ]);
    }

    if ($filePath === '' || stringLength($filePath) > 500) {
        respondJson(422, [
            'success' => false,
            'message' => 'Đường dẫn file không hợp lệ.',
        ]);
    }

    if ($fileName === '' || stringLength($fileName) > 255) {
        respondJson(422, [
            'success' => false,
            'message' => 'Tên file không hợp lệ.',
        ]);
    }

    return [
        'id' => $id,
        'title' => $title,
        'document_type_id' => $documentTypeId,
        'customer_id' => (int) $customerIds[0],
        'customer_ids' => $customerIds,
        'status' => $status,
        'file_path' => $filePath,
        'file_name' => $fileName,
        'file_size' => $fileSize,
        'mime_type' => $mimeType,
    ];
}

function generateDocumentCode(): string
{
    return 'DOC-' . date('YmdHis') . '-' . random_int(10, 99);
}

function ensureDocumentUploadDirectory(): string
{
    $root = realpath(__DIR__ . '/../../');
    if ($root === false) {
        throw new RuntimeException('Không tìm thấy thư mục dự án.');
    }

    $targetDirectory = $root . DIRECTORY_SEPARATOR . 'portal' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents';
    if (!is_dir($targetDirectory)) {
        if (!mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException('Không thể tạo thư mục upload tài liệu.');
        }
    }

    return $targetDirectory;
}

function resolveCreatorUserId(PDO $pdo): int
{
    $adminSession = is_array($_SESSION['nhutin_admin'] ?? null) ? $_SESSION['nhutin_admin'] : [];
    $email = strtolower(trim((string) ($adminSession['email'] ?? '')));

    if ($email !== '') {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user && isset($user['id'])) {
            return (int) $user['id'];
        }
    }

    $fallback = $pdo->query("SELECT id FROM users WHERE role IN ('admin','staff') ORDER BY id ASC LIMIT 1")->fetch();
    if ($fallback && isset($fallback['id'])) {
        return (int) $fallback['id'];
    }

    throw new RuntimeException('Không tìm thấy tài khoản users để gán created_by.');
}

function syncDocumentCustomers(PDO $pdo, int $documentId, array $customerIds): void
{
    $deleteStmt = $pdo->prepare("DELETE FROM document_customers WHERE document_id = :document_id");
    $deleteStmt->execute(['document_id' => $documentId]);

    if (count($customerIds) === 0) {
        return;
    }

    $insertStmt = $pdo->prepare(
        "INSERT INTO document_customers (document_id, customer_id) VALUES (:document_id, :customer_id)"
    );
    foreach ($customerIds as $customerId) {
        $insertStmt->execute([
            'document_id' => $documentId,
            'customer_id' => (int) $customerId,
        ]);
    }
}

ensureAdminOnly();

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$action = (string) ($_GET['action'] ?? '');

try {
    $pdo = getDbConnection();
    ensureDocumentsSchema($pdo);
} catch (Throwable $e) {
    error_log('Documents DB init failed: ' . $e->getMessage());
    respondJson(500, [
        'success' => false,
        'message' => 'Lỗi khởi tạo cơ sở dữ liệu: ' . $e->getMessage(),
    ]);
}

if ($method === 'GET' && $action === 'meta') {
    try {
        $documentTypes = $pdo->query(
            "SELECT id, code, name FROM document_types WHERE is_active = 1 ORDER BY name ASC"
        )->fetchAll();
        $customers = $pdo->query(
            "SELECT id, customer_code, company_name FROM customers ORDER BY company_name ASC"
        )->fetchAll();

        respondJson(200, [
            'success' => true,
            'data' => [
                'document_types' => $documentTypes,
                'customers' => $customers,
            ],
        ]);
    } catch (Throwable $e) {
        error_log('Load document meta failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể tải dữ liệu danh mục: ' . $e->getMessage(),
        ]);
    }
}

if ($method === 'POST' && $action === 'upload_file') {
    try {
        if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
            respondJson(422, [
                'success' => false,
                'message' => 'Vui lòng chọn file tài liệu.',
            ]);
        }

        $file = $_FILES['file'];
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode !== UPLOAD_ERR_OK) {
            respondJson(422, [
                'success' => false,
                'message' => 'Upload file thất bại.',
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
        if ($size <= 0 || $size > MAX_FILE_SIZE_BYTES) {
            respondJson(422, [
                'success' => false,
                'message' => 'File phải nhỏ hơn hoặc bằng 20MB.',
            ]);
        }

        $originalName = (string) ($file['name'] ?? 'document');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $safeExtension = preg_replace('/[^a-z0-9]/', '', $extension);
        if ($safeExtension === '') {
            $safeExtension = 'bin';
        }
        $storedName = 'doc_' . date('YmdHis') . '_' . random_int(1000, 9999) . '.' . $safeExtension;
        $targetDirectory = ensureDocumentUploadDirectory();
        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $storedName;

        if (!move_uploaded_file($tmpPath, $targetPath)) {
            throw new RuntimeException('Không thể lưu file upload.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? (string) finfo_file($finfo, $targetPath) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        respondJson(200, [
            'success' => true,
            'message' => 'Upload tài liệu thành công.',
            'file' => [
                'file_path' => '../portal/uploads/documents/' . $storedName,
                'file_name' => $originalName,
                'file_size' => $size,
                'mime_type' => $mimeType,
            ],
        ]);
    } catch (Throwable $e) {
        error_log('Upload document failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể upload tài liệu: ' . $e->getMessage(),
        ]);
    }
}

if ($method === 'GET') {
    try {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare(
                "SELECT d.*,
                        dt.name AS document_type_name,
                        c.company_name AS customer_name,
                        dcm.customer_names,
                        dcm.customer_ids_csv
                 FROM documents d
                 LEFT JOIN document_types dt ON dt.id = d.document_type_id
                 LEFT JOIN customers c ON c.id = d.customer_id
                 LEFT JOIN (
                    SELECT dc.document_id,
                           GROUP_CONCAT(DISTINCT c_map.company_name ORDER BY c_map.company_name SEPARATOR ', ') AS customer_names,
                           GROUP_CONCAT(DISTINCT dc.customer_id ORDER BY dc.customer_id SEPARATOR ',') AS customer_ids_csv
                    FROM document_customers dc
                    LEFT JOIN customers c_map ON c_map.id = dc.customer_id
                    GROUP BY dc.document_id
                 ) AS dcm ON dcm.document_id = d.id
                 WHERE d.id = :id
                 LIMIT 1"
            );
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            if (!$row) {
                respondJson(404, [
                    'success' => false,
                    'message' => 'Không tìm thấy tài liệu.',
                ]);
            }

            respondJson(200, [
                'success' => true,
                'data' => mapDocumentRow($row),
            ]);
        }

        $search = trim((string) ($_GET['search'] ?? ''));
        $status = isset($_GET['status']) && (string) $_GET['status'] !== '' ? normalizeStatus((string) $_GET['status']) : '';
        $documentTypeId = (int) ($_GET['document_type_id'] ?? 0);

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = max(1, min((int) ($_GET['limit'] ?? 10), 50));
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = '(d.title LIKE :keyword OR d.document_code LIKE :keyword OR d.file_name LIKE :keyword OR EXISTS (
                SELECT 1
                FROM document_customers dc_search
                LEFT JOIN customers c_search ON c_search.id = dc_search.customer_id
                WHERE dc_search.document_id = d.id
                  AND c_search.company_name LIKE :keyword_customer
            ))';
            $params['keyword'] = '%' . $search . '%';
            $params['keyword_customer'] = '%' . $search . '%';
        }
        if ($status !== '') {
            $where[] = 'd.status = :status';
            $params['status'] = $status;
        }
        if ($documentTypeId > 0) {
            $where[] = 'd.document_type_id = :document_type_id';
            $params['document_type_id'] = $documentTypeId;
        }

        $whereSql = count($where) > 0 ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = $pdo->prepare(
            "SELECT COUNT(DISTINCT d.id)
             FROM documents d
             {$whereSql}"
        );
        foreach ($params as $key => $value) {
            $countStmt->bindValue(':' . $key, $value);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $listStmt = $pdo->prepare(
            "SELECT d.*,
                    dt.name AS document_type_name,
                    c.company_name AS customer_name,
                    dcm.customer_names,
                    dcm.customer_ids_csv
             FROM documents d
             LEFT JOIN document_types dt ON dt.id = d.document_type_id
             LEFT JOIN customers c ON c.id = d.customer_id
             LEFT JOIN (
                SELECT dc.document_id,
                       GROUP_CONCAT(DISTINCT c_map.company_name ORDER BY c_map.company_name SEPARATOR ', ') AS customer_names,
                       GROUP_CONCAT(DISTINCT dc.customer_id ORDER BY dc.customer_id SEPARATOR ',') AS customer_ids_csv
                FROM document_customers dc
                LEFT JOIN customers c_map ON c_map.id = dc.customer_id
                GROUP BY dc.document_id
             ) AS dcm ON dcm.document_id = d.id
             {$whereSql}
             ORDER BY d.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            if ($key === 'document_type_id') {
                $listStmt->bindValue(':' . $key, (int) $value, PDO::PARAM_INT);
                continue;
            }
            $listStmt->bindValue(':' . $key, (string) $value);
        }
        $listStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $listStmt->execute();

        $rows = $listStmt->fetchAll();
        $data = array_map(static fn(array $row): array => mapDocumentRow($row), $rows);

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
        error_log('Load documents failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể tải danh sách tài liệu: ' . $e->getMessage(),
        ]);
    }
}

if ($method === 'POST') {
    $payload = validateDocumentInput(getRequestBody(), false);

    try {
        $pdo->beginTransaction();
        $creatorId = resolveCreatorUserId($pdo);
        $stmt = $pdo->prepare(
            "INSERT INTO documents
             (document_code, title, document_type_id, customer_id, file_path, file_name, file_size, mime_type, status, created_by)
             VALUES
             (:document_code, :title, :document_type_id, :customer_id, :file_path, :file_name, :file_size, :mime_type, :status, :created_by)"
        );
        $stmt->execute([
            'document_code' => generateDocumentCode(),
            'title' => $payload['title'],
            'document_type_id' => $payload['document_type_id'],
            'customer_id' => $payload['customer_id'],
            'file_path' => $payload['file_path'],
            'file_name' => $payload['file_name'],
            'file_size' => $payload['file_size'],
            'mime_type' => $payload['mime_type'],
            'status' => $payload['status'],
            'created_by' => $creatorId,
        ]);
        $newId = (int) $pdo->lastInsertId();
        syncDocumentCustomers($pdo, $newId, $payload['customer_ids']);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
            respondJson(409, [
                'success' => false,
                'message' => 'Mã tài liệu bị trùng.',
            ]);
        }
        error_log('Create document failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể tạo tài liệu: ' . $e->getMessage(),
        ]);
    }

    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $newId]);
    $row = $stmt->fetch();

    respondJson(201, [
        'success' => true,
        'message' => 'Tạo tài liệu thành công.',
        'data' => mapDocumentRow($row ?: []),
    ]);
}

if ($method === 'PUT') {
    $payload = validateDocumentInput(getRequestBody(), true);

    $existsStmt = $pdo->prepare("SELECT id FROM documents WHERE id = :id LIMIT 1");
    $existsStmt->execute(['id' => $payload['id']]);
    if (!$existsStmt->fetch()) {
        respondJson(404, [
            'success' => false,
            'message' => 'Không tìm thấy tài liệu.',
        ]);
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            "UPDATE documents
             SET title = :title,
                 document_type_id = :document_type_id,
                 customer_id = :customer_id,
                 file_path = :file_path,
                 file_name = :file_name,
                 file_size = :file_size,
                 mime_type = :mime_type,
                 status = :status
             WHERE id = :id"
        );
        $stmt->execute([
            'id' => $payload['id'],
            'title' => $payload['title'],
            'document_type_id' => $payload['document_type_id'],
            'customer_id' => $payload['customer_id'],
            'file_path' => $payload['file_path'],
            'file_name' => $payload['file_name'],
            'file_size' => $payload['file_size'],
            'mime_type' => $payload['mime_type'],
            'status' => $payload['status'],
        ]);
        syncDocumentCustomers($pdo, $payload['id'], $payload['customer_ids']);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Update document failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể cập nhật tài liệu: ' . $e->getMessage(),
        ]);
    }

    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $payload['id']]);
    $row = $stmt->fetch();

    respondJson(200, [
        'success' => true,
        'message' => 'Cập nhật tài liệu thành công.',
        'data' => mapDocumentRow($row ?: []),
    ]);
}

if ($method === 'DELETE') {
    $body = getRequestBody();
    $id = (int) ($body['id'] ?? ($_GET['id'] ?? 0));
    if ($id <= 0) {
        respondJson(422, [
            'success' => false,
            'message' => 'ID tài liệu không hợp lệ.',
        ]);
    }

    $existsStmt = $pdo->prepare("SELECT id FROM documents WHERE id = :id LIMIT 1");
    $existsStmt->execute(['id' => $id]);
    if (!$existsStmt->fetch()) {
        respondJson(404, [
            'success' => false,
            'message' => 'Không tìm thấy tài liệu.',
        ]);
    }

    $pdo->beginTransaction();
    try {
        $deleteMapStmt = $pdo->prepare("DELETE FROM document_customers WHERE document_id = :document_id");
        $deleteMapStmt->execute(['document_id' => $id]);

        $stmt = $pdo->prepare("DELETE FROM documents WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Delete document failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể xóa tài liệu: ' . $e->getMessage(),
        ]);
    }

    respondJson(200, [
        'success' => true,
        'message' => 'Đã xóa tài liệu.',
    ]);
}

respondJson(405, [
    'success' => false,
    'message' => 'Method not allowed.',
]);
