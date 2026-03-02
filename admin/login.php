<?php
/**
 * Admin Login - Nếu đã đăng nhập thì chuyển hướng về Dashboard
 */
session_name('nhutin_admin_session');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!empty($_SESSION['nhutin_admin_logged_in']) && !empty($_SESSION['nhutin_admin'])) {
    header('Location: dashboard.html', true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - NHUTIN</title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Be Vietnam Pro', sans-serif; background: linear-gradient(180deg, #bfdbfe 0%, #93c5fd 25%, #bae6fd 50%, #cffafe 75%, #e0f2fe 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; background-attachment: fixed; }
        .login-container { display: flex; background: #fff; border-radius: 24px; box-shadow: 0 20px 60px rgba(0, 119, 182, 0.15); overflow: hidden; max-width: 1000px; width: 100%; min-height: 600px; }
        .login-left { flex: 1; background: linear-gradient(180deg, #0077b6 0%, #005a8d 100%); padding: 60px 50px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden; }
        .login-left::before { content: ''; position: absolute; top: -50%; right: -50%; width: 100%; height: 100%; background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%); border-radius: 50%; }
        .logo { display: flex; align-items: center; gap: 12px; color: #fff; position: relative; z-index: 1; }
        .logo-icon { width: 48px; height: 48px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; }
        .logo-icon svg { width: 28px; height: 28px; stroke: #fff; fill: none; stroke-width: 2; }
        .logo-text { font-size: 22px; font-weight: 700; letter-spacing: -0.5px; }
        .welcome-content { position: relative; z-index: 1; }
        .welcome-title { color: #fff; font-size: 32px; font-weight: 700; line-height: 1.3; margin-bottom: 16px; }
        .welcome-desc { color: rgba(255,255,255,0.85); font-size: 15px; line-height: 1.7; }
        .features-list { position: relative; z-index: 1; display: flex; flex-direction: column; gap: 14px; }
        .feature-item { display: flex; align-items: center; gap: 12px; color: rgba(255,255,255,0.9); font-size: 14px; font-weight: 500; }
        .feature-icon { width: 32px; height: 32px; background: rgba(255,255,255,0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .feature-icon svg { width: 16px; height: 16px; stroke: #fff; fill: none; }
        .login-right { flex: 1; padding: 60px 50px; display: flex; flex-direction: column; justify-content: center; }
        .login-header { margin-bottom: 40px; }
        .login-header h2 { font-size: 26px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
        .login-header p { color: #666; font-size: 14px; }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #333; margin-bottom: 8px; }
        .form-group input { width: 100%; padding: 14px 16px; border: 1.5px solid #e0e0e0; border-radius: 12px; font-size: 14px; font-family: inherit; transition: all 0.3s ease; background: #fafafa; }
        .form-group input:focus { outline: none; border-color: #0077b6; background: #fff; box-shadow: 0 0 0 4px rgba(0, 119, 182, 0.1); }
        .password-wrap { position: relative; }
        .password-wrap input { padding-right: 48px; }
        .password-wrap .toggle-pw { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 24px; height: 24px; border: none; background: none; cursor: pointer; color: #666; padding: 0; display: flex; align-items: center; justify-content: center; }
        .password-wrap .toggle-pw:hover { color: #0077b6; }
        .form-group input::placeholder { color: #999; }
        .login-btn { width: 100%; padding: 16px; background: linear-gradient(135deg, #3B82F6 0%, #1E40AF 100%); color: #fff; border: none; border-radius: 12px; font-size: 15px; font-weight: 700; font-family: inherit; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0, 119, 182, 0.3); }
        .login-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0, 119, 182, 0.4); }
        .login-btn:active { transform: translateY(0); }
        .login-message { display: none; margin-bottom: 16px; padding: 12px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; }
        .login-message.error { display: block; color: #b91c1c; background: #fee2e2; border: 1px solid #fecaca; }
        .login-message.success { display: block; color: #166534; background: #dcfce7; border: 1px solid #bbf7d0; }
        .security-note { margin-top: 24px; padding: 14px; background: #FEF3C7; border-left: 3px solid #F59E0B; border-radius: 8px; font-size: 12px; color: #92400E; line-height: 1.6; }
        @media (max-width: 768px) { .login-container { flex-direction: column; min-height: auto; } .login-left { padding: 40px 30px; min-height: 300px; } .login-right { padding: 40px 30px; } .welcome-title { font-size: 24px; } }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="logo">
                <div class="logo-icon"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div>
                <span class="logo-text">NHUTIN Admin</span>
            </div>
            <div class="welcome-content">
                <h1 class="welcome-title">Admin Control Panel</h1>
                <p class="welcome-desc">Quản lý hệ thống, khách hàng, tài liệu và tickets một cách hiệu quả.</p>
            </div>
            <div class="features-list">
                <div class="feature-item"><div class="feature-icon"><svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div><span>Quản lý khách hàng & users</span></div>
                <div class="feature-item"><div class="feature-icon"><svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div><span>Quản lý tài liệu & dữ liệu</span></div>
                <div class="feature-item"><div class="feature-icon"><svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div><span>Cấu hình & bảo mật hệ thống</span></div>
            </div>
        </div>
        <div class="login-right">
            <div class="login-header"><h2>Đăng nhập Admin</h2><p>Vui lòng nhập thông tin quản trị viên</p></div>
            <form id="loginForm">
                <div id="loginMessage" class="login-message"></div>
                <div class="form-group"><label for="email">Email Admin</label><input type="email" id="email" placeholder="admin@nhutin.vn" required></div>
                <div class="form-group"><label for="password">Mật khẩu</label>
                    <div class="password-wrap">
                        <input type="password" id="password" placeholder="Nhập mật khẩu admin" required>
                        <button type="button" class="toggle-pw" id="togglePw" title="Hiện/ẩn mật khẩu" aria-label="Hiện mật khẩu">
                            <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeOffIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px;display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="login-btn">Đăng nhập Admin</button>
                <div class="security-note"><strong>Lưu ý bảo mật:</strong> Đây là khu vực quản trị viên. Không chia sẻ thông tin đăng nhập với bất kỳ ai.</div>
            </form>
        </div>
    </div>
    <script>
        (function(){
            var pw = document.getElementById('password');
            var toggle = document.getElementById('togglePw');
            var eyeIcon = document.getElementById('eyeIcon');
            var eyeOffIcon = document.getElementById('eyeOffIcon');
            if (toggle) toggle.addEventListener('click', function(){
                if (pw.type === 'password') { pw.type = 'text'; eyeIcon.style.display='none'; eyeOffIcon.style.display='block'; }
                else { pw.type = 'password'; eyeIcon.style.display='block'; eyeOffIcon.style.display='none'; }
            });
        })();
        var loginForm = document.getElementById('loginForm');
        var loginMessage = document.getElementById('loginMessage');
        var submitButton = loginForm.querySelector('button[type="submit"]');
        var csrfToken = '';
        function showMessage(type, text) { loginMessage.className = 'login-message ' + type; loginMessage.textContent = text; }
        function loadCsrfToken() { return fetch('./api/login.php?action=csrf', { method: 'GET', credentials: 'same-origin' }).then(function(r) { return r.json(); }); }
        loadCsrfToken().then(function(result) { if (result && result.success && result.csrf_token) csrfToken = result.csrf_token; }).catch(function() { showMessage('error', 'Không thể khởi tạo bảo mật. Vui lòng tải lại trang.'); });
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var email = document.getElementById('email').value.trim();
            var password = document.getElementById('password').value;
            if (!csrfToken) { showMessage('error', 'Phiên đăng nhập hết hạn. Vui lòng tải lại trang.'); return; }
            loginMessage.className = 'login-message'; loginMessage.textContent = '';
            submitButton.disabled = true; submitButton.textContent = 'Đang đăng nhập...';
            fetch('./api/login.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken }, credentials: 'same-origin', body: JSON.stringify({ email: email, password: password }) })
                .then(function(r) { return r.json(); })
                .then(function(result) {
                    if (!result.success) { showMessage('error', result.message || 'Đăng nhập thất bại.'); return; }
                    localStorage.setItem('nhutin_admin', JSON.stringify(result.admin));
                    localStorage.setItem('nhutin_admin_logged_in', 'true');
                    showMessage('success', 'Đăng nhập thành công, đang chuyển trang...');
                    setTimeout(function() { window.location.href = 'dashboard.html'; }, 500);
                })
                .catch(function() { showMessage('error', 'Không thể kết nối server. Vui lòng thử lại.'); })
                .finally(function() { submitButton.disabled = false; submitButton.textContent = 'Đăng nhập Admin'; });
        });
    </script>
</body>
</html>
