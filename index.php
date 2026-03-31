<?php
require_once 'config.php';
if (is_logged_in()) {
    if (is_student()) {
        header('Location: student_dashboard.php');
        exit;
    }
    if (is_faculty()) {
        header('Location: faculty_dashboard.php');
        exit;
    }
}
?>
<?php include 'header.php'; ?>
<section class="home">
    <h1>Welcome to Online Assignment Submission System</h1>
    <p>Please choose your portal:</p>
    <div class="card-grid">
        <div class="card">
            <h2>Student Login</h2>
            <p>Submit assignments and view feedback.</p>
            <a class="btn" href="student_login.php">Student Login</a>
            <a class="btn-outline" href="student_register.php">Student Register</a>
        </div>
        <div class="card">
            <h2>Faculty Login</h2>
            <p>Review submissions, grade and give feedback.</p>
            <a class="btn" href="faculty_login.php">Faculty Login</a>
        </div>
    </div>
</section>
<?php include 'footer.php'; ?>
