<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Liên hệ — NHUTIN</title>
    <meta name="description" content="Liên hệ NHUTIN để được tư vấn về nhiên liệu sinh khối bã điều và hệ thống sàn trượt tự đổ." />
    <link rel="canonical" href="https://nhutincompany.com/lien-he" />
    <link rel="icon" href="/img/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/css/site.css" />
    <style>
      .contact-hero {
        position: relative;
        min-height: 100svh;
        display: flex;
        align-items: center;
        padding-top: 100px;
        padding-bottom: 80px;
        background: linear-gradient(to bottom, #0b3d35 50%, transparent 100%);
        overflow: hidden;
      }
      .contact-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: url('img/nhutinbanner.png') center / cover no-repeat;
        opacity: 0.58;
        -webkit-mask-image: linear-gradient(to bottom, black 72%, transparent 98%);
        mask-image: linear-gradient(to bottom, black 72%, transparent 98%);
        pointer-events: none;
      }
      .contact-hero::after {
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
      .contact-hero .container { position: relative; z-index: 1; width: 100%; }
      .contact-hero-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        max-width: 860px;
        margin: 0 auto;
      }
      .contact-hero h1 { margin: 0 0 20px; font-size: clamp(28px, 3.8vw, 54px); font-weight: 800; letter-spacing: -0.03em; line-height: 1.15; color: #ffffff; text-shadow: 0 2px 24px rgba(0,0,0,0.35); }
      .contact-hero .sub { font-size: 16px; line-height: 1.85; max-width: 78ch; margin: 0 auto; color: rgba(255,255,255,0.88); }
      .contact-hero .btn:not(.primary) { background: var(--deep); border-color: var(--deep); color: #ffffff; }
      .contact-hero .btn:not(.primary):hover { background: #0e4a3e; border-color: #0e4a3e; }

      .contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: start; }
      .contact-info-card { display: flex; flex-direction: column; gap: 20px; }
      .contact-item { display: flex; gap: 16px; align-items: flex-start; }
      .contact-item-icon {
        width: 48px; height: 48px; border-radius: 12px; background: var(--primary); display: flex;
        align-items: center; justify-content: center; flex-shrink: 0; color: white;
      }
      .contact-item-text strong { display: block; font-size: 15px; margin-bottom: 4px; color: var(--deep); }
      .contact-item-text span, .contact-item-text a { font-size: 14px; color: rgba(11,18,32,0.75); text-decoration: none; }
      .contact-item-text a:hover { text-decoration: underline; color: var(--primary); }

      .contact-form-card { background: white; border-radius: 16px; padding: 32px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
      .contact-form-card h2 { font-size: 22px; font-weight: 700; margin: 0 0 24px; color: var(--deep); }
      .form-group { margin-bottom: 18px; }
      .form-group label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 6px; color: var(--deep); }
      .form-group input, .form-group textarea, .form-group select {
        width: 100%; padding: 10px 14px; border: 1.5px solid rgba(11,18,32,0.15);
        border-radius: 10px; font-size: 15px; font-family: inherit; color: var(--deep);
        background: #fafbfc; transition: border-color 0.15s; box-sizing: border-box;
        outline: none;
      }
      .form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: var(--primary); background: white; }
      .form-group textarea { resize: vertical; min-height: 120px; }
      .form-group select { cursor: pointer; }

      @media (max-width: 768px) {
        .contact-grid { grid-template-columns: 1fr; }
        .contact-hero { padding-top: 90px; padding-bottom: 60px; }
      }
    </style>
  </head>

  <body>
    <div class="bgfx" aria-hidden="true"></div>
    <a class="skip" href="#main">Bỏ qua menu</a>

    <header class="topbar" data-include="/components/navbar.html"></header>

    <main id="main">
      <section class="contact-hero">
        <div class="container">
          <div class="contact-hero-inner" data-reveal>
            <h1>Liên hệ NHUTIN</h1>
            <p class="sub">
              Chúng tôi sẵn sàng tư vấn và hỗ trợ bạn về giải pháp nhiên liệu sinh khối bã điều
              và hệ thống sàn trượt tự đổ. Đội ngũ kỹ thuật đồng hành từ khảo sát đến triển khai.
            </p>
            <div class="hero-actions" style="margin-top: 24px; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
              <a class="btn primary large" href="tel:0907917301">0907 917 301</a>
              <a class="btn large" href="https://zalo.me/0907917301" target="_blank" rel="noopener noreferrer">Chat Zalo</a>
            </div>
          </div>
        </div>
      </section>

      <section class="section">
        <div class="container">
          <div class="contact-grid">
            <div data-reveal>
              <div class="contact-info-card">
                <div class="contact-item">
                  <div class="contact-item-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                  </div>
                  <div class="contact-item-text">
                    <strong>Địa chỉ</strong>
                    <span>39 Chu Văn An, KP1, P. Hiệp Phú, Tp. Thủ Đức, Tp. HCM</span>
                  </div>
                </div>

                <div class="contact-item">
                  <div class="contact-item-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M22 16.92V19.92C22 20.48 21.56 20.92 21 20.92C10.45 20.92 2 12.47 2 2C2 1.44 2.44 1 3 1H6C6.56 1 7 1.44 7 2C7 3.85 7.35 5.62 7.98 7.25C8.11 7.56 8.03 7.92 7.77 8.17L5.76 10.18C7.5 13.69 10.31 16.5 13.82 18.24L15.83 16.23C16.08 15.98 16.44 15.89 16.75 16.02C18.38 16.65 20.15 17 22 17C22.56 17 23 17.44 23 18V19.92C23 20.48 22.56 20.92 22 20.92Z"/>
                    </svg>
                  </div>
                  <div class="contact-item-text">
                    <strong>Hotline 1</strong>
                    <a href="tel:0907917301">0907 917 301</a>
                  </div>
                </div>

                <div class="contact-item">
                  <div class="contact-item-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M22 16.92V19.92C22 20.48 21.56 20.92 21 20.92C10.45 20.92 2 12.47 2 2C2 1.44 2.44 1 3 1H6C6.56 1 7 1.44 7 2C7 3.85 7.35 5.62 7.98 7.25C8.11 7.56 8.03 7.92 7.77 8.17L5.76 10.18C7.5 13.69 10.31 16.5 13.82 18.24L15.83 16.23C16.08 15.98 16.44 15.89 16.75 16.02C18.38 16.65 20.15 17 22 17C22.56 17 23 17.44 23 18V19.92C23 20.48 22.56 20.92 22 20.92Z"/>
                    </svg>
                  </div>
                  <div class="contact-item-text">
                    <strong>Hotline 2</strong>
                    <a href="tel:0978572662">0978 572 662</a>
                  </div>
                </div>

                <div class="contact-item">
                  <div class="contact-item-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                  </div>
                  <div class="contact-item-text">
                    <strong>Email</strong>
                    <a href="mailto:nhutincorp@gmail.com">nhutincorp@gmail.com</a>
                  </div>
                </div>

                <div class="contact-item">
                  <div class="contact-item-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                    </svg>
                  </div>
                  <div class="contact-item-text">
                    <strong>Giờ làm việc</strong>
                    <span>Thứ 2 – Thứ 6: 8:00 – 17:30</span>
                  </div>
                </div>

                <div class="contact-item">
                  <div class="contact-item-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                    </svg>
                  </div>
                  <div class="contact-item-text">
                    <strong>Website</strong>
                    <a href="https://bavohatdieu.com" target="_blank" rel="noopener noreferrer">bavohatdieu.com</a><br>
                    <a href="https://thungxetudo.com" target="_blank" rel="noopener noreferrer">thungxetudo.com</a>
                  </div>
                </div>
              </div>
            </div>

            <div data-reveal>
              <div class="contact-form-card">
                <h2>Gửi yêu cầu tư vấn</h2>
                <form id="contactForm">
                  <div class="form-group">
                    <label for="name">Họ và tên *</label>
                    <input type="text" id="name" name="name" placeholder="Nhập họ và tên" required />
                  </div>
                  <div class="form-group">
                    <label for="phone">Số điện thoại *</label>
                    <input type="tel" id="phone" name="phone" placeholder="090x xxx xxx" required />
                  </div>
                  <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="email@domain.com" />
                  </div>
                  <div class="form-group">
                    <label for="service">Dịch vụ quan tâm</label>
                    <select id="service" name="service">
                      <option value="">-- Chọn dịch vụ --</option>
                      <option value="biomass">Nhiên liệu sinh khối bã điều</option>
                      <option value="floor">Hệ thống sàn trượt tự đổ</option>
                      <option value="both">Cả hai dịch vụ</option>
                      <option value="other">Khác</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="message">Nội dung *</label>
                    <textarea id="message" name="message" placeholder="Mô tả nhu cầu, quy mô, địa điểm..." required></textarea>
                  </div>
                  <button type="submit" class="btn primary" style="width: 100%; padding: 14px; font-size: 16px; border-radius: 10px;">
                    Gửi yêu cầu
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="section" style="background: #f8f9fa">
        <div class="container">
          <div data-reveal>
            <h2 class="h2" style="text-align:center; margin-bottom: 8px">Bạn cần tư vấn nhanh?</h2>
            <p class="sub" style="text-align:center; max-width: 600px; margin: 0 auto 24px">
              Gọi trực tiếp hoặc nhắn Zalo, đội ngũ NHUTIN phản hồi trong vòng 30 phút trong giờ làm việc.
            </p>
          </div>
          <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; max-width: 700px; margin: 0 auto;">
            <div class="card padded solid" style="text-align: center" data-reveal>
              <div style="font-size: 32px; margin-bottom: 8px">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M22 16.92V19.92C22 20.48 21.56 20.92 21 20.92C10.45 20.92 2 12.47 2 2C2 1.44 2.44 1 3 1H6C6.56 1 7 1.44 7 2C7 3.85 7.35 5.62 7.98 7.25C8.11 7.56 8.03 7.92 7.77 8.17L5.76 10.18C7.5 13.69 10.31 16.5 13.82 18.24L15.83 16.23C16.08 15.98 16.44 15.89 16.75 16.02C18.38 16.65 20.15 17 22 17C22.56 17 23 17.44 23 18V19.92C23 20.48 22.56 20.92 22 20.92Z"/></svg>
              </div>
              <strong>0907 917 301</strong>
              <p style="font-size: 13px; color: var(--muted, #666); margin: 4px 0 12px">Hotline 1</p>
              <a class="btn primary small" href="tel:0907917301">Gọi ngay</a>
            </div>
            <div class="card padded solid" style="text-align: center" data-reveal>
              <div style="font-size: 32px; margin-bottom: 8px">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M22 16.92V19.92C22 20.48 21.56 20.92 21 20.92C10.45 20.92 2 12.47 2 2C2 1.44 2.44 1 3 1H6C6.56 1 7 1.44 7 2C7 3.85 7.35 5.62 7.98 7.25C8.11 7.56 8.03 7.92 7.77 8.17L5.76 10.18C7.5 13.69 10.31 16.5 13.82 18.24L15.83 16.23C16.08 15.98 16.44 15.89 16.75 16.02C18.38 16.65 20.15 17 22 17C22.56 17 23 17.44 23 18V19.92C23 20.48 22.56 20.92 22 20.92Z"/></svg>
              </div>
              <strong>0978 572 662</strong>
              <p style="font-size: 13px; color: var(--muted, #666); margin: 4px 0 12px">Hotline 2</p>
              <a class="btn primary small" href="tel:0978572662">Gọi ngay</a>
            </div>
            <div class="card padded solid" style="text-align: center" data-reveal>
              <div style="font-size: 32px; margin-bottom: 8px">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="#0068FF"><path d="M12 2C6.48 2 2 6.48 2 12C2 14.85 3.24 17.41 5.25 19.13L4 22L7.05 20.82C8.56 21.57 10.24 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2Z"/></svg>
              </div>
              <strong>Zalo</strong>
              <p style="font-size: 13px; color: var(--muted, #666); margin: 4px 0 12px">Nhắn nhanh</p>
              <a class="btn primary small" href="https://zalo.me/0907917301" target="_blank" rel="noopener noreferrer">Mở Zalo</a>
            </div>
          </div>
        </div>
      </section>

      <div data-include="/components/footer.html"></div>
    </main>

    <script src="/js/i18n.js" defer></script>
    <script src="/js/include.js" defer></script>
    <script src="/js/site.js" defer></script>
    <script>
      document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var name = document.getElementById('name').value.trim();
        var phone = document.getElementById('phone').value.trim();
        var email = document.getElementById('email').value.trim();
        var service = document.getElementById('service').value;
        var message = document.getElementById('message').value.trim();

        if (!name || !phone || !message) {
          alert('Vui lòng điền đầy đủ thông tin bắt buộc.');
          return;
        }

        var serviceLabels = { biomass: 'Nhiên liệu sinh khối bã điều', floor: 'Hệ thống sàn trượt tự đổ', both: 'Cả hai dịch vụ', other: 'Khác' };
        var subject = encodeURIComponent('[NHUTIN] Yêu cầu tư vấn từ website');
        var body = encodeURIComponent(
          'Họ và tên: ' + name + '\n' +
          'SĐT: ' + phone + '\n' +
          'Email: ' + (email || 'Không có') + '\n' +
          'Dịch vụ quan tâm: ' + (serviceLabels[service] || 'Không chọn') + '\n\n' +
          'Tin nhắn:\n' + message + '\n'
        );
        window.location.href = 'mailto:nhutincorp@gmail.com?subject=' + subject + '&body=' + body;
      });
    </script>
  </body>
</html>
