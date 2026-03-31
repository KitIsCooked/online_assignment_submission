<?php
// Database configuration
$DB_HOST = 'localhost';
$DB_PORT = '3306';
$DB_USER = 'root'; // change if needed
$DB_PASS = '';    // change if you have a password
$DB_NAME = 'assignment_portal';

// Base URL (optional, can be adjusted if you use a subfolder)
$BASE_URL = '';

session_start();

function is_submission_late($submitted_at, $due_date) {
    $submission_date = new DateTime($submitted_at);
    $due_datetime = new DateTime($due_date . ' 23:59:59');
    return $submission_date > $due_datetime;
}

function get_late_submission_status($submitted_at, $due_date) {
    if (is_submission_late($submitted_at, $due_date)) {
        return 'Late';
    }
    return 'On Time';
}

function auto_flag_late_submissions($conn) {
    // Update all submissions to flag late ones
    $query = "UPDATE submissions s 
              JOIN assignments a ON s.assignment_id = a.id 
              SET s.is_late = CASE 
                  WHEN s.submitted_at > CONCAT(a.due_date, ' 23:59:59') THEN 1 
                  ELSE 0 
              END";
    return $conn->query($query);
}

function get_db_connection() {
    global $DB_HOST, $DB_PORT, $DB_USER, $DB_PASS, $DB_NAME;
    // mysqli(host, username, password, dbname, port)
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }
    return $conn;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_student() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

function is_faculty() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'faculty';
}

function require_login($role = null) {
    // Prevent caching of protected pages so back/forward always re-checks login
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
    if ($role !== null && (!isset($_SESSION['role']) || $_SESSION['role'] !== $role)) {
        header('Location: index.php');
        exit;
    }
}
