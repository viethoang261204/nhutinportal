<?php
declare(strict_types=1);

/**
 * API công khai cho bài viết - KHÔNG yêu cầu đăng nhập.
 * Chỉ trả về bài viết status=published.
 * Dùng cho news.html (trang web chính).
 */
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');

const DEFAULT_THUMBNAIL = 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&h=400&fit=crop';

function respondJson(int $code, array $data): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function mapPost(array $row): array
{
    $thumb = trim((string) ($row['thumbnail_url'] ?? ''));
    if ($thumb === '') $thumb = DEFAULT_THUMBNAIL;
    elseif (!str_starts_with($thumb, 'http')) {
        $thumb = preg_replace('#^\.\./#', '', $thumb);
    }
    $pub = trim((string) ($row['published_at'] ?? $row['created_at'] ?? ''));
    $year = $pub !== '' ? substr($pub, 0, 4) : date('Y');
    return [
        'id' => (int) ($row['id'] ?? 0),
        'title' => (string) ($row['title'] ?? ''),
        'slug' => (string) ($row['slug'] ?? ''),
        'category' => (string) ($row['category'] ?? ''),
        'excerpt' => (string) ($row['excerpt'] ?? ''),
        'content' => (string) ($row['content'] ?? ''),
        'thumbnail_url' => $thumb,
        'published_at' => $pub,
        'year' => $year,
    ];
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ($method !== 'GET') {
    respondJson(405, ['success' => false, 'message' => 'Method not allowed']);
}

try {
    $pdo = getDbConnection();
} catch (Throwable $e) {
    error_log('Posts public API DB error: ' . $e->getMessage());
    respondJson(500, ['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
}

$id = (int) ($_GET['id'] ?? 0);
$slug = trim((string) ($_GET['slug'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = max(1, min((int) ($_GET['limit'] ?? 12), 50));
$offset = ($page - 1) * $limit;

try {
    if ($id > 0 || $slug !== '') {
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id AND status = 'published' LIMIT 1");
            $stmt->execute(['id' => $id]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = :slug AND status = 'published' LIMIT 1");
            $stmt->execute(['slug' => $slug]);
        }
        $row = $stmt->fetch();
        if (!$row) {
            respondJson(404, ['success' => false, 'message' => 'Không tìm thấy bài viết.']);
        }
        $pdo->prepare("UPDATE posts SET view_count = view_count + 1 WHERE id = :id")->execute(['id' => (int) $row['id']]);
        respondJson(200, ['success' => true, 'data' => mapPost($row)]);
    }

    $total = (int) $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();

    $listStmt = $pdo->prepare(
        "SELECT * FROM posts WHERE status = 'published' ORDER BY COALESCE(published_at, created_at) DESC, id DESC LIMIT :limit OFFSET :offset"
    );
    $listStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $listStmt->execute();

    $rows = $listStmt->fetchAll();
    $data = array_map('mapPost', $rows);

    $resp = [
        'success' => true,
        'data' => $data,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => (int) ceil($total / $limit),
        ],
    ];
    respondJson(200, $resp);
} catch (Throwable $e) {
    error_log('Posts public API error: ' . $e->getMessage());
    respondJson(500, ['success' => false, 'message' => 'Không thể tải bài viết.']);
}
