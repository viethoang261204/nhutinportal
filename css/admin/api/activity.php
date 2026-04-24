<?php
declare(strict_types=1);

@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ob_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth-helper.php';

ob_clean();
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
    if (ob_get_length()) ob_clean();
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    ensureAdminOnly();
} catch (Throwable $e) {
    error_log('Activity auth failed: ' . $e->getMessage());
    respondJson(401, ['success' => false, 'message' => 'Vui lòng đăng nhập lại.']);
}

try {
    $pdo = getDbConnection();
    ensureActivityLogSchema($pdo);
} catch (Throwable $e) {
    error_log('Activity DB init failed: ' . $e->getMessage());
    respondJson(500, ['success' => false, 'message' => 'Không thể kết nối cơ sở dữ liệu.']);
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ($method !== 'GET') {
    respondJson(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = max(1, min((int) ($_GET['limit'] ?? 30), 100));
$offset = (int) (($page - 1) * $limit);
$entityType = trim((string) ($_GET['entity_type'] ?? ''));
$search = trim((string) ($_GET['search'] ?? ''));

$where = [];
$params = [];

if ($entityType !== '') {
    $where[] = 'entity_type = :entity_type';
    $params['entity_type'] = $entityType;
}
if ($search !== '') {
    $where[] = '(user_name LIKE :search OR action LIKE :search2 OR details LIKE :search3)';
    $params['search'] = '%' . $search . '%';
    $params['search2'] = '%' . $search . '%';
    $params['search3'] = '%' . $search . '%';
}

$whereSql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

try {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs {$whereSql}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $stmt = $pdo->prepare(
        "SELECT id, user_id, user_name, user_role, action, entity_type, entity_id,
                COALESCE(details, description) AS details, ip_address, created_at
         FROM activity_logs {$whereSql}
         ORDER BY created_at DESC
         LIMIT " . (int) $limit . " OFFSET " . (int) $offset
    );
    $stmt->execute($params);

    $items = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $items[] = [
            'id' => (int) ($row['id'] ?? 0),
            'user_id' => (int) ($row['user_id'] ?? 0),
            'user_name' => (string) ($row['user_name'] ?? ''),
            'user_role' => (string) ($row['user_role'] ?? ''),
            'action' => (string) ($row['action'] ?? ''),
            'entity_type' => (string) ($row['entity_type'] ?? ''),
            'entity_id' => isset($row['entity_id']) && $row['entity_id'] !== '' ? (int) $row['entity_id'] : null,
            'details' => isset($row['details']) ? (string) $row['details'] : null,
            'ip_address' => isset($row['ip_address']) ? (string) $row['ip_address'] : null,
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }

    respondJson(200, [
        'success' => true,
        'data' => $items,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => (int) ceil($total / $limit),
        ],
    ]);
} catch (Throwable $e) {
    error_log('Activity fetch failed: ' . $e->getMessage());
    respondJson(500, ['success' => false, 'message' => 'Không thể tải nhật ký: ' . $e->getMessage()]);
}
