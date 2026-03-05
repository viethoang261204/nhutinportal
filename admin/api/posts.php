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

const POST_ALLOWED_STATUS = ['published', 'draft'];
const DEFAULT_THUMBNAIL_URL = 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&h=400&fit=crop';
const MAX_THUMBNAIL_SIZE_BYTES = 5 * 1024 * 1024;

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

function ensureThumbnailUploadDirectory(): string
{
    $uploadDirectory = realpath(__DIR__ . '/../../img');
    if ($uploadDirectory === false) {
        throw new RuntimeException('Không tìm thấy thư mục img.');
    }

    $thumbnailDirectory = $uploadDirectory . DIRECTORY_SEPARATOR . 'post-thumbnails';
    if (!is_dir($thumbnailDirectory)) {
        if (!mkdir($thumbnailDirectory, 0775, true) && !is_dir($thumbnailDirectory)) {
            throw new RuntimeException('Không thể tạo thư mục lưu ảnh bài viết.');
        }
    }

    return $thumbnailDirectory;
}

function getThumbnailExtension(string $mimeType, string $originalName): string
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

function normalizeStatus(string $status): string
{
    $normalized = strtolower(trim($status));
    if (!in_array($normalized, POST_ALLOWED_STATUS, true)) {
        return 'draft';
    }
    return $normalized;
}

function normalizeSlug(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug ?: '');
    return trim((string) $slug, '-');
}

function ensurePostsSchema(PDO $pdo): void
{
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS posts (
                id SERIAL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                category VARCHAR(100) NOT NULL,
                excerpt TEXT NULL,
                content TEXT NULL,
                thumbnail_url VARCHAR(500) NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'draft',
                view_count INT NOT NULL DEFAULT 0,
                published_at TIMESTAMP NULL,
                created_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        );
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_posts_status ON posts (status)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_posts_category ON posts (category)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_posts_created ON posts (created_at)");
    } catch (Throwable $e) {}
}

function resolveCreatorIdFromSession(): ?int
{
    $admin = is_array($_SESSION['nhutin_admin'] ?? null) ? $_SESSION['nhutin_admin'] : [];
    $id = (int) ($admin['id'] ?? 0);
    return $id > 0 ? $id : null;
}

function generateUniqueSlug(PDO $pdo, string $title, ?int $exceptId = null): string
{
    $base = normalizeSlug($title);
    if ($base === '') {
        $base = 'post-' . date('YmdHis');
    }
    $slug = $base;
    $suffix = 1;

    while (true) {
        if ($exceptId !== null) {
            $stmt = $pdo->prepare('SELECT id FROM posts WHERE slug = :slug AND id <> :id LIMIT 1');
            $stmt->execute(['slug' => $slug, 'id' => $exceptId]);
        } else {
            $stmt = $pdo->prepare('SELECT id FROM posts WHERE slug = :slug LIMIT 1');
            $stmt->execute(['slug' => $slug]);
        }
        if (!$stmt->fetch()) {
            return $slug;
        }
        $suffix += 1;
        $slug = $base . '-' . $suffix;
    }
}

function mapPostRow(array $row): array
{
    $thumb = trim((string) ($row['thumbnail_url'] ?? ''));
    if ($thumb === '') {
        $thumb = DEFAULT_THUMBNAIL_URL;
    }
    return [
        'id' => (int) ($row['id'] ?? 0),
        'title' => (string) ($row['title'] ?? ''),
        'slug' => (string) ($row['slug'] ?? ''),
        'category' => (string) ($row['category'] ?? ''),
        'excerpt' => (string) ($row['excerpt'] ?? ''),
        'content' => (string) ($row['content'] ?? ''),
        'thumbnail_url' => $thumb,
        'status' => normalizeStatus((string) ($row['status'] ?? 'draft')),
        'view_count' => (int) ($row['view_count'] ?? 0),
        'published_at' => (string) ($row['published_at'] ?? ''),
        'created_at' => (string) ($row['created_at'] ?? ''),
    ];
}

function validatePostInput(array $input, bool $isUpdate = false): array
{
    $id = (int) ($input['id'] ?? 0);
    $title = trim((string) ($input['title'] ?? ''));
    $category = trim((string) ($input['category'] ?? ''));
    $excerpt = trim((string) ($input['excerpt'] ?? ''));
    $content = trim((string) ($input['content'] ?? ''));
    $status = normalizeStatus((string) ($input['status'] ?? 'draft'));
    $thumbnailUrl = trim((string) ($input['thumbnail_url'] ?? ''));
    $publishedAt = trim((string) ($input['published_at'] ?? ''));

    if ($isUpdate && $id <= 0) {
        respondJson(422, [
            'success' => false,
            'message' => 'ID bài viết không hợp lệ.',
        ]);
    }

    if ($title === '' || stringLength($title) > 255) {
        respondJson(422, [
            'success' => false,
            'message' => 'Tiêu đề bài viết không hợp lệ.',
        ]);
    }

    if ($category === '' || stringLength($category) > 100) {
        respondJson(422, [
            'success' => false,
            'message' => 'Danh mục không hợp lệ.',
        ]);
    }

    if (stringLength($excerpt) > 3000) {
        respondJson(422, [
            'success' => false,
            'message' => 'Mô tả ngắn quá dài.',
        ]);
    }

    if (stringLength($content) > 120000) {
        respondJson(422, [
            'success' => false,
            'message' => 'Nội dung quá dài.',
        ]);
    }

    if ($thumbnailUrl !== '' && stringLength($thumbnailUrl) > 500) {
        respondJson(422, [
            'success' => false,
            'message' => 'Ảnh đại diện không hợp lệ.',
        ]);
    }

    return [
        'id' => $id,
        'title' => $title,
        'category' => $category,
        'excerpt' => $excerpt,
        'content' => $content,
        'status' => $status,
        'thumbnail_url' => $thumbnailUrl !== '' ? $thumbnailUrl : DEFAULT_THUMBNAIL_URL,
        'published_at' => $publishedAt,
    ];
}

ensureAdminOrStaff();

try {
    $pdo = getDbConnection();
    ensurePostsSchema($pdo);
} catch (Throwable $e) {
    error_log('Posts DB init failed: ' . $e->getMessage());
    respondJson(500, [
        'success' => false,
        'message' => 'Lỗi khởi tạo cơ sở dữ liệu: ' . $e->getMessage(),
    ]);
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$action = (string) ($_GET['action'] ?? '');

if ($method === 'POST' && $action === 'upload_thumbnail') {
    try {
        if (!isset($_FILES['thumbnail']) || !is_array($_FILES['thumbnail'])) {
            respondJson(422, [
                'success' => false,
                'message' => 'Vui lòng chọn ảnh để tải lên.',
            ]);
        }

        $file = $_FILES['thumbnail'];
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
        if ($size <= 0 || $size > MAX_THUMBNAIL_SIZE_BYTES) {
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

        $extension = getThumbnailExtension($mimeType, (string) ($file['name'] ?? ''));
        $targetDir = ensureThumbnailUploadDirectory();
        $filename = 'post_' . date('YmdHis') . '_' . random_int(1000, 9999) . '.' . $extension;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($tmpPath, $targetPath)) {
            throw new RuntimeException('Không thể lưu file ảnh.');
        }

        respondJson(200, [
            'success' => true,
            'message' => 'Tải ảnh thành công.',
            'thumbnail_url' => '../img/post-thumbnails/' . $filename,
        ]);
    } catch (Throwable $e) {
        error_log('Upload post thumbnail failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể tải ảnh lên: ' . $e->getMessage(),
        ]);
    }
}

if ($method === 'GET') {
    try {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            if (!$row) {
                respondJson(404, [
                    'success' => false,
                    'message' => 'Không tìm thấy bài viết.',
                ]);
            }
            respondJson(200, [
                'success' => true,
                'data' => mapPostRow($row),
            ]);
        }

        $search = trim((string) ($_GET['search'] ?? ''));
        $status = isset($_GET['status']) && (string) $_GET['status'] !== '' ? normalizeStatus((string) $_GET['status']) : '';
        $category = trim((string) ($_GET['category'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = max(1, min((int) ($_GET['limit'] ?? 10), 50));
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = '(title LIKE :keyword OR excerpt LIKE :keyword OR category LIKE :keyword)';
            $params['keyword'] = '%' . $search . '%';
        }
        if ($status !== '') {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }
        if ($category !== '') {
            $where[] = 'category = :category';
            $params['category'] = $category;
        }

        $whereSql = count($where) > 0 ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM posts {$whereSql}");
        foreach ($params as $key => $value) {
            $countStmt->bindValue(':' . $key, (string) $value);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $listStmt = $pdo->prepare("SELECT * FROM posts {$whereSql} ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            $listStmt->bindValue(':' . $key, (string) $value);
        }
        $listStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $listStmt->execute();

        $rows = $listStmt->fetchAll();
        $data = array_map(static fn(array $row): array => mapPostRow($row), $rows);

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
        error_log('Load posts failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể tải danh sách bài viết: ' . $e->getMessage(),
        ]);
    }
}

if ($method === 'POST') {
    $payload = validatePostInput(getRequestBody(), false);

    try {
        $slug = generateUniqueSlug($pdo, $payload['title'], null);
        $publishedAt = $payload['status'] === 'published'
            ? ($payload['published_at'] !== '' ? $payload['published_at'] : date('Y-m-d H:i:s'))
            : null;
        $stmt = $pdo->prepare(
            "INSERT INTO posts (title, slug, category, excerpt, content, thumbnail_url, status, published_at, created_by)
             VALUES (:title, :slug, :category, :excerpt, :content, :thumbnail_url, :status, :published_at, :created_by)"
        );
        $stmt->execute([
            'title' => $payload['title'],
            'slug' => $slug,
            'category' => $payload['category'],
            'excerpt' => $payload['excerpt'],
            'content' => $payload['content'],
            'thumbnail_url' => $payload['thumbnail_url'],
            'status' => $payload['status'],
            'published_at' => $publishedAt,
            'created_by' => resolveCreatorIdFromSession(),
        ]);
        $newId = (int) $pdo->lastInsertId();
        logActivity($pdo, 'create', 'post', $newId, 'Tạo bài viết: ' . $payload['title']);
    } catch (Throwable $e) {
        if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
            respondJson(409, [
                'success' => false,
                'message' => 'Slug bài viết đã tồn tại.',
            ]);
        }
        error_log('Create post failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể tạo bài viết: ' . $e->getMessage(),
        ]);
    }

    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $newId]);
    $row = $stmt->fetch();

    respondJson(201, [
        'success' => true,
        'message' => 'Tạo bài viết thành công.',
        'data' => mapPostRow($row ?: []),
    ]);
}

if ($method === 'PUT') {
    $payload = validatePostInput(getRequestBody(), true);

    $existsStmt = $pdo->prepare("SELECT id, slug FROM posts WHERE id = :id LIMIT 1");
    $existsStmt->execute(['id' => $payload['id']]);
    $existing = $existsStmt->fetch();
    if (!$existing) {
        respondJson(404, [
            'success' => false,
            'message' => 'Không tìm thấy bài viết.',
        ]);
    }

    try {
        $slug = generateUniqueSlug($pdo, $payload['title'], $payload['id']);
        $publishedAt = $payload['status'] === 'published'
            ? ($payload['published_at'] !== '' ? $payload['published_at'] : date('Y-m-d H:i:s'))
            : null;

        $stmt = $pdo->prepare(
            "UPDATE posts
             SET title = :title,
                 slug = :slug,
                 category = :category,
                 excerpt = :excerpt,
                 content = :content,
                 thumbnail_url = :thumbnail_url,
                 status = :status,
                 published_at = :published_at
             WHERE id = :id"
        );
        $stmt->execute([
            'id' => $payload['id'],
            'title' => $payload['title'],
            'slug' => $slug,
            'category' => $payload['category'],
            'excerpt' => $payload['excerpt'],
            'content' => $payload['content'],
            'thumbnail_url' => $payload['thumbnail_url'],
            'status' => $payload['status'],
            'published_at' => $publishedAt,
        ]);
        logActivity($pdo, 'update', 'post', $payload['id'], 'Cập nhật bài viết: ' . $payload['title']);
    } catch (Throwable $e) {
        if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
            respondJson(409, [
                'success' => false,
                'message' => 'Slug bài viết đã tồn tại.',
            ]);
        }
        error_log('Update post failed: ' . $e->getMessage());
        respondJson(500, [
            'success' => false,
            'message' => 'Không thể cập nhật bài viết: ' . $e->getMessage(),
        ]);
    }

    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $payload['id']]);
    $row = $stmt->fetch();

    respondJson(200, [
        'success' => true,
        'message' => 'Cập nhật bài viết thành công.',
        'data' => mapPostRow($row ?: []),
    ]);
}

if ($method === 'DELETE') {
    $body = getRequestBody();
    $id = (int) ($body['id'] ?? ($_GET['id'] ?? 0));
    if ($id <= 0) {
        respondJson(422, [
            'success' => false,
            'message' => 'ID bài viết không hợp lệ.',
        ]);
    }

    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() === 0) {
        respondJson(404, [
            'success' => false,
            'message' => 'Không tìm thấy bài viết.',
        ]);
    }

    logActivity($pdo, 'delete', 'post', $id, 'Xóa bài viết #' . $id);
    respondJson(200, [
        'success' => true,
        'message' => 'Đã xóa bài viết.',
    ]);
}

respondJson(405, [
    'success' => false,
    'message' => 'Method not allowed.',
]);
