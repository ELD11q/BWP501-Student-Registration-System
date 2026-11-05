<?php
/*
 * register.php - handles new user registration (inserts into the `users` table).
 * The form itself is register.html; errors are sent back as ?error=code.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit;
}

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

function back(string $code): void
{
    header('Location: register.html?error=' . urlencode($code));
    exit;
}

// Server-side validation (never trust the browser only)
if ($username === '' || $email === '' || $password === '' || $confirm === '') {
    back('empty');
}
if (!preg_match('/^[A-Za-z0-9_]{3,100}$/', $username)) {
    back('username');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    back('email');
}
if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    back('weak');
}
if ($password !== $confirm) {
    back('mismatch');
}

// Username / email must be unique
$stmt = $pdo->prepare('SELECT username, email FROM users WHERE username = ? OR email = ?');
$stmt->execute([$username, $email]);
if ($row = $stmt->fetch()) {
    back(strcasecmp($row['username'], $username) === 0 ? 'username_taken' : 'email_taken');
}

// Secure password hashing
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
$stmt->execute([$username, $email, $hash]);

header('Location: login.html?success=registered');
exit;
