<?php
require "db.php";

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("Location: register.html");
    exit;
}

$username = trim($_POST['username'] ?? "");
$email = trim($_POST['email'] ?? "");
$password = $_POST['password'] ?? "";
$confirm = $_POST['confirm_password'] ?? "";

if ($username == "" || $email == "" || $password == "" || $confirm == "") {
    header("Location: register.html?error=empty");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.html?error=email");
    exit;
}

if (strlen($password) < 6) {
    header("Location: register.html?error=short");
    exit;
}

if ($password != $confirm) {
    header("Location: register.html?error=match");
    exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);

if ($stmt->fetch()) {
    header("Location: register.html?error=exists");
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->execute([$username, $email, $hash]);

header("Location: login.html?success=registered");
exit;
