<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Assignment Submission System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="light" id="body">
<header class="main-header">
    <div class="logo-container">
        <img src="assets/images/DonBosco-Color_200px.png" alt="DonBosco Logo" class="logo">
        <span class="site-name">Online Assignment Submission System</span>
    </div>
    <button id="navToggle" class="nav-toggle" aria-label="Toggle navigation">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <nav class="nav-links" id="mainNav">
        <?php if (is_logged_in() && is_student()): ?>
            <a href="student_dashboard.php">Student Dashboard</a>
            <a href="upload_assignment.php">Upload Assignment</a>
            <a href="student_submissions.php">My Submissions</a>
        <?php elseif (is_logged_in() && is_faculty()): ?>
            <a href="faculty_dashboard.php">Faculty Dashboard</a>
            <a href="add_assignment.php">Create Assignment</a>
            <a href="faculty_submissions.php">View Submissions</a>
        <?php endif; ?>
        <?php if (is_logged_in()): ?>
            <a href="change_password.php">Change Password</a>
            <a href="logout.php">Logout</a>
        <?php endif; ?>
        <button id="themeToggle" class="theme-toggle">🌙</button>
    </nav>
</header>
<main class="container">

<script>
// Mobile navigation toggle
document.addEventListener('DOMContentLoaded', function () {
    const navToggle = document.getElementById('navToggle');
    const mainNav = document.getElementById('mainNav');
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function () {
            mainNav.classList.toggle('open');
        });
    }
});
</script>
