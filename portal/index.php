<?php
/**
 * Portal entry point - /portal hoặc /portal/
 * Có session → Dashboard | Không có session → Login
 */
session_name('nhutin_portal_session');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!empty($_SESSION['nhutin_portal_logged_in']) && !empty($_SESSION['nhutin_portal_user'])) {
    header('Location: /portal/dashboard.html', true, 302);
    exit;
}

header('Location: /portal/login.html', true, 302);
exit;
