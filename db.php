<?php
session_start();

$host = "localhost";
$dbname = "student_registration_system";
$dbuser = "root";
$dbpass = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات");
}

function checkLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.html?error=login");
        exit;
    }
}

function clean($text)
{
    return htmlspecialchars($text ?? "", ENT_QUOTES, "UTF-8");
}

function validateStudent($conn, $data, $id = 0)
{
    $errors = [];
    $name = trim($data['name'] ?? "");
    $email = trim($data['email'] ?? "");
    $phone = trim($data['phone'] ?? "");
    $address = trim($data['address'] ?? "");

    if ($name == "") {
        $errors['name'] = "الاسم مطلوب";
    } elseif (strlen($name) > 100) {
        $errors['name'] = "الاسم طويل جداً";
    }

    if ($email == "") {
        $errors['email'] = "البريد الإلكتروني مطلوب";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "البريد الإلكتروني غير صحيح";
    } else {
        $stmt = $conn->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            $errors['email'] = "هذا البريد مستخدم لطالب آخر";
        }
    }

    if ($phone == "") {
        $errors['phone'] = "رقم الجوال مطلوب";
    } elseif (!preg_match('/^\+?[0-9]{7,14}$/', $phone)) {
        $errors['phone'] = "رقم الجوال يجب أن يكون من 7 إلى 14 رقماً";
    }

    if ($address == "") {
        $errors['address'] = "العنوان مطلوب";
    }

    return $errors;
}

function uploadPicture()
{
    if (!isset($_FILES['picture']) || $_FILES['picture']['error'] == UPLOAD_ERR_NO_FILE) {
        return ["path" => null, "error" => null];
    }

    $file = $_FILES['picture'];

    if ($file['error'] != UPLOAD_ERR_OK) {
        return ["path" => null, "error" => "فشل رفع الصورة"];
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        return ["path" => null, "error" => "حجم الصورة يجب أن يكون أقل من 2 ميجابايت"];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ["jpg", "jpeg", "png", "gif", "webp"];

    if (!in_array($ext, $allowed) || getimagesize($file['tmp_name']) === false) {
        return ["path" => null, "error" => "يُسمح بالصور فقط (jpg, png, gif, webp)"];
    }

    $newName = "uploads/" . time() . "_" . rand(1000, 9999) . "." . $ext;

    if (!move_uploaded_file($file['tmp_name'], __DIR__ . "/" . $newName)) {
        return ["path" => null, "error" => "تعذر حفظ الصورة"];
    }

    return ["path" => $newName, "error" => null];
}

function deletePicture($path)
{
    if ($path && file_exists(__DIR__ . "/" . $path)) {
        unlink(__DIR__ . "/" . $path);
    }
}
