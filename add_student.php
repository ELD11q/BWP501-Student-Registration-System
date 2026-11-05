<?php
require "db.php";
checkLogin();

$name = $email = $phone = $address = "";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $phone = trim($_POST['phone'] ?? "");
    $address = trim($_POST['address'] ?? "");

    $errors = validateStudent($conn, $_POST);
    $picture = null;

    if (empty($errors)) {
        $upload = uploadPicture();
        if ($upload['error']) {
            $errors['picture'] = $upload['error'];
        } else {
            $picture = $upload['path'];
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO students (name, email, phone, address, profile_picture) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $address, $picture]);

        $_SESSION['message'] = "Student added successfully";
        header("Location: view_students.php");
        exit;
    }
}

$title = "Add Student";
include "header.php";
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h3 class="mb-0">Add Student</h3></div>
            <div class="card-body">
                <form id="studentForm" action="add_student.php" method="POST" enctype="multipart/form-data" novalidate>
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
                            <label class="form-label">Profile Picture (optional)</label>
                            <input type="file" name="picture" id="picture" class="form-control <?= isset($errors['picture']) ? 'is-invalid' : '' ?>" accept="image/*">
                            <div class="invalid-feedback"><?= $errors['picture'] ?? "" ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" rows="3" class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>"><?= clean($address) ?></textarea>
                            <div class="invalid-feedback" id="address-error"><?= $errors['address'] ?? "" ?></div>
                        </div>
                        <div class="col-12">
                            <img id="preview" class="student-photo-lg d-none" src="" alt="preview">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Add Student</button>
                    <a href="view_students.php" class="btn btn-secondary mt-3">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
