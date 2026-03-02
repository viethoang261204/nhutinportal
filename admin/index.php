<?php
/**
 * Admin entry point - /admin
 * Có session → Dashboard | Không có session → Login
 */
session_name('nhutin_admin_session');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!empty($_SESSION['nhutin_admin_logged_in']) && !empty($_SESSION['nhutin_admin'])) {
    header('Location: /admin/dashboard.html', true, 302);
    exit;
}

header('Location: /admin/login.html', true, 302);
exit;
