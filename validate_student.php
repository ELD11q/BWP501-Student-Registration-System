<?php
require "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["valid" => false, "errors" => ["login" => "Please login first"]]);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$errors = validateStudent($conn, $_POST, $id);

echo json_encode(["valid" => empty($errors), "errors" => (object)$errors]);
