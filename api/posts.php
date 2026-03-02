<?php
declare(strict_types=1);

/**
 * API công khai cho bài viết - trả về bài viết đã xuất bản (status=published).
 * Không yêu cầu đăng nhập.
 */
require_once __DIR__ . '/../admin/config/db.php';

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
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            category VARCHAR(100) NOT NULL,
            excerpt TEXT NULL,
            content MEDIUMTEXT NULL,
            thumbnail_url VARCHAR(500) NULL,
            status ENUM('published','draft') NOT NULL DEFAULT 'draft',
            view_count INT NOT NULL DEFAULT 0,
            published_at TIMESTAMP NULL,
            created_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_posts_status (status),
            INDEX idx_posts_category (category)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
} catch (Throwable $e) {
    error_log('Posts public API DB error: ' . $e->getMessage());
    respondJson(500, ['success' => false, 'message' => 'Lỗi hệ thống.']);
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
        if ($id > 0 || $slug !== '') {
            $pdo->prepare("UPDATE posts SET view_count = view_count + 1 WHERE id = :id")->execute(['id' => (int) $row['id']]);
        }
        respondJson(200, ['success' => true, 'data' => mapPost($row)]);
    }

    $countStmt = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'");
    $total = (int) $countStmt->fetchColumn();

    $listStmt = $pdo->prepare(
        "SELECT * FROM posts WHERE status = 'published' ORDER BY COALESCE(published_at, created_at) DESC LIMIT :limit OFFSET :offset"
    );
    $listStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $listStmt->execute();

    $rows = $listStmt->fetchAll();
    $data = array_map('mapPost', $rows);

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
    error_log('Posts public API error: ' . $e->getMessage());
    respondJson(500, ['success' => false, 'message' => 'Không thể tải bài viết.']);
}
