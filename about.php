<!doctype html>
<html lang="vi">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Giới thiệu — NHUTIN</title>
    <meta
      name="description"
      content="Công ty Cổ phần Như Tín (NHUTIN) thành lập 01/2010. Chuyên cung cấp chất đốt sinh khối bã điều và giải pháp sàn trượt tự đổ."
    />
    <link rel="icon" href="/img/logo.png" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="/css/site.css" />
    <style>
      .about-hero {
        position: relative;
        min-height: 100svh;
        display: flex;
        align-items: center;
        padding-top: 100px;
        padding-bottom: 80px;
        background: linear-gradient(to bottom, #0b3d35 50%, transparent 100%);
        overflow: hidden;
      }
      .about-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: url('img/nhutinbanner.png') center / cover no-repeat;
        opacity: 0.58;
        -webkit-mask-image: linear-gradient(to bottom, black 72%, transparent 98%);
        mask-image: linear-gradient(to bottom, black 72%, transparent 98%);
        pointer-events: none;
      }
      .about-hero::after {
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
      .about-hero .container { position: relative; z-index: 1; width: 100%; }
      .about-hero-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        max-width: 860px;
        margin: 0 auto;
      }
      .about-hero h1 {
        margin: 0 0 20px;
        font-size: clamp(28px, 3.8vw, 54px);
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.15;
        color: #ffffff;
        text-shadow: 0 2px 24px rgba(0,0,0,0.35);
        white-space: nowrap;
      }
      .about-hero .sub {
        font-size: 16px;
        line-height: 1.85;
        max-width: 78ch;
        margin: 0 auto;
        color: rgba(255,255,255,0.88);
      }
      .about-hero .sub strong { color: #ffffff; }
      .about-tags {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        margin-bottom: 20px;
        background: none;
      }
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
      .about-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--primary);
        flex-shrink: 0;
      }
      .about-hero .page-stats {
        background: rgba(255,255,255,0.18);
        border: 1px solid rgba(255,255,255,0.12);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 6px 24px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.08);
        padding: 4px 0;
        transform: scale(1.10);
        transform-origin: center;
      }
      .about-hero .page-stat strong { color: #d4f5ed; }
      .about-hero .page-stat span { color: rgba(255,255,255,0.78); }
      .about-hero .page-stat-sep { background: rgba(255,255,255,0.22); }
      .about-hero .btn:not(.primary) {
        background: var(--deep);
        border-color: var(--deep);
        color: #ffffff;
      }
      .about-hero .btn:not(.primary):hover {
        background: #0e4a3e;
        border-color: #0e4a3e;
      }
      @media (max-width: 640px) {
        .about-hero { min-height: 100svh; padding-top: 90px; padding-bottom: 60px; }
        .about-hero h1 { font-size: clamp(24px, 7vw, 38px); white-space: normal; }
        .about-hero .sub { font-size: 15px; }
        .about-hero .page-stats { flex-direction: column; }
        .about-hero .page-stat-sep { width: 70%; height: 1px; }
      }
    </style>
  </head>

  <body>
    <div class="bgfx" aria-hidden="true"></div>
    <a class="skip" href="#main" data-i18n="skip">Bỏ qua menu</a>

    <header class="topbar" data-include="/components/navbar.html"></header>

    <main id="main">
      <section class="about-hero">
        <div class="container">
          <div class="about-hero-inner" data-reveal>
            <div class="about-tags">
              <span class="about-tag-pill"><i class="about-dot"></i><span data-i18n="about.tag">Nhiên liệu sinh khối cho công nghiệp</span></span>
            </div>
            <h1>About Nhu Tin Coporation</h1>
            <p class="sub" data-i18n="about.hero.sub">
              NHUTIN không ngừng phát triển hoạt động kinh doanh trên cơ sở mang lại lợi ích
              song phương, đồng hành kỹ thuật sát với nhu cầu vận hành thực tế của khách hàng.
            </p>
            <div class="page-stats">
              <div class="page-stat">
                <strong>15+</strong>
                <span data-i18n="about.stat1">Năm kinh nghiệm</span>
              </div>
              <div class="page-stat-sep"></div>
              <div class="page-stat">
                <strong>2</strong>
                <span data-i18n="about.stat2">Lĩnh vực chủ lực</span>
              </div>
              <div class="page-stat-sep"></div>
              <div class="page-stat">
                <strong>100%</strong>
                <span data-i18n="about.stat3">Cam kết đồng hành</span>
              </div>
            </div>
            <div class="hero-actions">
              <a class="btn primary" href="/san-pham" data-i18n="btn.viewProduct">Xem sản phẩm</a>
              <a class="btn" href="/lien-he" data-i18n="btn.contact2">Liên hệ</a>
            </div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div data-reveal>
            <h2 class="h2" data-i18n="about.what">Chúng tôi làm gì?</h2>
            <p class="sub" data-i18n-html="about.what.sub">
              NHUTIN chuyên cung cấp <strong>chất đốt sinh khối bã điều</strong> (bã vỏ hạt điều sau ép dầu) và triển khai
              giải pháp <strong>xuống hàng tự động</strong> cho xe/thùng/container.
            </p>
          </div>

          <div class="grid twoCol" style="margin-top: 18px">
            <div class="card padded solid" data-reveal>
              <h3 style="margin: 0; font-size: 18px" data-i18n="about.sol1">Giải pháp chất đốt sinh khối</h3>
              <p class="sub" data-i18n="about.sol1.sub">
                Tiên phong ứng dụng chất đốt bã điều cho lò hơi công nghiệp với kinh nghiệm thực tế triển khai và tư vấn.
              </p>
              <ul class="list">
                <li data-i18n="about.sol1.li1">Thiết bị cải tiến lò hơi để đốt bã điều hiệu quả</li>
                <li data-i18n="about.sol1.li2">Tư vấn vận hành không ảnh hưởng tuổi thọ thiết bị</li>
                <li data-i18n="about.sol1.li3">Giải pháp xử lý khói thải thân thiện môi trường</li>
              </ul>
            </div>

            <div class="card padded solid" data-reveal>
              <h3 style="margin: 0; font-size: 18px" data-i18n="about.sol2">Giải pháp xuống hàng tự động</h3>
              <p class="sub" data-i18n="about.sol2.sub">
                NHUTIN Coporation là đại lý ủy quyền của KEITH® (Mỹ) tại Việt Nam, triển khai hệ thống sàn trượt tự đổ — tối ưu logistics và giảm thời gian chờ.
              </p>
              <ul class="list">
                <li data-i18n="about.sol2.li1">Phù hợp nhiều loại hàng rời và quy mô vận hành</li>
                <li data-i18n="about.sol2.li2">Thi công, lắp đặt nhanh theo cấu hình xe sẵn có</li>
                <li data-i18n="about.sol2.li3">Hỗ trợ bảo trì, phụ tùng và hướng dẫn vận hành</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="card padded deep" data-reveal>
            <h2 class="h2" data-i18n="about.focus">Tập trung vào hiệu quả vận hành & dịch vụ</h2>
            <p class="sub" data-i18n="about.focus.sub">
              Chúng tôi coi trọng tính ổn định, an toàn và hiệu suất dài hạn của hệ thống khi đưa vào vận hành thực tế.
            </p>

            <div class="grid featureGrid" style="margin-top: 18px">
              <div class="card featureCard" data-reveal>
                <h3 data-i18n="about.step1">Khảo sát nhanh</h3>
                <p data-i18n="about.step1.desc">Tư vấn phương án theo hiện trạng và mục tiêu vận hành.</p>
              </div>
              <div class="card featureCard" data-reveal>
                <h3 data-i18n="about.step2">Triển khai gọn</h3>
                <p data-i18n="about.step2.desc">Thi công sạch, tối ưu tiến độ và hướng dẫn vận hành rõ ràng.</p>
              </div>
              <div class="card featureCard" data-reveal>
                <h3 data-i18n="about.step3">Đồng hành dài hạn</h3>
                <p data-i18n="about.step3.desc">Hỗ trợ kỹ thuật theo dự án, bảo trì và phụ tùng khi cần.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div data-reveal>
            <h2 class="h2" data-i18n="about.gallery">Một vài hình ảnh triển khai</h2>
            <p class="sub" data-i18n="about.gallery.sub">Tổng hợp ảnh thực tế từ hoạt động triển khai và vận hành.</p>
          </div>

          <div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-top: 18px">
            <div class="card solid" style="overflow: hidden" data-reveal>
              <img src="/img/aboutk10.jpg" alt="Thực tế triển khai" style="height: 240px; width: 100%; object-fit: cover" />
            </div>
            <div class="card solid" style="overflow: hidden" data-reveal>
              <img src="/img/aboutk11.jpg" alt="Thi công lắp đặt" style="height: 240px; width: 100%; object-fit: cover" />
            </div>
            <div class="card solid" style="overflow: hidden" data-reveal>
              <img src="/img/aboutk13.jpg" alt="Hỗ trợ kỹ thuật" style="height: 240px; width: 100%; object-fit: cover" />
            </div>
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
