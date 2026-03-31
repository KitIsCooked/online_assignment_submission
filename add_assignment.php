<?php
require_once 'config.php';
require_login('faculty');
$conn = get_db_connection();
$message = '';

$delete_message = '';

// Handle delete assignment
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    if ($delete_id > 0) {
        // Optional: prevent delete if submissions exist; for now we just delete
        $del = $conn->prepare('DELETE FROM assignments WHERE id = ?');
        $del->bind_param('i', $delete_id);
        if ($del->execute()) {
            $delete_message = 'deleted';
        } else {
            $delete_message = 'Failed to delete assignment.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $pdf_path = '';

    if ($title === '' || $due_date === '') {
        $message = 'Title and Due Date are required.';
    } else {
        // Handle PDF upload if provided
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['pdf'];
            $ext = strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $uploadDir = 'uploads/assignments/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $newName = 'assignment_' . time() . '.' . $ext;
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
            $stmt = $conn->prepare('INSERT INTO assignments (title, description, due_date, pdf_path) VALUES (?,?,?,?)');
            $stmt->bind_param('ssss', $title, $description, $due_date, $pdf_path);
        } else {
            $stmt = $conn->prepare('INSERT INTO assignments (title, description, due_date) VALUES (?,?,?)');
            $stmt->bind_param('sss', $title, $description, $due_date);
        }
        
        if ($stmt->execute()) {
            $message = 'success';
        } else {
            $message = 'Failed to create assignment.';
        }
    }
}

// Load assignments for table
$assignments_list = $conn->query('SELECT id, title, description, due_date, created_at, pdf_path FROM assignments ORDER BY created_at DESC');
?>
<?php include 'header.php'; ?>
<h2>Create Assignment</h2>
<div class="form-card">
    <form method="post" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        <div class="form-group">
            <label for="due_date">Due Date</label>
            <input type="date" id="due_date" name="due_date" required>
        </div>
        <div class="form-group">
            <label for="pdf">Assignment PDF (Optional)</label>
            <input type="file" id="pdf" name="pdf" accept=".pdf">
        </div>
        <button type="submit" class="btn">Save Assignment</button>
    </form>
</div>
<h3 style="margin-top: 2rem;">All Assignments</h3>
<div class="table-wrapper" style="margin-top: 0.5rem;">
<table id="assignmentsTableFaculty" class="display">
    <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Due Date</th>
            <th>Created At</th>
            <th>PDF</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $assignments_list->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                <td>
                    <?php if (isset($row['pdf_path']) && $row['pdf_path']): ?>
                        <a href="<?php echo htmlspecialchars($row['pdf_path']); ?>" target="_blank" class="btn-outline">View PDF</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit_assignment.php?id=<?php echo (int)$row['id']; ?>" class="btn-outline">Edit</a>
                    <a href="#" class="btn-outline delete-assignment" data-id="<?php echo (int)$row['id']; ?>">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>

<script>
// Initialize DataTables for faculty assignments and bind delete actions
$(document).ready(function () {
    $('#assignmentsTableFaculty').DataTable();

    $('.delete-assignment').on('click', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        if (!id) return;

        Swal.fire({
            title: 'Delete this assignment?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'add_assignment.php?delete=' + encodeURIComponent(id);
            }
        });
    });
});

<?php if ($message === 'success'): ?>
Swal.fire({
    icon: 'success',
    title: 'Created',
    text: 'Assignment has been created.',
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

<?php if ($delete_message === 'deleted'): ?>
Swal.fire({
    icon: 'success',
    title: 'Deleted',
    text: 'Assignment has been deleted.',
}).then(() => {
    window.location.href = 'add_assignment.php';
});
<?php elseif ($delete_message && $delete_message !== 'deleted'): ?>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '<?php echo htmlspecialchars($delete_message); ?>',
});
<?php endif; ?>
</script>
<?php include 'footer.php'; ?>
