<?php // DB removed — add later when DB is ready
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Bài viết — NHUTIN</title>
    <meta name="description" content="Bài viết NHUTIN về nhiên liệu sinh khối bã điều và hệ thống sàn trượt tự đổ." />
    <link rel="icon" href="img/logo.png" />
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/site.css" />
    <style>
        .post-header { margin-bottom: 24px; }
        .post-meta { color: var(--muted, #666); font-size: 14px; margin-bottom: 8px; }
        .post-title { font-size: clamp(24px, 3vw, 32px); font-weight: 700; line-height: 1.25; color: var(--deep, #0b1220); }
        .post-thumb { border-radius: 12px; overflow: hidden; margin-bottom: 28px; max-height: 400px; }
        .post-thumb img { width: 100%; height: auto; max-height: 400px; object-fit: cover; }
        .post-body { font-size: 16px; line-height: 1.75; color: rgba(11, 18, 32, 0.85); }
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
                <a href="/tin-tuc" class="back-link">&larr; Tin tức</a>
                <article class="post-header" data-reveal>
                    <div class="post-meta">Tin tức · <?= date('Y') ?></div>
                    <h1 class="post-title">Bài viết đang được cập nhật</h1>
                </article>
                <div class="post-thumb" data-reveal><img src="img/picture1.png" alt=""></div>
                <div class="post-body" data-reveal>
                    <p>Nội dung bài viết sẽ được cập nhật sớm nhất. Vui lòng quay lại trang <a href="/tin-tuc">Tin tức</a> hoặc liên hệ NHUTIN để được tư vấn.</p>
                </div>
            </div>
        </section>
        <div data-include="components/footer.html"></div>
    </main>
    <script src="js/i18n.js" defer></script>
    <script src="js/include.js" defer></script>
    <script src="js/site.js" defer></script>
</body>
</html>
