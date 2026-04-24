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

ensureAdminOrStaff();

try {
    $pdo = getDbConnection();
} catch (Throwable $e) {
    respondJson(500, ['success' => false, 'message' => 'Không thể kết nối cơ sở dữ liệu.']);
}

$stats = [
    'customers' => 0,
    'documents' => 0,
    'users' => 0,
    'posts' => 0,
    'tickets_open' => 0,
    'tickets_progress' => 0,
    'tickets_resolved' => 0,
    'tickets_pending' => 0,
];

$recentCustomers = [];
$recentTickets = [];

try {
    // Count customers
    $stmt = $pdo->query("SELECT COUNT(*) FROM customers");
    if ($stmt) $stats['customers'] = (int) $stmt->fetchColumn();
} catch (Throwable $e) {}

try {
    // Count documents
    $stmt = $pdo->query("SELECT COUNT(*) FROM documents");
    if ($stmt) $stats['documents'] = (int) $stmt->fetchColumn();
} catch (Throwable $e) {}

try {
    // Count staff users (users table with role = 'staff')
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'staff'");
    if ($stmt) {
        $stats['users'] = (int) $stmt->fetchColumn();
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        if ($stmt) $stats['users'] = (int) $stmt->fetchColumn();
    }
} catch (Throwable $e) {}

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts");
    if ($stmt) $stats['posts'] = (int) $stmt->fetchColumn();
} catch (Throwable $e) {}

try {
    foreach (['open', 'progress', 'resolved'] as $s) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE status = :s");
        $stmt->execute(['s' => $s]);
        $stats["tickets_{$s}"] = (int) $stmt->fetchColumn();
    }
    $stats['tickets_pending'] = $stats['tickets_open'] + $stats['tickets_progress'];
} catch (Throwable $e) {}

try {
    $stmt = $pdo->query(
        "SELECT id, company_name, email, status, logo_url, created_at FROM customers ORDER BY created_at DESC LIMIT 10"
    );
    while ($row = $stmt->fetch()) {
        $avatar = trim((string) ($row['logo_url'] ?? ''));
        if ($avatar === '') $avatar = '../img/default.png';
        $recentCustomers[] = [
            'id' => (int) $row['id'],
            'name' => (string) ($row['company_name'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
            'avatar_url' => $avatar,
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }
} catch (Throwable $e) {}

try {
    $stmt = $pdo->query(
        "SELECT t.id, t.ticket_code, t.title, t.status, t.created_at, c.company_name AS customer_name " .
        "FROM tickets t LEFT JOIN customers c ON t.customer_id = c.id ORDER BY t.created_at DESC LIMIT 8"
    );
    while ($row = $stmt->fetch()) {
        $recentTickets[] = [
            'id' => (int) $row['id'],
            'ticket_code' => (string) ($row['ticket_code'] ?? ''),
            'title' => (string) ($row['title'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
            'customer_name' => (string) ($row['customer_name'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }
} catch (Throwable $e) {}

respondJson(200, [
    'success' => true,
    'data' => [
        'stats' => $stats,
        'recent_customers' => $recentCustomers,
        'recent_tickets' => $recentTickets,
    ],
]);
