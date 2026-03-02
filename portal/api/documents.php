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

function mapTypeToSlug(string $typeName): string
{
    $lower = strtolower($typeName);
    if (str_contains($lower, 'invoice') || str_contains($lower, 'hóa đơn') || str_contains($lower, 'hoa don')) {
        return 'invoice';
    }
    if (str_contains($lower, 'packing')) {
        return 'packing';
    }
    if (str_contains($lower, 'certificate') || str_contains($lower, 'chứng nhận') || str_contains($lower, 'c/o') || str_contains($lower, 'co ')) {
        return 'certificate';
    }
    if (str_contains($lower, 'bill') || str_contains($lower, 'lading') || str_contains($lower, 'b/l')) {
        return 'bill';
    }
    return 'other';
}

function resolveFilePath(string $dbPath): ?string
{
    $base = realpath(__DIR__ . '/../uploads/documents');
    if ($base === false || !is_dir($base)) {
        return null;
    }
    $fileName = basename($dbPath);
    if ($fileName === '' || $fileName === '.') {
        return null;
    }
    $fullPath = $base . DIRECTORY_SEPARATOR . $fileName;
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        return null;
    }
    $real = realpath($fullPath);
    if ($real === false || !str_starts_with($real, $base)) {
        return null;
    }
    return $real;
}

// --- Auth check ---
if (empty($_SESSION['nhutin_portal_logged_in']) || empty($_SESSION['nhutin_portal_user'])) {
    respondJson(401, ['success' => false, 'message' => 'Vui lòng đăng nhập.']);
}

$customerId = (int) ($_SESSION['nhutin_portal_user']['customer_id'] ?? 0);
if ($customerId <= 0) {
    respondJson(403, ['success' => false, 'message' => 'Không xác định được khách hàng.']);
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

try {
    $pdo = getDbConnection();
} catch (Throwable $e) {
    error_log('Portal documents DB: ' . $e->getMessage());
    respondJson(500, ['success' => false, 'message' => 'Lỗi kết nối. Vui lòng thử lại sau.']);
}

// --- GET ?action=download&id=X ---
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'download') {
    $id = (int) ($_GET['id'] ?? 0);
    $disposition = (string) ($_GET['disposition'] ?? 'attachment');
    if ($id <= 0) {
        respondJson(400, ['success' => false, 'message' => 'ID tài liệu không hợp lệ.']);
    }

    $stmt = $pdo->prepare(
        "SELECT d.id, d.file_path, d.file_name, d.mime_type
         FROM documents d
         LEFT JOIN document_customers dc ON dc.document_id = d.id AND dc.customer_id = :cid1
         WHERE d.id = :id
           AND d.status = 'published'
           AND (d.customer_id = :cid2 OR dc.customer_id = :cid3)
         LIMIT 1"
    );
    $stmt->execute(['id' => $id, 'cid1' => $customerId, 'cid2' => $customerId, 'cid3' => $customerId]);
    $row = $stmt->fetch();

    if (!$row) {
        respondJson(404, ['success' => false, 'message' => 'Không tìm thấy tài liệu hoặc bạn không có quyền truy cập.']);
    }

    $physicalPath = resolveFilePath((string) $row['file_path']);
    if ($physicalPath === null) {
        respondJson(404, ['success' => false, 'message' => 'File tài liệu không tồn tại.']);
    }

    $fileName = (string) ($row['file_name']);
    $mimeType = trim((string) ($row['mime_type'] ?? ''));
    if ($mimeType === '') {
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $mimeMap = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
        ];
        $mimeType = $mimeMap[$ext] ?? 'application/octet-stream';
    }

    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: ' . ($disposition === 'inline' ? 'inline' : 'attachment') . '; filename="' . str_replace('"', '\\"', $fileName) . '"');
    header('Content-Length: ' . (string) filesize($physicalPath));
    readfile($physicalPath);
    exit;
}

// --- GET list (default) ---
if ($method === 'GET') {
    try {
        $search = trim((string) ($_GET['search'] ?? ''));
        $typeSlug = trim((string) ($_GET['type'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = max(1, min((int) ($_GET['limit'] ?? 10), 50));
        $offset = ($page - 1) * $limit;

        $where = [
            "d.status = 'published'",
            "(d.customer_id = :cid1 OR EXISTS (SELECT 1 FROM document_customers dc WHERE dc.document_id = d.id AND dc.customer_id = :cid2))",
        ];
        $params = ['cid1' => $customerId, 'cid2' => $customerId];

        if ($search !== '') {
            $where[] = '(d.title LIKE :keyword OR d.document_code LIKE :keyword2 OR d.file_name LIKE :keyword3)';
            $params['keyword'] = '%' . $search . '%';
            $params['keyword2'] = '%' . $search . '%';
            $params['keyword3'] = '%' . $search . '%';
        }

        if ($typeSlug !== '' && $typeSlug !== 'all') {
            $typeConditions = [
                'invoice' => "(LOWER(COALESCE(dt.name,'')) LIKE '%invoice%' OR LOWER(COALESCE(dt.name,'')) LIKE '%hóa đơn%' OR LOWER(COALESCE(dt.name,'')) LIKE '%hoa don%')",
                'packing' => "LOWER(COALESCE(dt.name,'')) LIKE '%packing%'",
                'certificate' => "(LOWER(COALESCE(dt.name,'')) LIKE '%certificate%' OR LOWER(COALESCE(dt.name,'')) LIKE '%chứng nhận%' OR LOWER(COALESCE(dt.name,'')) LIKE '%c/o%' OR LOWER(COALESCE(dt.name,'')) LIKE '%chung nhan%')",
                'bill' => "(LOWER(COALESCE(dt.name,'')) LIKE '%bill%' OR LOWER(COALESCE(dt.name,'')) LIKE '%lading%' OR LOWER(COALESCE(dt.name,'')) LIKE '%b/l%')",
            ];
            if (isset($typeConditions[$typeSlug])) {
                $where[] = $typeConditions[$typeSlug];
            }
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $joinDt = ($typeSlug !== '' && $typeSlug !== 'all') ? ' LEFT JOIN document_types dt ON dt.id = d.document_type_id' : '';
        $countFrom = "documents d{$joinDt}";

        $countStmt = $pdo->prepare(
            "SELECT COUNT(DISTINCT d.id) FROM {$countFrom} {$whereSql}"
        );
        foreach ($params as $key => $val) {
            $countStmt->bindValue(':' . $key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $listStmt = $pdo->prepare(
            "SELECT d.id, d.document_code, d.title, d.document_type_id,
                    dt.name AS document_type_name,
                    d.file_name, d.file_size, d.mime_type, d.metadata, d.created_at
             FROM documents d
             LEFT JOIN document_types dt ON dt.id = d.document_type_id
             {$whereSql}
             ORDER BY d.created_at DESC
             LIMIT :lim OFFSET :off"
        );
        foreach ($params as $key => $val) {
            $listStmt->bindValue(':' . $key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $listStmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $listStmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $listStmt->execute();

        $rows = $listStmt->fetchAll();
        $data = [];
        foreach ($rows as $r) {
            $typeName = (string) ($r['document_type_name'] ?? '');
            $slug = mapTypeToSlug($typeName);
            $meta = $r['metadata'] ?? null;
            $metaArr = is_string($meta) ? json_decode($meta, true) : (is_array($meta) ? $meta : []);
            $data[] = [
                'id' => (int) $r['id'],
                'document_code' => (string) ($r['document_code'] ?? ''),
                'title' => (string) ($r['title'] ?? ''),
                'document_type_name' => $typeName,
                'type_slug' => $slug,
                'file_name' => (string) ($r['file_name'] ?? ''),
                'file_size' => (int) ($r['file_size'] ?? 0),
                'created_at' => (string) ($r['created_at'] ?? ''),
                'metadata' => is_array($metaArr) ? $metaArr : [],
            ];
        }

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
        error_log('Portal documents list: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể tải danh sách tài liệu: ' . $e->getMessage(),
        ]);
    }
}

respondJson(405, ['success' => false, 'message' => 'Method not allowed.']);
