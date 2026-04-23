<?php
/**
 * Chi tiết bài viết - Hiển thị trực tiếp từ DB
 */
require_once __DIR__ . '/admin/config/db.php';

$slug = trim($_GET['slug'] ?? '');
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$post = null;
if ($slug || $id > 0) {
    try {
        $pdo = getDbConnection();
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND status = 'published' LIMIT 1");
            $stmt->execute([$id]);
        } elseif ($slug && preg_match('/^\d+$/', $slug)) {
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND status = 'published' LIMIT 1");
            $stmt->execute([$slug]);
        } elseif ($slug) {
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = ? AND status = 'published' LIMIT 1");
            $stmt->execute([$slug]);
        } else {
            $stmt = null;
        }
        $post = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    } catch (Throwable $e) {}
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $post ? e($post['title']) . ' — NHUTIN' : 'Bài viết — NHUTIN' ?></title>
    <meta name="description" content="<?= $post ? e(mb_substr($post['excerpt'] ?? $post['title'] ?? '', 0, 160, 'UTF-8')) : 'Bài viết NHUTIN' ?>" />
    <link rel="icon" href="img/logo.png" />
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/site.css" />
    <style>
        .post-header { margin-bottom: 24px; }
        .post-meta { color: var(--muted, #666); font-size: 14px; margin-bottom: 8px; }
        .post-title { font-size: clamp(24px, 3vw, 32px); font-weight: 700; line-height: 1.25; color: var(--deep, #0b1220); }
        .post-thumb { border-radius: 12px; overflow: hidden; margin-bottom: 28px; max-height: 400px; }
        .post-thumb img { width: 100%; height: auto; max-height: 400px; object-fit: cover; }
        .post-body { font-size: 16px; line-height: 1.75; color: rgba(11, 18, 32, 0.85); white-space: pre-wrap; }
        .post-body p { margin: 0 0 1em; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--accent); text-decoration: none; font-weight: 600; margin-bottom: 16px; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="bgfx" aria-hidden="true"></div>
    <a class="skip" href="#main">Bỏ qua menu</a>
    <header class="topbar" data-include="components/navbar.html"></header>

    <main id="main">
        <section class="section">
            <div class="container" style="max-width: 800px">
<?php if (!$post): ?>
                <p class="sub">Không tìm thấy bài viết. <a href="news.php">Quay lại tin tức</a></p>
<?php else:
    $thumb = trim($post['thumbnail_url'] ?? '');
    if ($thumb && !str_starts_with($thumb, 'http')) $thumb = preg_replace('#^\.\./#', '', $thumb);
    $cat = trim($post['category'] ?? '') ?: 'Tin tức';
    $pub = $post['published_at'] ?? $post['created_at'] ?? '';
    $pubStr = $pub ? substr($pub, 0, 10) : '';
    $body = $post['content'] ?? $post['excerpt'] ?? '';
    $body = nl2br(e($body));
?>
                <a href="news.php" class="back-link">&larr; Tin tức</a>
                <article class="post-header" data-reveal>
                    <div class="post-meta"><?= e($cat) ?><?= $pubStr ? ' · ' . e($pubStr) : '' ?></div>
                    <h1 class="post-title"><?= e($post['title']) ?></h1>
                </article>
                <?php if ($thumb): ?><div class="post-thumb" data-reveal><img src="<?= e($thumb) ?>" alt=""></div><?php endif; ?>
                <div class="post-body" data-reveal><?= $body ?></div>
<?php endif; ?>
            </div>
        </section>
        <div data-include="components/footer.html"></div>
    </main>
    <script src="js/i18n.js" defer></script>
    <script src="js/include.js" defer></script>
    <script src="js/site.js" defer></script>
</body>
</html>
