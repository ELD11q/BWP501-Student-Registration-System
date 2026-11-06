<?php
require "db.php";
checkLogin();

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("Location: view_students.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);

$stmt = $conn->prepare("SELECT profile_picture FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if ($student) {
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);
    deletePicture($student['profile_picture']);
    $_SESSION['message'] = "تم حذف الطالب بنجاح";
}

header("Location: view_students.php");
exit;
