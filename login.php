<?php
/*
 * login.php - validates credentials (username OR email + password) and starts a session.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$login    = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

if ($login === '' || $password === '') {
    header('Location: login.html?error=empty');
    exit;
}

$stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ? OR email = ? LIMIT 1');
$stmt->execute([$login, $login]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);               // prevent session fixation
    $_SESSION['user_id']  = (int)$user['id'];
    $_SESSION['username'] = $user['username'];
    flash('success', 'Welcome back, ' . $user['username'] . '!');
    header('Location: view_students.php');
    exit;
}

header('Location: login.html?error=invalid');
exit;
