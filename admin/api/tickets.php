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

const TICKET_STATUSES = ['open', 'progress', 'resolved'];
const TICKET_PRIORITIES = ['high', 'medium', 'low'];

function respondJson(int $statusCode, array $payload): void
{
    if (ob_get_length()) {
        ob_clean();
    }
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

function ensureAdminSession(): void
{
    if (empty($_SESSION['nhutin_admin_logged_in']) || empty($_SESSION['nhutin_admin'])) {
        respondJson(401, [
            'success' => false,
            'message' => 'Vui lòng đăng nhập lại.',
        ]);
    }
}

function stringLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function normalizeTicketStatus(string $status): string
{
    $n = strtolower(trim($status));
    return in_array($n, TICKET_STATUSES, true) ? $n : 'open';
}

function normalizeTicketPriority(string $priority): string
{
    $n = strtolower(trim($priority));
    return in_array($n, TICKET_PRIORITIES, true) ? $n : 'medium';
}

function ensureTicketsSchema(PDO $pdo): void
{
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS tickets (
                id SERIAL PRIMARY KEY,
                ticket_code VARCHAR(50) NOT NULL UNIQUE,
                title VARCHAR(255) NOT NULL,
                description TEXT NULL,
                customer_id INT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'open',
                priority VARCHAR(20) NOT NULL DEFAULT 'medium',
                assigned_to INT NULL,
                created_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        );
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_tickets_status ON tickets (status)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_tickets_priority ON tickets (priority)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_tickets_created ON tickets (created_at)");
    } catch (Throwable $e) {}

    // Lưu trao đổi / phản hồi cho từng ticket
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS ticket_replies (
                id SERIAL PRIMARY KEY,
                ticket_id INT NOT NULL,
                author_type VARCHAR(20) NOT NULL DEFAULT 'admin',
                author_id INT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        );
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ticket_replies_ticket ON ticket_replies (ticket_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ticket_replies_created ON ticket_replies (created_at)");
    } catch (Throwable $e) {}

    try {
        $pdo->exec("ALTER TABLE tickets ADD COLUMN IF NOT EXISTS created_by INT NULL");
    } catch (Throwable $e) {}
}

function resolveCreatorId(): ?int
{
    $admin = is_array($_SESSION['nhutin_admin'] ?? null) ? $_SESSION['nhutin_admin'] : [];
    $id = (int) ($admin['id'] ?? 0);
    return $id > 0 ? $id : null;
}

function generateTicketCode(PDO $pdo): string
{
    $year = date('Y');
    $stmt = $pdo->prepare(
        "SELECT id FROM tickets WHERE ticket_code LIKE :pattern ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute(['pattern' => "TICKET-{$year}-%"]);
    $last = $stmt->fetch();
    $seq = $last ? ((int) ($last['id'] ?? 0)) + 1 : 1;
    return sprintf('TICKET-%s-%05d', $year, $seq);
}

function mapTicketRow(array $row): array
{
    return [
        'id' => (int) ($row['id'] ?? 0),
        'ticket_code' => (string) ($row['ticket_code'] ?? ''),
        'title' => (string) ($row['title'] ?? ''),
        'description' => (string) ($row['description'] ?? ''),
        'customer_id' => $row['customer_id'] !== null ? (int) $row['customer_id'] : null,
        'customer_name' => (string) ($row['customer_name'] ?? ''),
        'status' => normalizeTicketStatus((string) ($row['status'] ?? 'open')),
        'priority' => normalizeTicketPriority((string) ($row['priority'] ?? 'medium')),
        'assigned_to' => $row['assigned_to'] !== null ? (int) $row['assigned_to'] : null,
        'created_at' => (string) ($row['created_at'] ?? ''),
        'updated_at' => (string) ($row['updated_at'] ?? ''),
    ];
}

function validateTicketInput(array $input, bool $isUpdate = false): array
{
    $id = (int) ($input['id'] ?? 0);
    $title = trim((string) ($input['title'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $customerId = isset($input['customer_id']) && $input['customer_id'] !== '' ? (int) $input['customer_id'] : null;
    $status = normalizeTicketStatus((string) ($input['status'] ?? 'open'));
    $priority = normalizeTicketPriority((string) ($input['priority'] ?? 'medium'));

    if ($isUpdate && $id <= 0) {
        respondJson(422, ['success' => false, 'message' => 'ID ticket không hợp lệ.']);
    }
    if ($title === '' || stringLength($title) > 255) {
        respondJson(422, ['success' => false, 'message' => 'Tiêu đề ticket không hợp lệ.']);
    }
    if (stringLength($description) > 65535) {
        respondJson(422, ['success' => false, 'message' => 'Mô tả quá dài.']);
    }

    return [
        'id' => $id,
        'title' => $title,
        'description' => $description,
        'customer_id' => $customerId,
        'status' => $status,
        'priority' => $priority,
    ];
}

ensureAdminOrStaff();

try {
    $pdo = getDbConnection();
    ensureTicketsSchema($pdo);
} catch (Throwable $e) {
    error_log('Tickets DB init failed: ' . $e->getMessage());
    respondJson(500, [
        'success' => false,
        'message' => 'Lỗi khởi tạo cơ sở dữ liệu: ' . $e->getMessage(),
    ]);
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$action = (string) ($_GET['action'] ?? '');

if ($method === 'GET' && $action === 'stats') {
    try {
        $counts = ['open' => 0, 'progress' => 0, 'resolved' => 0];
        foreach (array_keys($counts) as $s) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE status = :s");
            $stmt->execute(['s' => $s]);
            $counts[$s] = (int) $stmt->fetchColumn();
        }
        respondJson(200, ['success' => true, 'stats' => $counts]);
    } catch (Throwable $e) {
        respondJson(500, ['success' => false, 'message' => 'Không thể tải thống kê.']);
    }
}

if ($method === 'GET' && $action === 'fix_schema') {
    respondJson(200, [
        'success' => true,
        'message' => 'Schema đã dùng PostgreSQL-compatible, không cần fix thủ công.',
    ]);
}

if ($method === 'GET' && $action === 'meta') {
    try {
        $customers = [];
        $stmt = $pdo->query("SELECT id, company_name FROM customers ORDER BY company_name ASC");
        while ($row = $stmt->fetch()) {
            $customers[] = [
                'id' => (int) $row['id'],
                'name' => (string) ($row['company_name'] ?? ''),
            ];
        }
        respondJson(200, ['success' => true, 'customers' => $customers]);
    } catch (Throwable $e) {
        respondJson(500, ['success' => false, 'message' => 'Không thể tải meta.']);
    }
}

if ($method === 'GET') {
    try {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare(
                "SELECT t.*, c.company_name AS customer_name FROM tickets t " .
                "LEFT JOIN customers c ON t.customer_id = c.id WHERE t.id = :id LIMIT 1"
            );
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            if (!$row) {
                respondJson(404, ['success' => false, 'message' => 'Không tìm thấy ticket.']);
            }
            respondJson(200, ['success' => true, 'data' => mapTicketRow($row)]);
        }

        $search = trim((string) ($_GET['search'] ?? ''));
        $status = isset($_GET['status']) && (string) $_GET['status'] !== '' ? normalizeTicketStatus((string) $_GET['status']) : '';
        $priority = isset($_GET['priority']) && (string) $_GET['priority'] !== '' ? normalizeTicketPriority((string) $_GET['priority']) : '';
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = max(1, min((int) ($_GET['limit'] ?? 10), 50));
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if ($search !== '') {
            $kw = '%' . $search . '%';
            $where[] = '(t.title LIKE :kw1 OR t.description LIKE :kw2 OR t.ticket_code LIKE :kw3 OR c.company_name LIKE :kw4)';
            $params['kw1'] = $kw;
            $params['kw2'] = $kw;
            $params['kw3'] = $kw;
            $params['kw4'] = $kw;
        }
        if ($status !== '') {
            $where[] = 't.status = :status';
            $params['status'] = $status;
        }
        if ($priority !== '') {
            $where[] = 't.priority = :priority';
            $params['priority'] = $priority;
        }

        $whereSql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
        $joinSql = "SELECT t.*, c.company_name AS customer_name FROM tickets t LEFT JOIN customers c ON t.customer_id = c.id {$whereSql}";

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM tickets t LEFT JOIN customers c ON t.customer_id = c.id {$whereSql}");
        foreach ($params as $k => $v) {
            $countStmt->bindValue(':' . $k, $v);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $listStmt = $pdo->prepare("{$joinSql} ORDER BY t.created_at DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $k => $v) {
            $listStmt->bindValue(':' . $k, $v);
        }
        $listStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $listStmt->execute();

        $rows = $listStmt->fetchAll();
        $data = array_map(static fn(array $r): array => mapTicketRow($r), $rows);

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
        error_log('Load tickets failed: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể tải danh sách ticket: ' . $e->getMessage()]);
    }
}

if ($method === 'POST') {
    $payload = validateTicketInput(getRequestBody(), false);

    try {
        $code = generateTicketCode($pdo);
        $createdBy = resolveCreatorId() ?? 1;
        $stmt = $pdo->prepare(
            "INSERT INTO tickets (ticket_code, title, description, customer_id, status, priority, assigned_to, created_by) " .
            "VALUES (:ticket_code, :title, :description, :customer_id, :status, :priority, NULL, :created_by)"
        );
        $stmt->execute([
            'ticket_code' => $code,
            'title' => $payload['title'],
            'description' => $payload['description'],
            'customer_id' => $payload['customer_id'],
            'status' => $payload['status'],
            'priority' => $payload['priority'],
            'created_by' => $createdBy,
        ]);
        $newId = (int) $pdo->lastInsertId();
        logActivity($pdo, 'create', 'ticket', $newId, 'Tạo ticket: ' . $payload['title']);
    } catch (Throwable $e) {
        error_log('Create ticket failed: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể tạo ticket: ' . $e->getMessage()]);
    }

    $stmt = $pdo->prepare(
        "SELECT t.*, c.company_name AS customer_name FROM tickets t LEFT JOIN customers c ON t.customer_id = c.id WHERE t.id = :id LIMIT 1"
    );
    $stmt->execute(['id' => $newId]);
    $row = $stmt->fetch();

    respondJson(201, [
        'success' => true,
        'message' => 'Tạo ticket thành công.',
        'data' => mapTicketRow($row ?: []),
    ]);
}

if ($method === 'PUT') {
    $payload = validateTicketInput(getRequestBody(), true);

    $existsStmt = $pdo->prepare("SELECT id FROM tickets WHERE id = :id LIMIT 1");
    $existsStmt->execute(['id' => $payload['id']]);
    if (!$existsStmt->fetch()) {
        respondJson(404, ['success' => false, 'message' => 'Không tìm thấy ticket.']);
    }

    try {
        // Chỉ cho phép cập nhật trạng thái và mức độ ưu tiên từ phía admin,
        // không cho sửa tiêu đề, mô tả hoặc khách hàng sau khi ticket đã được tạo.
        $stmt = $pdo->prepare(
            "UPDATE tickets SET status = :status, priority = :priority WHERE id = :id"
        );
        $stmt->execute([
            'id' => $payload['id'],
            'status' => $payload['status'],
            'priority' => $payload['priority'],
        ]);
        logActivity($pdo, 'update', 'ticket', $payload['id'], 'Cập nhật ticket (trạng thái: ' . $payload['status'] . ', ưu tiên: ' . $payload['priority'] . ')');
    } catch (Throwable $e) {
        error_log('Update ticket failed: ' . $e->getMessage());
        respondJson(500, ['success' => false, 'message' => 'Không thể cập nhật ticket: ' . $e->getMessage()]);
    }

    $stmt = $pdo->prepare(
        "SELECT t.*, c.company_name AS customer_name FROM tickets t LEFT JOIN customers c ON t.customer_id = c.id WHERE t.id = :id LIMIT 1"
    );
    $stmt->execute(['id' => $payload['id']]);
    $row = $stmt->fetch();

    respondJson(200, [
        'success' => true,
        'message' => 'Cập nhật ticket thành công.',
        'data' => mapTicketRow($row ?: []),
    ]);
}

if ($method === 'DELETE') {
    $body = getRequestBody();
    $id = (int) ($body['id'] ?? ($_GET['id'] ?? 0));
    if ($id <= 0) {
        respondJson(422, ['success' => false, 'message' => 'ID ticket không hợp lệ.']);
    }

    $stmt = $pdo->prepare("DELETE FROM tickets WHERE id = :id");
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() === 0) {
        respondJson(404, ['success' => false, 'message' => 'Không tìm thấy ticket.']);
    }

    logActivity($pdo, 'delete', 'ticket', $id, 'Xóa ticket #' . $id);
    respondJson(200, ['success' => true, 'message' => 'Đã xóa ticket.']);
}

respondJson(405, ['success' => false, 'message' => 'Method not allowed.']);
