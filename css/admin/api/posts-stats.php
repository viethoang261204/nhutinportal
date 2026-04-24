<?php
declare(strict_types=1);

/**
 * Kiểm tra nhanh: tổng bài viết, published, draft.
 * Mở trực tiếp: admin/api/posts-stats.php
 */
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$out = ['ok' => false, 'total' => 0, 'published' => 0, 'draft' => 0, 'message' => ''];
try {
    $pdo = getDbConnection();
    $out['total'] = (int) $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    $out['published'] = (int) $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
    $out['draft'] = $out['total'] - $out['published'];
    $out['ok'] = true;
} catch (Throwable $e) {
    $out['message'] = $e->getMessage();
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);
