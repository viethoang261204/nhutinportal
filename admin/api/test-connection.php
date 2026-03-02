<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

try {
    $pdo = getDbConnection();
    $version = $pdo->query('SELECT VERSION() AS version')->fetch();

    echo json_encode([
        'success' => true,
        'message' => 'MySQL connection successful',
        'mysql_version' => $version['version'] ?? null,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'MySQL connection failed',
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

