<?php
declare(strict_types=1);

/**
 * Auth & Activity helper - dùng chung cho các API admin
 * Gọi require sau khi session_start
 */

function getAuthRole(): string
{
    $admin = $_SESSION['nhutin_admin'] ?? [];
    return (string) ($admin['role'] ?? '');
}

function isAdmin(): bool
{
    return getAuthRole() === 'admin';
}

function isStaff(): bool
{
    return getAuthRole() === 'staff';
}

function getAuthUserId(): int
{
    $admin = $_SESSION['nhutin_admin'] ?? [];
    return (int) ($admin['id'] ?? 0);
}

function getAuthUserName(): string
{
    $admin = $_SESSION['nhutin_admin'] ?? [];
    return (string) ($admin['name'] ?? 'Unknown');
}

/** Chỉ admin và staff đã đăng nhập */
function ensureLoggedIn(): void
{
    if (empty($_SESSION['nhutin_admin_logged_in']) || empty($_SESSION['nhutin_admin'])) {
        if (function_exists('respondJson')) {
            respondJson(401, ['success' => false, 'message' => 'Vui lòng đăng nhập lại.']);
        }
        http_response_code(401);
        exit;
    }
}

/** Chỉ admin - staff bị chặn */
function ensureAdminOnly(): void
{
    ensureLoggedIn();
    if (!isAdmin()) {
        if (function_exists('respondJson')) {
            respondJson(403, ['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
        }
        http_response_code(403);
        exit;
    }
}

/** Admin hoặc staff - ai đăng nhập cũng dùng được */
function ensureAdminOrStaff(): void
{
    ensureLoggedIn();
    $role = getAuthRole();
    if ($role !== 'admin' && $role !== 'staff') {
        if (function_exists('respondJson')) {
            respondJson(403, ['success' => false, 'message' => 'Bạn không có quyền truy cập.']);
        }
        http_response_code(403);
        exit;
    }
}

function ensureActivityLogSchema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS activity_logs (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            user_name VARCHAR(100) NULL,
            user_role VARCHAR(20) NULL,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(50) NOT NULL,
            entity_id INT NULL,
            details TEXT NULL,
            description TEXT NULL,
            ip_address VARCHAR(45) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_activity_created (created_at),
            INDEX idx_activity_entity (entity_type, entity_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM activity_logs")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('user_name', $cols, true)) {
            $pdo->exec("ALTER TABLE activity_logs ADD COLUMN user_name VARCHAR(100) NULL DEFAULT '' AFTER user_id");
        }
        if (!in_array('user_role', $cols, true)) {
            $pdo->exec("ALTER TABLE activity_logs ADD COLUMN user_role VARCHAR(20) NULL DEFAULT 'staff' AFTER user_name");
        }
        if (!in_array('details', $cols, true) && in_array('description', $cols, true)) {
            $pdo->exec("ALTER TABLE activity_logs ADD COLUMN details TEXT NULL AFTER entity_id");
        }
    } catch (Throwable $e) {
        error_log('Activity schema migration: ' . $e->getMessage());
    }
}

function logActivity(
    PDO $pdo,
    string $action,
    string $entityType,
    ?int $entityId = null,
    ?string $details = null
): void {
    try {
        ensureActivityLogSchema($pdo);
        $stmt = $pdo->prepare(
            "INSERT INTO activity_logs (user_id, user_name, user_role, action, entity_type, entity_id, details, ip_address) 
             VALUES (:user_id, :user_name, :user_role, :action, :entity_type, :entity_id, :details, :ip)"
        );
        $stmt->execute([
            'user_id' => getAuthUserId(),
            'user_name' => getAuthUserName(),
            'user_role' => getAuthRole(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $e) {
        error_log('Activity log failed: ' . $e->getMessage());
    }
}
