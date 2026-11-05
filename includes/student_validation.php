<?php
/*
 * Shared validation for the student forms.
 * Used by the AJAX endpoint (validate_student.php) AND by the server-side
 * handlers (add/update), so the rules are the same in both places.
 */

/**
 * @return array field => error message (empty array = valid)
 */
function validate_student(array $data, PDO $pdo, ?int $ignoreId = null): array
{
    $errors = [];
    $name    = trim($data['name'] ?? '');
    $email   = trim($data['email'] ?? '');
    $phone   = trim($data['phone'] ?? '');
    $address = trim($data['address'] ?? '');

    if ($name === '') {
        $errors['name'] = 'Name is required.';
    } elseif (mb_strlen($name) > 100) {
        $errors['name'] = 'Name must be 100 characters or less.';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $errors['email'] = 'Please enter a valid email address.';
    } else {
        $sql = 'SELECT id FROM students WHERE email = ?' . ($ignoreId ? ' AND id <> ?' : '');
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ignoreId ? [$email, $ignoreId] : [$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'A student with this email already exists.';
        }
    }

    if ($phone === '') {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^\+?[0-9 \-]{7,15}$/', $phone)) {
        $errors['phone'] = 'Phone must be 7-15 digits (may start with +).';
    }

    if ($address === '') {
        $errors['address'] = 'Address is required.';
    }

    return $errors;
}

/**
 * Handles the optional profile picture upload with move_uploaded_file().
 * @return array [string|null $path, string|null $error]
 */
function handle_picture_upload(string $field = 'profile_picture'): array
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];                       // optional: nothing uploaded
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'File upload failed. Please try again.'];
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        return [null, 'Image must be 2 MB or smaller.'];
    }

    // Check the real file type (not just the extension)
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return [null, 'Only JPG, PNG, GIF or WEBP images are allowed.'];
    }

    $dir = __DIR__ . '/../uploads/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $newName = 'student_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . $newName)) {
        return [null, 'Could not save the uploaded image.'];
    }
    return ['uploads/' . $newName, null];          // relative path saved in the database
}

/** Deletes an old picture file from the uploads folder. */
function delete_picture(?string $path): void
{
    if ($path && strpos($path, 'uploads/') === 0) {
        $full = __DIR__ . '/../' . $path;
        if (is_file($full)) {
            unlink($full);
        }
    }
}
