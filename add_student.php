<?php
/*
 * add_student.php - shows the Add Student form (GET) and inserts a new student (POST),
 * including the optional profile picture upload.
 */
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/student_validation.php';

$student = ['name' => '', 'email' => '', 'phone' => '', 'address' => '', 'profile_picture' => ''];
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $student = array_merge($student, array_map('trim', array_intersect_key($_POST, $student)));
    $errors  = validate_student($_POST, $pdo);

    if (empty($errors)) {
        [$picturePath, $uploadError] = handle_picture_upload();
        if ($uploadError) {
            $errors['profile_picture'] = $uploadError;
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'INSERT INTO students (name, email, phone, address, profile_picture) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$student['name'], $student['email'], $student['phone'], $student['address'], $picturePath]);

        flash('success', 'Student "' . $student['name'] . '" was added successfully.');
        header('Location: view_students.php');
        exit;
    }
    $student['profile_picture'] = '';   // no preview after a failed submit
}

$pageTitle   = 'Add Student';
$pageScripts = ['assets/js/validation.js'];
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h3 class="mb-0"><i class="bi bi-person-plus text-primary"></i> Add Student</h3>
            </div>
            <div class="card-body p-4">
                <?php
                $formAction  = 'add_student.php';
                $submitLabel = 'Add Student';
                $isUpdate    = false;
                require __DIR__ . '/includes/student_form.php';
                ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
