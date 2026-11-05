<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? "Student Registration System" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.html">Student System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="add_student.php">Add Student</a></li>
                <li class="nav-item"><a class="nav-link" href="view_students.php">View Students</a></li>
            </ul>
            <span class="navbar-text text-white me-3">Hello, <?= clean($_SESSION['username'] ?? "") ?></span>
            <a class="btn btn-light btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>
<div class="container py-4 flex-grow-1">
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success"><?= clean($_SESSION['message']) ?></div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>
