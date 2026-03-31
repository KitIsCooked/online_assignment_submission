<?php
require_once 'config.php';
require_login('faculty');
$conn = get_db_connection();
$message = '';

$assignment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($assignment_id <= 0) {
    die('Invalid assignment ID.');
}

// Load assignment
$stmt = $conn->prepare('SELECT id, title, description, due_date, pdf_path FROM assignments WHERE id = ?');
$stmt->bind_param('i', $assignment_id);
$stmt->execute();
$res = $stmt->get_result();
$assignment = $res->fetch_assoc();

if (!$assignment) {
    die('Assignment not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $pdf_path = $assignment['pdf_path']; // Keep existing PDF

    if ($title === '' || $due_date === '') {
        $message = 'Title and Due Date are required.';
    } else {
        // Handle new PDF upload if provided
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['pdf'];
            $ext = strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $uploadDir = 'uploads/assignments/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $newName = 'assignment_' . $assignment_id . '_' . time() . '.' . $ext;
                $dest = $uploadDir . $newName;
                if (move_uploaded_file($_FILES['pdf']['tmp_name'], $dest)) {
                    $pdf_path = $dest;
                }
            }
        }

        // Check if pdf_path column exists
        $pdf_check = $conn->query("SHOW COLUMNS FROM assignments LIKE 'pdf_path'");
        $has_pdf_path = $pdf_check->num_rows > 0;

        if ($has_pdf_path) {
            $update = $conn->prepare('UPDATE assignments SET title = ?, description = ?, due_date = ?, pdf_path = ? WHERE id = ?');
            $update->bind_param('ssssi', $title, $description, $due_date, $pdf_path, $assignment_id);
        } else {
            $update = $conn->prepare('UPDATE assignments SET title = ?, description = ?, due_date = ? WHERE id = ?');
            $update->bind_param('sssi', $title, $description, $due_date, $assignment_id);
        }
        
        if ($update->execute()) {
            $message = 'success';
        } else {
            $message = 'Failed to update assignment.';
        }
    }
}
?>
<?php include 'header.php'; ?>
<h2>Edit Assignment</h2>
<div class="form-card">
    <form method="post" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($assignment['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($assignment['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="due_date">Due Date</label>
            <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($assignment['due_date']); ?>" required>
        </div>
        <div class="form-group">
            <label for="pdf">Assignment PDF (Optional - Leave empty to keep current)</label>
            <input type="file" id="pdf" name="pdf" accept=".pdf">
            <?php if (isset($assignment['pdf_path']) && $assignment['pdf_path']): ?>
                <p style="margin: 5px 0; font-size: 14px; color: #666;">
                    Current PDF: <a href="<?php echo htmlspecialchars($assignment['pdf_path']); ?>" target="_blank" style="color: #007bff;">View Current PDF</a>
                </p>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn">Save Changes</button>
        <a href="add_assignment.php" class="btn-outline">Back</a>
    </form>
</div>
<script>
<?php if ($message === 'success'): ?>
Swal.fire({
    icon: 'success',
    title: 'Updated',
    text: 'Assignment has been updated.',
}).then(() => {
    window.location.href = 'add_assignment.php';
});
<?php elseif ($message): ?>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '<?php echo htmlspecialchars($message); ?>',
});
<?php endif; ?>
</script>
<?php include 'footer.php'; ?>
