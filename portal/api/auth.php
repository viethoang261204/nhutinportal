<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

session_name('nhutin_portal_session');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function respondJson(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['nhutin_portal_logged_in']) || empty($_SESSION['nhutin_portal_user'])) {
    respondJson(401, ['success' => false, 'authenticated' => false]);
}

respondJson(200, [
    'success' => true,
    'authenticated' => true,
    'user' => $_SESSION['nhutin_portal_user'],
]);
