<?php // DB removed — add later when DB is ready
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tin tức — NHUTIN</title>
    <meta name="description" content="Tin tức và kiến thức về nhiên liệu sinh khối bã điều, giải pháp sàn trượt tự đổ." />
    <link rel="icon" href="/img/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/css/site.css" />
    <style>
      .news-hero {
        position: relative;
        min-height: 100svh;
        display: flex;
        align-items: center;
        padding-top: 100px;
        padding-bottom: 80px;
        background: linear-gradient(to bottom, #0b3d35 50%, transparent 100%);
        overflow: hidden;
      }
      .news-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: url('img/nhutinbanner.png') center / cover no-repeat;
        opacity: 0.58;
        -webkit-mask-image: linear-gradient(to bottom, black 72%, transparent 98%);
        mask-image: linear-gradient(to bottom, black 72%, transparent 98%);
        pointer-events: none;
      }
      .news-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom,
          rgba(11,61,53,0.58) 0%,
          rgba(11,61,53,0.30) 40%,
          rgba(11,61,53,0.05) 85%,
          transparent 98%);
        pointer-events: none;
      }
      .news-hero .container { position: relative; z-index: 1; width: 100%; }
      .news-hero-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        max-width: 860px;
        margin: 0 auto;
      }
      .about-tags { display: flex; flex-direction: column; align-items: center; gap: 6px; margin-bottom: 20px; background: none; }
      .about-tag-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #ffffff;
        letter-spacing: 0.02em;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        padding: 5px 14px 5px 10px;
        border-radius: 999px;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
      }
      .about-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--primary); flex-shrink: 0; }
      .news-hero h1 {
        margin: 0 0 20px;
        font-size: clamp(28px, 3.8vw, 54px);
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.15;
        color: #ffffff;
        text-shadow: 0 2px 24px rgba(0,0,0,0.35);
        white-space: nowrap;
      }
      .news-hero .sub { font-size: 16px; line-height: 1.85; max-width: 78ch; margin: 0 auto; color: rgba(255,255,255,0.88); }
      .news-hero .sub strong { color: #ffffff; }
      .news-hero .btn:not(.primary) { background: var(--deep); border-color: var(--deep); color: #ffffff; }
      .news-hero .btn:not(.primary):hover { background: #0e4a3e; border-color: #0e4a3e; }
      @media (max-width: 640px) {
        .news-hero { padding-top: 90px; padding-bottom: 60px; }
        .news-hero h1 { font-size: clamp(24px, 7vw, 38px); white-space: normal; }
        .news-hero .sub { font-size: 15px; }
      }
    </style>
</head>
<body>
    <div class="bgfx" aria-hidden="true"></div>
    <a class="skip" href="#main">Bỏ qua menu</a>
    <header class="topbar" data-include="/components/navbar.html"></header>

    <main id="main">
        <section class="news-hero">
            <div class="container">
                <div class="news-hero-inner" data-reveal>
                    <div class="about-tags">
                      <span class="about-tag-pill"><i class="about-dot"></i>Kiến thức & Cập nhật</span>
                    </div>
                    <h1>NHUTIN News</h1>
                    <p class="sub">
                        Nội dung tập trung vào ứng dụng thực tế, tối ưu chi phí và quy trình vận hành.
                        Bạn cần tư vấn theo dự án? Hãy gửi nhu cầu để NHUTIN hỗ trợ nhanh.
                    </p>
                    <div class="hero-actions">
                        <a class="btn primary" href="/lien-he">Liên hệ tư vấn</a>
                        <a class="btn" href="/san-pham">Xem sản phẩm</a>
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
                    <article class="card padded blogCard solid" data-reveal>
                        <div class="thumb"><img src="/img/picture1.png" alt="Sàn trượt tự đổ" loading="lazy" onerror="this.src='img/picture1.png'"></div>
                        <div class="meta"><span>Sàn trượt tự đổ</span><span><?= date('Y') ?></span></div>
                        <h3>Sàn trượt tự đổ là gì? Khi nào nên triển khai?</h3>
                        <p>Nguyên lý hoạt động, lợi ích và các tình huống giúp thu hồi vốn nhanh.</p>
                    </article>
                    <article class="card padded blogCard solid" data-reveal>
                        <div class="thumb"><img src="/img/picture2.png" alt="Vận hành" loading="lazy" onerror="this.src='img/picture1.png'"></div>
                        <div class="meta"><span>Vận hành</span><span><?= date('Y') ?></span></div>
                        <h3>Checklist an toàn khi xuống hàng tự động</h3>
                        <p>Các bước chuẩn hóa giúp giảm rủi ro và tăng độ ổn định khi vận hành.</p>
                    </article>
                    <article class="card padded blogCard solid" data-reveal>
                        <div class="thumb"><img src="/img/picture3.png" alt="Sinh khối" loading="lazy" onerror="this.src='img/picture1.png'"></div>
                        <div class="meta"><span>Sinh khối</span><span><?= date('Y') ?></span></div>
                        <h3>Đốt bã điều hiệu quả: tối ưu lò hơi &amp; chi phí</h3>
                        <p>Gợi ý cải tiến lò hơi, lưu trữ và vận hành để tối ưu hiệu suất đốt.</p>
                    </article>
                </div>
            </div>
        </section>

        <div data-include="/components/footer.html"></div>
    </main>

    <script src="/js/i18n.js" defer></script>
    <script src="/js/include.js" defer></script>
    <script src="/js/site.js" defer></script>
</body>
</html>
