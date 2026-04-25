<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function is_customer(): bool
{
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'customer';
}

function require_auth(): void
{
    if (!is_logged_in()) {
        header('Location: ' . app_url('login.php'));
        exit;
    }
}

/**
 * 🔒 CRITICAL: ensures garage context is always present
 */
function require_garage(): void
{
    if (!isset($_SESSION['user']['garage_id'])) {
        session_destroy();
        header('Location: ' . app_url('login.php'));
        exit;
    }
}

function require_admin(): void
{
    require_auth();
    require_garage();

    if (!is_admin()) {
        header('Location: ' . app_url('customer/dashboard.php'));
        exit;
    }
}

function require_customer(): void
{
    require_auth();
    require_garage();

    if (!is_customer()) {
        header('Location: ' . app_url('admin/dashboard.php'));
        exit;
    }
}

/**
 * 🔥 Optional helper (very useful later)
 */
function require_same_garage(int $garageId): void
{
    if ((int)($_SESSION['user']['garage_id'] ?? 0) !== $garageId) {
        session_destroy();
        header('Location: ' . app_url('login.php'));
        exit;
    }
}
?>