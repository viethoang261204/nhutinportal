<?php
/**
 * Debug script: Kiểm tra tài liệu Portal lấy từ DB
 * Gọi: portal/api/documents-debug.php (sau khi đăng nhập portal)
 */
declare(strict_types=1);

require_once __DIR__ . '/../../admin/config/db.php';

header('Content-Type: text/html; charset=utf-8');

session_name('nhutin_portal_session');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

echo "<h2>Debug: Portal Documents</h2>";
echo "<pre>";

// 1. Session
echo "=== 1. SESSION ===\n";
if (empty($_SESSION['nhutin_portal_logged_in'])) {
    echo "Chưa đăng nhập. Vui lòng đăng nhập portal trước.\n";
    exit;
}
$customerId = (int) ($_SESSION['nhutin_portal_user']['customer_id'] ?? 0);
$userInfo = $_SESSION['nhutin_portal_user'] ?? [];
echo "customer_id: " . $customerId . "\n";
echo "user: " . json_encode($userInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

if ($customerId <= 0) {
    echo "customer_id = 0 → Không xác định được khách hàng.\n";
    exit;
}

// 2. DB
try {
    $pdo = getDbConnection();
} catch (Throwable $e) {
    echo "Lỗi kết nối DB: " . $e->getMessage() . "\n";
    exit;
}

// 3. Tổng documents trong DB
echo "=== 2. TỔNG TÀI LIỆU TRONG DB ===\n";
$all = $pdo->query("SELECT status, COUNT(*) as cnt FROM documents GROUP BY status")->fetchAll();
foreach ($all as $r) {
    echo "  status=" . $r['status'] . ": " . $r['cnt'] . " bản ghi\n";
}
$total = $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();
echo "  Tổng: {$total}\n\n";

// 4. Documents published
echo "=== 3. DOCUMENTS STATUS=published ===\n";
$pub = $pdo->query("SELECT id, document_code, title, customer_id, status FROM documents WHERE status = 'published'")->fetchAll();
echo "Số bản ghi published: " . count($pub) . "\n";
foreach ($pub as $r) {
    echo "  id={$r['id']} customer_id={$r['customer_id']} | {$r['title']}\n";
}
echo "\n";

// 5. document_customers cho customer này
echo "=== 4. document_customers (customer_id=$customerId) ===\n";
$stmt = $pdo->prepare("SELECT document_id, customer_id FROM document_customers WHERE customer_id = ?");
$stmt->execute([$customerId]);
$dcRows = $stmt->fetchAll();
echo "Số mapping: " . count($dcRows) . "\n";
foreach ($dcRows as $r) {
    echo "  document_id={$r['document_id']} customer_id={$r['customer_id']}\n";
}
echo "\n";

// 6. Query giống portal – documents mà customer này được xem
echo "=== 5. KẾT QUẢ QUERY PORTAL (customer_id=$customerId) ===\n";
$where = [
    "d.status = 'published'",
    "(d.customer_id = :cid OR EXISTS (SELECT 1 FROM document_customers dc WHERE dc.document_id = d.id AND dc.customer_id = :cid))",
];
$whereSql = 'WHERE ' . implode(' AND ', $where);
$stmt = $pdo->prepare(
    "SELECT d.id, d.document_code, d.title, d.customer_id, d.status, d.file_path
     FROM documents d
     {$whereSql}
     ORDER BY d.created_at DESC"
);
$stmt->execute(['cid' => $customerId]);
$rows = $stmt->fetchAll();
echo "Số tài liệu trả về: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "  id={$r['id']} customer_id={$r['customer_id']} | {$r['title']}\n";
}
echo "\n";

// 7. Documents có customer_id = customer này
echo "=== 6. documents.customer_id = $customerId ===\n";
$stmt = $pdo->prepare("SELECT id, title, status FROM documents WHERE customer_id = ?");
$stmt->execute([$customerId]);
$byCid = $stmt->fetchAll();
echo "Số bản ghi: " . count($byCid) . "\n";
foreach ($byCid as $r) {
    echo "  id={$r['id']} status={$r['status']} | {$r['title']}\n";
}
echo "\n";

// 8. Khách hàng - email khớp?
echo "=== 7. KHÁCH HÀNG (id=$customerId) ===\n";
$stmt = $pdo->prepare("SELECT id, customer_code, company_name, email FROM customers WHERE id = ?");
$stmt->execute([$customerId]);
$cust = $stmt->fetch();
if ($cust) {
    echo json_encode($cust, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo "Không tìm thấy customer id=$customerId trong bảng customers.\n";
}

echo "</pre>";
echo "<p><a href='documents.html'>← Quay lại Tài liệu</a></p>";
