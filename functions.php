<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/schema_updates.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function current_user(): ?array
{
    ensure_progress03_schema(db());
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT u.id AS user_id, u.username, e.id AS employee_id, e.employee_code,
                e.full_name, e.profession, r.id AS role_id, r.code AS role_code,
                r.name AS role_name, un.name AS unit_name
         FROM users u
         JOIN employees e ON e.id = u.employee_id
         JOIN roles r ON r.id = e.role_id
         LEFT JOIN units un ON un.id = e.unit_id
         WHERE u.id = ? AND u.is_active = 1
         LIMIT 1'
    );
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        redirect('login.php');
    }
    return $user;
}

function user_has_permission(int $roleId, string $permissionCode): bool
{
    $stmt = db()->prepare(
        'SELECT 1
         FROM role_permissions rp
         JOIN permissions p ON p.id = rp.permission_id
         WHERE rp.role_id = ? AND p.code = ?
         LIMIT 1'
    );
    $stmt->execute([$roleId, $permissionCode]);
    return (bool) $stmt->fetchColumn();
}

function require_permission(array $user, string $permissionCode): void
{
    if (!user_has_permission((int) $user['role_id'], $permissionCode)) {
        http_response_code(403);
        echo '<h2 style="font-family:Arial">403 — Akses ditolak</h2><p>Role Anda tidak memiliki akses ke halaman ini.</p>';
        exit;
    }
}

function sidebar_menus(int $roleId): array
{
    $stmt = db()->prepare(
        'SELECT DISTINCT m.label, m.icon, m.section_name, m.page_file, m.sort_order
         FROM menus m
         JOIN permissions p ON p.code = m.permission_code
         JOIN role_permissions rp ON rp.permission_id = p.id
         WHERE rp.role_id = ? AND m.is_active = 1
         ORDER BY m.sort_order, m.id'
    );
    $stmt->execute([$roleId]);
    return $stmt->fetchAll();
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }

    $message = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $message;
}

function make_code(string $prefix, int $id, int $pad = 4): string
{
    return $prefix . '-' . str_pad((string) $id, $pad, '0', STR_PAD_LEFT);
}
