<?php
/* Common page header + navbar for the PHP pages. Set $pageTitle before including. */
$pageTitle = $pageTitle ?? 'Student Registration System';
$current   = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Student Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.html"><i class="bi bi-mortarboard-fill"></i> SRS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= $current === 'add_student.php' ? 'active' : '' ?>" href="add_student.php">Add Student</a></li>
                <li class="nav-item"><a class="nav-link <?= $current === 'view_students.php' ? 'active' : '' ?>" href="view_students.php">View Students</a></li>
            </ul>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <span class="navbar-text text-white me-3"><i class="bi bi-person-circle"></i> <?= e($_SESSION['username']) ?></span>
                <a class="btn btn-outline-light btn-sm" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
            <?php else: ?>
                <a class="btn btn-outline-light btn-sm me-2" href="login.html">Login</a>
                <a class="btn btn-light btn-sm" href="register.html">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="container py-4 flex-grow-1">
    <?= show_flash() ?>
