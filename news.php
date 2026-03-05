<?php
/**
 * Tin tức - Hiển thị bài viết trực tiếp từ DB (đơn giản, không cần JS fetch)
 */
require_once __DIR__ . '/admin/config/db.php';

$posts = [];
try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT * FROM posts WHERE status = 'published' ORDER BY COALESCE(published_at, created_at) DESC LIMIT 50");
    $posts = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Throwable $e) {
    $posts = [];
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function thumb($row) {
    $t = trim($row['thumbnail_url'] ?? '');
    if (!$t) return 'img/picture1.png';
    if (str_starts_with($t, 'http')) return $t;
    return preg_replace('#^\.\./#', '', $t);
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tin tức — NHUTIN</title>
    <meta name="description" content="Tin tức và kiến thức về nhiên liệu sinh khối bã điều, giải pháp sàn trượt tự đổ." />
    <link rel="icon" href="img/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/site.css" />
</head>
<body>
    <div class="bgfx" aria-hidden="true"></div>
    <a class="skip" href="#main">Bỏ qua menu</a>
    <header class="topbar" data-include="components/navbar.html"></header>

    <main id="main">
        <section class="section hero">
            <div class="container">
                <div class="grid hero-grid">
                    <div class="card padded" data-reveal>
                        <h1>Kiến thức & cập nhật</h1>
                        <p class="sub">
                            Nội dung tập trung vào ứng dụng thực tế, tối ưu chi phí và quy trình vận hành. Bạn cần tư vấn theo dự án?
                            Hãy gửi nhu cầu để NHUTIN hỗ trợ nhanh.
                        </p>
                        <div class="hero-actions">
                            <a class="btn primary" href="contact.html">Liên hệ</a>
                            <a class="btn" href="products.html">Xem sản phẩm</a>
                        </div>
                    </div>
                    <div class="card hero-media solid" data-reveal>
                        <img src="img/picture2.png" alt="Tin tức NHUTIN" />
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div data-reveal>
                    <h2 class="h2">Góc nhìn thực chiến</h2>
                    <p class="sub">
                        Các bài viết dưới đây là nội dung tham khảo/giới thiệu. Khi bạn muốn triển khai, NHUTIN sẽ tư vấn theo hiện trạng cụ thể.
                    </p>
                </div>

                <div class="grid blog" style="margin-top: 18px">
<?php if (empty($posts)): ?>
                    <div style="grid-column:1/-1;display:flex;align-items:center;justify-content:center;min-height:200px;">
                        <p class="sub" style="margin:0;text-align:center;color:var(--muted,#666);">Chưa có bài viết.</p>
                    </div>
<?php else: foreach ($posts as $p): 
    $href = 'news-detail.php?id=' . (int)($p['id'] ?? 0);
    $thumb = thumb($p);
    $cat = trim($p['category'] ?? '') ?: 'Tin tức';
    $year = !empty($p['published_at']) ? substr($p['published_at'], 0, 4) : (substr($p['created_at'] ?? '', 0, 4) ?: date('Y'));
    $excerpt = mb_substr($p['excerpt'] ?? $p['title'] ?? '', 0, 120, 'UTF-8');
    if (mb_strlen(($p['excerpt'] ?? $p['title'] ?? ''), 'UTF-8') > 120) $excerpt .= '...';
?>
                    <article class="card padded blogCard solid" data-reveal>
                        <a href="<?= e($href) ?>" style="text-decoration:none;color:inherit;display:block">
                            <div class="thumb"><img src="<?= e($thumb) ?>" alt="<?= e($p['title'] ?? '') ?>" loading="lazy" onerror="this.src='img/picture1.png'"></div>
                            <div class="meta"><span><?= e($cat) ?></span><span><?= e($year) ?></span></div>
                            <h3><?= e($p['title'] ?? '') ?></h3>
                            <p><?= e($excerpt) ?></p>
                        </a>
                    </article>
<?php endforeach; endif; ?>
                </div>
            </div>
        </section>

        <div data-include="components/footer.html"></div>
    </main>

    <script src="js/include.js" defer></script>
    <script src="js/site.js" defer></script>
</body>
</html>
