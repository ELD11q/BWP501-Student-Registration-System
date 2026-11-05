<?php
/*
 * validate_student.php - AJAX endpoint for real-time form validation.
 * Receives the form fields by POST and returns JSON: {"valid": bool, "errors": {field: message}}
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/student_validation.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['valid' => false, 'errors' => ['_' => 'Please log in again.']]);
    exit;
}

$ignoreId = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : null;
$errors   = validate_student($_POST, $pdo, $ignoreId);

// When validating a single field (on blur), only report that field
$field = $_POST['field'] ?? '';
if ($field !== '') {
    $errors = isset($errors[$field]) ? [$field => $errors[$field]] : [];
}

echo json_encode(['valid' => empty($errors), 'errors' => (object)$errors]);
