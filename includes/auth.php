<?php
/*
 * Session management + helpers shared by all PHP pages.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

/** Redirect to the login page if the user is not logged in (protects secure pages). */
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: login.html?error=login_required');
        exit;
    }
}

/** Escape output to prevent XSS. */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** CSRF token for forms that change data. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        http_response_code(400);
        die('Invalid request (CSRF token mismatch).');
    }
}

/** One-time flash message shown on the next page. */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash(): string
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="alert alert-' . e($f['type']) . ' alert-dismissible fade show" role="alert">'
        . e($f['message'])
        . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
