<?php
/*
 * logout.php - logs the user out by destroying the session.
 */
require_once __DIR__ . '/includes/auth.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

header('Location: login.html?success=logged_out');
exit;
