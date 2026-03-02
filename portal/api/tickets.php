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

if (empty($_SESSION['nhutin_portal_logged_in']) || empty($_SESSION['nhutin_portal_user'])) {
    respondJson(401, ['success' => false, 'message' => 'Vui lòng đăng nhập.']);
}

$customerId = (int) ($_SESSION['nhutin_portal_user']['customer_id'] ?? 0);
$userId = (int) ($_SESSION['nhutin_portal_user']['id'] ?? 0);
if ($customerId <= 0) {
    respondJson(403, ['success' => false, 'message' => 'Không xác định được khách hàng.']);
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

try {
    $pdo = getDbConnection();
} catch (Throwable $e) {
    error_log('Portal tickets DB: ' . $e->getMessage());
    respondJson(500, ['success' => false, 'message' => 'Lỗi kết nối. Vui lòng thử lại sau.']);
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

// --- GET ?action=stats ---
if ($method === 'GET' && ($_GET['action'] ?? '') === 'stats') {
    try {
        $counts = ['open' => 0, 'progress' => 0, 'resolved' => 0];
        foreach (array_keys($counts) as $s) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE customer_id = :cid AND status = :s");
            $stmt->execute(['cid' => $customerId, 's' => $s]);
            $counts[$s] = (int) $stmt->fetchColumn();
        }
        $counts['total'] = $counts['open'] + $counts['progress'] + $counts['resolved'];
        respondJson(200, ['success' => true, 'stats' => $counts]);
    } catch (Throwable $e) {
        respondJson(500, ['success' => false, 'message' => 'Không thể tải thống kê.']);
    }
}

// --- GET list ---
if ($method === 'GET') {
    try {
        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = max(1, min((int) ($_GET['limit'] ?? 10), 50));
        $offset = ($page - 1) * $limit;

        $where = ['t.customer_id = :cid'];
        $params = ['cid' => $customerId];

        if ($search !== '') {
            $where[] = '(t.title LIKE :kw1 OR t.description LIKE :kw2 OR t.ticket_code LIKE :kw3)';
            $params['kw1'] = '%' . $search . '%';
            $params['kw2'] = '%' . $search . '%';
            $params['kw3'] = '%' . $search . '%';
        }
        if ($status !== '' && in_array($status, ['open', 'progress', 'resolved'], true)) {
            $where[] = 't.status = :status';
            $params['status'] = $status;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM tickets t {$whereSql}");
        foreach ($params as $k => $v) {
            $countStmt->bindValue(':' . $k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $listStmt = $pdo->prepare(
            "SELECT t.id, t.ticket_code, t.title, t.description, t.status, t.priority, t.created_at, t.updated_at
             FROM tickets t
             {$whereSql}
             ORDER BY t.created_at DESC
             LIMIT :lim OFFSET :off"
        );
        foreach ($params as $k => $v) {
            $listStmt->bindValue(':' . $k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $listStmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $listStmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $listStmt->execute();

        $rows = $listStmt->fetchAll();
        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'id' => (int) $r['id'],
                'ticket_code' => (string) ($r['ticket_code'] ?? ''),
                'title' => (string) ($r['title'] ?? ''),
                'description' => (string) ($r['description'] ?? ''),
                'status' => (string) ($r['status'] ?? 'open'),
                'priority' => (string) ($r['priority'] ?? 'medium'),
                'created_at' => (string) ($r['created_at'] ?? ''),
                'updated_at' => (string) ($r['updated_at'] ?? ''),
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
        error_log('Portal tickets list: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể tải danh sách ticket: ' . $e->getMessage()]);
    }
}

// --- POST: tạo ticket mới ---
if ($method === 'POST') {
    $body = getRequestBody();
    $title = trim((string) ($body['title'] ?? ''));
    $description = trim((string) ($body['description'] ?? ''));
    $priority = strtolower(trim((string) ($body['priority'] ?? 'medium')));

    if ($title === '') {
        respondJson(422, ['success' => false, 'message' => 'Vui lòng nhập tiêu đề ticket.']);
    }
    if (!in_array($priority, ['high', 'medium', 'low'], true)) {
        $priority = 'medium';
    }

    try {
        $year = date('Y');
        $seqStmt = $pdo->prepare("SELECT id FROM tickets WHERE ticket_code LIKE :pattern ORDER BY id DESC LIMIT 1");
        $seqStmt->execute(['pattern' => "TICKET-{$year}-%"]);
        $last = $seqStmt->fetch();
        $seq = $last ? ((int) ($last['id'] ?? 0)) + 1 : 1;
        $code = sprintf('TICKET-%s-%05d', $year, $seq);

        $stmt = $pdo->prepare(
            "INSERT INTO tickets (ticket_code, title, description, customer_id, status, priority, created_by)
             VALUES (:code, :title, :description, :cid, 'open', :priority, :uid)"
        );
        $stmt->execute([
            'code' => $code,
            'title' => $title,
            'description' => $description,
            'cid' => $customerId,
            'priority' => $priority,
            'uid' => $userId > 0 ? $userId : null,
        ]);
        $newId = (int) $pdo->lastInsertId();

        $fetchStmt = $pdo->prepare("SELECT * FROM tickets WHERE id = :id LIMIT 1");
        $fetchStmt->execute(['id' => $newId]);
        $row = $fetchStmt->fetch();

        respondJson(201, [
            'success' => true,
            'message' => 'Ticket đã được gửi thành công!',
            'data' => [
                'id' => (int) ($row['id'] ?? $newId),
                'ticket_code' => (string) ($row['ticket_code'] ?? $code),
                'title' => (string) ($row['title'] ?? $title),
                'description' => (string) ($row['description'] ?? $description),
                'status' => 'open',
                'priority' => $priority,
                'created_at' => (string) ($row['created_at'] ?? ''),
            ],
        ]);
    } catch (Throwable $e) {
        error_log('Portal create ticket: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể tạo ticket: ' . $e->getMessage()]);
    }
}

respondJson(405, ['success' => false, 'message' => 'Method not allowed.']);
