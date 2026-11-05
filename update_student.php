<?php
require "db.php";
checkLogin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: view_students.php");
    exit;
}

$name = $student['name'];
$email = $student['email'];
$phone = $student['phone'];
$address = $student['address'];
$picture = $student['profile_picture'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $phone = trim($_POST['phone'] ?? "");
    $address = trim($_POST['address'] ?? "");

    $errors = validateStudent($conn, $_POST, $id);

    if (empty($errors)) {
        $upload = uploadPicture();
        if ($upload['error']) {
            $errors['picture'] = $upload['error'];
        } elseif ($upload['path']) {
            deletePicture($picture);
            $picture = $upload['path'];
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, phone = ?, address = ?, profile_picture = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $address, $picture, $id]);

        $_SESSION['message'] = "Student updated successfully";
        header("Location: view_students.php");
        exit;
    }
}

$title = "Update Student";
include "header.php";
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h3 class="mb-0">Update Student</h3></div>
            <div class="card-body">
                <form id="studentForm" action="update_student.php" method="POST" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= clean($name) ?>">
                            <div class="invalid-feedback" id="name-error"><?= $errors['name'] ?? "" ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= clean($email) ?>">
                            <div class="invalid-feedback" id="email-error"><?= $errors['email'] ?? "" ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" value="<?= clean($phone) ?>">
                            <div class="invalid-feedback" id="phone-error"><?= $errors['phone'] ?? "" ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Change Picture (optional)</label>
                            <input type="file" name="picture" id="picture" class="form-control <?= isset($errors['picture']) ? 'is-invalid' : '' ?>" accept="image/*">
                            <div class="invalid-feedback"><?= $errors['picture'] ?? "" ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" rows="3" class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>"><?= clean($address) ?></textarea>
                            <div class="invalid-feedback" id="address-error"><?= $errors['address'] ?? "" ?></div>
                        </div>
                        <div class="col-12">
                            <img id="preview" class="student-photo-lg <?= $picture ? '' : 'd-none' ?>" src="<?= clean($picture) ?>" alt="picture">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
                    <a href="view_students.php" class="btn btn-secondary mt-3">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
