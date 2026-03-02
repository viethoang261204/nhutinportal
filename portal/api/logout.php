<?php
/**
 * Portal Logout - Hủy session và chuyển về trang đăng nhập
 */
session_name('nhutin_portal_session');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'] ?? '/', $params['domain'] ?? '',
        $params['secure'] ?? false, $params['httponly'] ?? true
    );
}
session_destroy();

header('Location: /portal/login.html', true, 302);
exit;
