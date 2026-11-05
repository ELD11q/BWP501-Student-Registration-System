<?php
/*
 * update_student.php - pre-fills the form with the student's current data (GET)
 * and saves the changes (POST), with the option to replace/remove the profile picture.
 */
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/student_validation.php';

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$id]);
$current = $stmt->fetch();

if (!$current) {
    flash('warning', 'Student not found.');
    header('Location: view_students.php');
    exit;
}

$student = $current;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    foreach (['name', 'email', 'phone', 'address'] as $f) {
        $student[$f] = trim($_POST[$f] ?? '');
    }
    $errors = validate_student($_POST, $pdo, $id);

    $picturePath = $current['profile_picture'];
    if (empty($errors)) {
        [$newPath, $uploadError] = handle_picture_upload();
        if ($uploadError) {
            $errors['profile_picture'] = $uploadError;
        } elseif ($newPath) {                       // new picture uploaded -> replace old one
            delete_picture($current['profile_picture']);
            $picturePath = $newPath;
        } elseif (!empty($_POST['remove_picture'])) {
            delete_picture($current['profile_picture']);
            $picturePath = null;
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'UPDATE students SET name = ?, email = ?, phone = ?, address = ?, profile_picture = ? WHERE id = ?'
        );
        $stmt->execute([$student['name'], $student['email'], $student['phone'], $student['address'], $picturePath, $id]);

        flash('success', 'Student "' . $student['name'] . '" was updated successfully.');
        header('Location: view_students.php');
        exit;
    }
}

$pageTitle   = 'Update Student';
$pageScripts = ['assets/js/validation.js'];
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h3 class="mb-0"><i class="bi bi-pencil-square text-primary"></i> Update Student</h3>
            </div>
            <div class="card-body p-4">
                <?php
                $formAction  = 'update_student.php';
                $submitLabel = 'Save Changes';
                $isUpdate    = true;
                require __DIR__ . '/includes/student_form.php';
                ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
