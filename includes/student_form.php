<?php
/*
 * Shared student form (used by add_student.php and update_student.php).
 * Expects: $student (array of values), $errors (array), $formAction, $submitLabel, $isUpdate
 */
$val = fn($k) => e($student[$k] ?? '');
$err = fn($k) => isset($errors[$k]) ? ' is-invalid' : '';
?>
<form id="studentForm" action="<?= e($formAction) ?>" method="POST" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <?php if ($isUpdate): ?>
        <input type="hidden" name="id" value="<?= (int)$student['id'] ?>">
    <?php endif; ?>

    <div id="formAlert" class="alert alert-danger <?= empty($errors) ? 'd-none' : '' ?>" role="alert">
        Please fix the highlighted fields.
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control<?= $err('name') ?>" id="name" name="name" maxlength="100" value="<?= $val('name') ?>">
            <div class="invalid-feedback" data-for="name"><?= e($errors['name'] ?? '') ?></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control<?= $err('email') ?>" id="email" name="email" maxlength="100" value="<?= $val('email') ?>">
            <div class="invalid-feedback" data-for="email"><?= e($errors['email'] ?? '') ?></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
            <input type="tel" class="form-control<?= $err('phone') ?>" id="phone" name="phone" maxlength="15" value="<?= $val('phone') ?>" placeholder="+966 5XXXXXXXX">
            <div class="invalid-feedback" data-for="phone"><?= e($errors['phone'] ?? '') ?></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="profile_picture" class="form-label">Profile Picture <span class="text-muted small">(optional, max 2 MB)</span></label>
            <input type="file" class="form-control<?= $err('profile_picture') ?>" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/gif,image/webp">
            <div class="invalid-feedback" data-for="profile_picture"><?= e($errors['profile_picture'] ?? '') ?></div>
        </div>
        <div class="col-12">
            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
            <textarea class="form-control<?= $err('address') ?>" id="address" name="address" rows="3"><?= $val('address') ?></textarea>
            <div class="invalid-feedback" data-for="address"><?= e($errors['address'] ?? '') ?></div>
        </div>

        <div class="col-12 d-flex align-items-center gap-3">
            <img id="picturePreview" class="avatar-lg <?= empty($student['profile_picture']) ? 'd-none' : '' ?>"
                 src="<?= $val('profile_picture') ?>" alt="Profile picture preview">
            <?php if ($isUpdate && !empty($student['profile_picture'])): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remove_picture" name="remove_picture" value="1">
                    <label class="form-check-label" for="remove_picture">Remove current picture</label>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4" id="submitBtn">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            <?= e($submitLabel) ?>
        </button>
        <a href="view_students.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
