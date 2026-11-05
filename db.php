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
    die("Database connection failed");
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
        $errors['name'] = "Name is required";
    } elseif (strlen($name) > 100) {
        $errors['name'] = "Name is too long";
    }

    if ($email == "") {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Email is not valid";
    } else {
        $stmt = $conn->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            $errors['email'] = "This email is already used";
        }
    }

    if ($phone == "") {
        $errors['phone'] = "Phone is required";
    } elseif (!preg_match('/^\+?[0-9]{7,14}$/', $phone)) {
        $errors['phone'] = "Phone must be 7 to 14 numbers";
    }

    if ($address == "") {
        $errors['address'] = "Address is required";
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
        return ["path" => null, "error" => "Upload failed"];
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        return ["path" => null, "error" => "Picture must be less than 2MB"];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ["jpg", "jpeg", "png", "gif", "webp"];

    if (!in_array($ext, $allowed) || getimagesize($file['tmp_name']) === false) {
        return ["path" => null, "error" => "Only images are allowed (jpg, png, gif, webp)"];
    }

    $newName = "uploads/" . time() . "_" . rand(1000, 9999) . "." . $ext;

    if (!move_uploaded_file($file['tmp_name'], __DIR__ . "/" . $newName)) {
        return ["path" => null, "error" => "Could not save the picture"];
    }

    return ["path" => $newName, "error" => null];
}

function deletePicture($path)
{
    if ($path && file_exists(__DIR__ . "/" . $path)) {
        unlink(__DIR__ . "/" . $path);
    }
}
