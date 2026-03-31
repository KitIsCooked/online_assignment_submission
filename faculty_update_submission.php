<?php
require_once 'config.php';
require_login('faculty');
$conn = get_db_connection();

$id = intval($_POST['id'] ?? 0);
$marks = trim($_POST['marks'] ?? '');
$feedback = trim($_POST['feedback'] ?? '');
$status = $_POST['status'] ?? 'Pending';

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$marksVal = ($marks === '') ? null : intval($marks);

$stmt = $conn->prepare('UPDATE submissions SET marks = ?, feedback = ?, status = ? WHERE id = ?');
$stmt->bind_param('issi', $marksVal, $feedback, $status, $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
