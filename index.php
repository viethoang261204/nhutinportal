<?php
$latestPosts = [];
try {
    require_once __DIR__ . '/admin/config/db.php';
    $pdo = getDbConnection();
    $stmt = $pdo->query("SELECT * FROM posts WHERE status = 'published' ORDER BY COALESCE(published_at, created_at) DESC LIMIT 3");
    $latestPosts = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Throwable $e) {}
function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function thumbUrl($r) {
    $t = trim($r['thumbnail_url'] ?? '');
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
    <title>NHUTIN — Nhiên liệu sinh khối bã điều & Sàn trượt tự đổ</title>
    <meta name="description" content="Công ty Cổ phần Như Tín (NHUTIN) — tiên phong ứng dụng chất đốt sinh khối bã điều cho lò hơi công nghiệp và triển khai hệ thống sàn trượt tự đổ." />
    <link rel="icon" href="img/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/site.css" />
  </head>
  <body>
    <div class="bgfx" aria-hidden="true"></div>
    <a class="skip" href="#main" data-i18n="skip">Bỏ qua menu</a>
    <header class="topbar" data-include="components/navbar.html"></header>

    <main id="main">
      <section class="main-banner" id="top">
        <div class="container">
          <div class="banner-content" data-reveal>
            <h1 class="banner-title">Nhu Tin Corporation</h1>
            <p class="banner-subtitle" data-i18n="home.banner.sub">Tiên phong ứng dụng nhiên liệu sinh khối bã điều cho lò hơi công nghiệp và triển khai hệ thống sàn trượt tự đổ – giải pháp tối ưu chi phí và nâng cao hiệu suất vận hành.</p>
            <div class="banner-actions">
              <a class="btn primary large" href="products.html" data-i18n="btn.viewProduct">Xem sản phẩm</a>
              <a class="btn large" href="about.html" data-i18n="btn.aboutUs">Về chúng tôi</a>
              <a class="btn large" href="contact.html" data-i18n="btn.contact">Liên hệ ngay</a>
            </div>
          </div>
          <div class="banner-features">
            <div class="banner-feature-main" data-reveal>
              <h3 data-i18n="home.why">Tại sao chọn chúng tôi?</h3>
              <p data-i18n="home.why.desc">Tối ưu chi phí — tăng hiệu suất — vận hành ổn định. Chúng tôi cung cấp giải pháp toàn diện từ tư vấn, triển khai đến bảo trì, giúp doanh nghiệp kiểm soát chi phí năng lượng và tối ưu quy trình xếp dỡ.</p>
            </div>
            <div class="banner-feature-right">
              <div class="banner-feature-card small" data-reveal>
                <div class="feature-icon-small"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
                <h3 data-i18n="home.feat1">Tự động hóa xuống hàng</h3>
                <p data-i18n="home.feat1.desc">Giảm nhân công, giảm thời gian chờ xe, tăng vòng quay kho bãi.</p>
              </div>
              <div class="banner-feature-card small" data-reveal>
                <div class="feature-icon-small"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
                <h3 data-i18n="home.feat2">Sinh khối bã điều</h3>
                <p data-i18n="home.feat2.desc">Tiết kiệm chi phí đến 50% so với đốt than, nguồn cung ổn định.</p>
              </div>
              <div class="banner-feature-card small" data-reveal>
                <div class="feature-icon-small"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                <h3 data-i18n="home.feat3">Đồng hành kỹ thuật</h3>
                <p data-i18n="home.feat3.desc">Tư vấn, triển khai và hỗ trợ vận hành theo từng dự án.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="section" id="products">
        <div class="container">
          <div data-reveal>
            <h2 class="h2" data-i18n="home.solutions">Giải pháp toàn diện từ NHUTIN</h2>
            <p class="sub" data-i18n="home.solutions.sub">Chúng tôi tập trung vào nhiên liệu sinh khối bã điều và hệ thống sàn trượt tự đổ. Mỗi dịch vụ đều có website chuyên biệt với đội ngũ chuyên môn và giải pháp tối ưu cho từng nhu cầu.</p>
          </div>
          <div class="grid twoCol" style="margin-top: 18px; align-items: stretch;">
            <article class="card padded solid" data-reveal style="display: flex; flex-direction: column;">
              <div class="mediaTop"><img src="img/badieu1.jpg" alt="Nhiên liệu sinh khối bã điều" /></div>
              <h3 style="margin: 16px 0 0; font-size: 18px" data-i18n="home.biomass">Nhiên liệu sinh khối bã điều</h3>
              <p class="sub" style="margin-top: 8px;"><strong>Website:</strong> <a href="https://bavohatdieu.com" target="_blank" rel="noopener noreferrer" style="color: var(--primary); font-weight: 800; text-decoration: underline;">bavohatdieu.com</a></p>
              <p class="sub" data-i18n="home.biomass.desc">Tiên phong ứng dụng bã vỏ hạt điều (sau ép dầu) cho lò hơi công nghiệp — giải pháp tiết kiệm chi phí và thân thiện môi trường cho các nhà máy.</p>
              <ul class="list" style="flex-grow: 1;">
                <li data-i18n="home.biomass.li1">Tiết kiệm chi phí năng lượng lên tới 50% so với than truyền thống</li>
                <li data-i18n="home.biomass.li2">Giao hàng nhanh chóng bằng hệ thống đội xe chuyên dụng</li>
                <li data-i18n="home.biomass.li3">Tư vấn vận hành lò hơi & xử lý khói thải đạt chuẩn môi trường</li>
                <li data-i18n="home.biomass.li4">Nguồn cung ổn định từ vùng nguyên liệu tập trung</li>
              </ul>
              <div class="hero-actions" style="margin-top: 16px; display: flex; gap: 10px;">
                <a class="btn primary" href="products.html#biomass" style="flex: 1;" data-i18n="btn.viewDetail2">Xem chi tiết</a>
                <a class="btn" href="https://bavohatdieu.com" target="_blank" rel="noopener noreferrer" style="flex: 1;" data-i18n="home.biomass.btn2">Tới bavohatdieu.com</a>
              </div>
            </article>
            <article class="card padded solid" data-reveal style="display: flex; flex-direction: column;">
              <div class="mediaTop"><img src="img/picture3.png" alt="Sàn trượt tự đổ" /></div>
              <h3 style="margin: 16px 0 0; font-size: 18px" data-i18n="home.walkfloor">Sàn trượt tự đổ</h3>
              <p class="sub" style="margin-top: 8px;"><strong>Website:</strong> <a href="https://thungxetudo.com" target="_blank" rel="noopener noreferrer" style="color: var(--primary); font-weight: 800; text-decoration: underline;">thungxetudo.com</a></p>
              <p class="sub" data-i18n="home.walkfloor.desc">NHUTIN Coporation là đại lý ủy quyền của KEITH® (Mỹ) tại Việt Nam, triển khai hệ thống sàn trượt tự đổ — giải pháp tự động hóa xuống hàng giúp tối ưu logistics và kho bãi.</p>
              <ul class="list" style="flex-grow: 1;">
                <li data-i18n="home.walkfloor.li1">Xuống hàng tự động nhanh chóng, giảm thời gian chờ xe đến 70%</li>
                <li data-i18n="home.walkfloor.li2">Phù hợp tải trọng lớn (20-80 tấn), bền bỉ trong môi trường khắc nghiệt</li>
                <li data-i18n="home.walkfloor.li3">Lắp đặt linh hoạt, cải tạo được cho xe container và thùng xe sẵn có</li>
                <li data-i18n="home.walkfloor.li4">Giảm nhân công, tăng an toàn lao động và vòng quay kho bãi</li>
              </ul>
              <div class="hero-actions" style="margin-top: 16px; display: flex; gap: 10px;">
                <a class="btn primary" href="products.html#walking-floor" style="flex: 1;" data-i18n="btn.viewDetail2">Xem chi tiết</a>
                <a class="btn" href="https://thungxetudo.com" target="_blank" rel="noopener noreferrer" style="flex: 1;" data-i18n="home.walkfloor.btn2">Tới thungxetudo.com</a>
              </div>
            </article>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="grid twoCol">
            <div data-reveal>
              <h2 class="h2" data-i18n="home.process">Nhìn nhanh quy trình & ứng dụng thực tế</h2>
              <p class="sub" data-i18n="home.process.sub">Video ngắn giúp bạn hình dung cách vận hành và ứng dụng, từ đó quyết định phương án phù hợp cho doanh nghiệp.</p>
              <div class="hero-actions" style="margin-top: 14px">
                <a class="btn primary" href="contact.html" data-i18n="btn.contact2">Liên hệ</a>
                <a class="btn" href="about.html" data-i18n="btn.aboutNhutin">Về NHUTIN</a>
              </div>
            </div>
            <div class="card solid" style="overflow: hidden" data-reveal>
              <video src="img/videointro.mp4" autoplay muted loop playsinline style="width: 100%; height: 360px; object-fit: cover"></video>
            </div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div data-reveal>
            <h2 class="h2" data-i18n="home.knowledge">Kiến thức & cập nhật</h2>
            <p class="sub" data-i18n="home.knowledge.sub">Chia sẻ ngắn gọn, tập trung vào ứng dụng thực tế và tối ưu vận hành.</p>
          </div>
          <div class="grid blog" style="margin-top: 18px">
<?php if (empty($latestPosts)): ?>
            <article class="card padded blogCard solid" data-reveal>
              <div class="thumb"><img src="img/picture1.png" alt="Sàn trượt tự đổ" /></div>
              <div class="meta"><span data-i18n="home.blog1.cat">Sàn trượt tự đổ</span><span><?= date('Y') ?></span></div>
              <h3 data-i18n="home.blog1.title">Sàn trượt tự đổ là gì? Khi nào nên triển khai?</h3>
              <p data-i18n="home.blog1.desc">Nguyên lý hoạt động, lợi ích và các tình huống giúp thu hồi vốn nhanh.</p>
            </article>
            <article class="card padded blogCard solid" data-reveal>
              <div class="thumb"><img src="img/picture2.png" alt="Vận hành" /></div>
              <div class="meta"><span data-i18n="home.blog2.cat">Vận hành</span><span><?= date('Y') ?></span></div>
              <h3 data-i18n="home.blog2.title">Checklist an toàn khi xuống hàng tự động</h3>
              <p data-i18n="home.blog2.desc">Các bước chuẩn hóa giúp giảm rủi ro và tăng độ ổn định khi vận hành.</p>
            </article>
            <article class="card padded blogCard solid" data-reveal>
              <div class="thumb"><img src="img/picture3.png" alt="Sinh khối" /></div>
              <div class="meta"><span data-i18n="home.blog3.cat">Sinh khối</span><span><?= date('Y') ?></span></div>
              <h3 data-i18n="home.blog3.title">Đốt bã điều hiệu quả: tối ưu lò hơi & chi phí</h3>
              <p data-i18n="home.blog3.desc">Gợi ý cải tiến lò hơi, lưu trữ và vận hành để tối ưu hiệu suất đốt.</p>
            </article>
<?php else: foreach ($latestPosts as $p):
    $href = 'news-detail.php?id=' . (int)($p['id'] ?? 0);
    $thumb = thumbUrl($p);
    $cat = trim($p['category'] ?? '') ?: 'Tin tức';
    $year = !empty($p['published_at']) ? substr($p['published_at'], 0, 4) : (substr($p['created_at'] ?? '', 0, 4) ?: date('Y'));
    $excerpt = mb_substr($p['excerpt'] ?? $p['title'] ?? '', 0, 100, 'UTF-8');
    if (mb_strlen(($p['excerpt'] ?? $p['title'] ?? ''), 'UTF-8') > 100) $excerpt .= '...';
?>
            <article class="card padded blogCard solid" data-reveal>
              <a href="<?= esc($href) ?>" style="text-decoration:none;color:inherit;display:block">
                <div class="thumb"><img src="<?= esc($thumb) ?>" alt="<?= esc($p['title'] ?? '') ?>" loading="lazy" onerror="this.src='img/picture1.png'"></div>
                <div class="meta"><span><?= esc($cat) ?></span><span><?= esc($year) ?></span></div>
                <h3><?= esc($p['title'] ?? '') ?></h3>
                <p><?= esc($excerpt) ?></p>
              </a>
            </article>
<?php endforeach; endif; ?>
          </div>
          <div class="hero-actions" style="margin-top: 18px" data-reveal>
            <a class="btn primary" href="news.php" data-i18n="btn.allNews">Xem tất cả tin tức</a>
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
