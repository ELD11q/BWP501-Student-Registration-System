<?php
/*
 * delete_student.php
 *  GET  ?id=N  -> confirmation page ("Are you sure?")
 *  POST id=N   -> deletes the record (and its picture file)
 * The list page also asks for confirmation with a Bootstrap modal before posting here.
 */
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/student_validation.php';

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    flash('warning', 'Student not found.');
    header('Location: view_students.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $pdo->prepare('DELETE FROM students WHERE id = ?')->execute([$id]);
    delete_picture($student['profile_picture']);

    flash('success', 'Student "' . $student['name'] . '" was deleted.');
    $back = $_POST['return'] ?? '';
    // Only allow returning to the list page (no open redirects)
    header('Location: ' . (strpos($back, 'view_students.php') === 0 ? $back : 'view_students.php'));
    exit;
}

$pageTitle = 'Delete Student';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card border-danger shadow-sm">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Confirm Deletion</h4>
            </div>
            <div class="card-body text-center p-4">
                <?php if ($student['profile_picture']): ?>
                    <img src="<?= e($student['profile_picture']) ?>" class="avatar-lg mb-3" alt="">
                <?php endif; ?>
                <p class="fs-5">Are you sure you want to delete <strong><?= e($student['name']) ?></strong>?</p>
                <p class="text-muted"><?= e($student['email']) ?> &middot; <?= e($student['phone']) ?></p>
                <p class="text-danger small">This action cannot be undone.</p>
                <form method="POST" action="delete_student.php" class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$student['id'] ?>">
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Yes, Delete</button>
                    <a href="view_students.php" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
