<?php
declare(strict_types=1);

/**
 * API công khai - trả về thông tin công ty từ cài đặt admin (site_settings).
 * Dùng cho trang support, footer, v.v. - không cần đăng nhập.
 */
require_once __DIR__ . '/../../admin/config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function respondJson(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

const PUBLIC_KEYS = ['company_name', 'contact_email', 'contact_phone', 'company_address'];

const DEFAULT_COMPANY = [
    'company_name' => 'CÔNG TY CỔ PHẦN NHƯ TÍN',
    'contact_email' => 'contact@nhutin.vn',
    'contact_phone' => '0909 123 456',
    'company_address' => '123 Đường ABC, Quận 1, TP.HCM',
];

try {
    $pdo = getDbConnection();
    $out = DEFAULT_COMPANY;

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $stmt = $pdo->prepare(
        "SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('company_name','contact_email','contact_phone','company_address')"
    );
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $k = (string) ($row['setting_key'] ?? '');
        if (in_array($k, PUBLIC_KEYS, true)) {
            $out[$k] = (string) ($row['setting_value'] ?? '');
        }
    }

    respondJson(200, [
        'success' => true,
        'company' => [
            'name' => $out['company_name'],
            'email' => $out['contact_email'],
            'phone' => $out['contact_phone'],
            'address' => $out['company_address'],
        ],
    ]);
} catch (Throwable $e) {
    error_log('Portal settings API: ' . $e->getMessage());
    respondJson(200, [
        'success' => true,
        'company' => DEFAULT_COMPANY,
    ]);
}
