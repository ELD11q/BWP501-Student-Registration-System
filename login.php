<?php
require "db.php";

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("Location: login.html");
    exit;
}

$login = trim($_POST['login'] ?? "");
$password = $_POST['password'] ?? "";

if ($login == "" || $password == "") {
    header("Location: login.html?error=empty");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmt->execute([$login, $login]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    header("Location: view_students.php");
    exit;
}

header("Location: login.html?error=wrong");
exit;
