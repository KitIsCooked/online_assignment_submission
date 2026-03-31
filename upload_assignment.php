<?php
require_once 'config.php';
require_login('student');
$conn = get_db_connection();
$message = '';

$student_id = $_SESSION['user_id'];

$preselected_assignment_id = isset($_GET['assignment_id']) ? intval($_GET['assignment_id']) : 0;

// Get current student's submissions
$subs_stmt = $conn->prepare('SELECT assignment_id FROM submissions WHERE student_id = ?');
$subs_stmt->bind_param('i', $student_id);
$subs_stmt->execute();
$subs_result = $subs_stmt->get_result();

// Create array of submitted assignment IDs
$submitted_assignments = [];
while ($sub = $subs_result->fetch_assoc()) {
    $submitted_assignments[] = $sub['assignment_id'];
}

// Get assignments for dropdown
$assignments = $conn->query('SELECT id, title, due_date FROM assignments ORDER BY created_at DESC');

// Handle delete submission action
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    if ($delete_id > 0) {
        $stmt = $conn->prepare('SELECT file_path FROM submissions WHERE id = ? AND student_id = ?');
        $stmt->bind_param('ii', $delete_id, $student_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $filePath = $row['file_path'];
            if ($filePath && file_exists($filePath)) {
                @unlink($filePath);
            }
            $del = $conn->prepare('DELETE FROM submissions WHERE id = ? AND student_id = ?');
            $del->bind_param('ii', $delete_id, $student_id);
            if ($del->execute()) {
                $message = 'deleted';
            } else {
                $message = 'Failed to delete submission.';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = intval($_POST['assignment_id'] ?? 0);

    if ($assignment_id <= 0 || !isset($_FILES['file'])) {
        $message = 'Please select an assignment and choose a file.';
    } else {
        // Check if student has already submitted this assignment
        $check_stmt = $conn->prepare('SELECT id FROM submissions WHERE student_id = ? AND assignment_id = ?');
        $check_stmt->bind_param('ii', $student_id, $assignment_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $message = 'You have already submitted this assignment. You can delete your previous submission and upload a new one if needed.';
        } else {
            $file = $_FILES['file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $message = 'File upload error.';
            } else {
                $allowed = ['pdf', 'doc', 'docx'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    $message = 'Only PDF, DOC, DOCX files are allowed.';
                } else {
                    $uploadDir = 'uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $newName = 'sub_' . $student_id . '_' . time() . '.' . $ext;
                    $dest = $uploadDir . $newName;
                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        $stmt = $conn->prepare('INSERT INTO submissions (student_id, assignment_id, file_path) VALUES (?,?,?)');
                        $stmt->bind_param('iis', $student_id, $assignment_id, $dest);
                        if ($stmt->execute()) {
                            $message = 'success';
                        } else {
                            $message = 'Database error while saving submission.';
                        }
                    } else {
                        $message = 'Failed to move uploaded file.';
                    }
                }
            }
        }
    }
}

// Get current student's submissions for the table
$subs_table_stmt = $conn->prepare('SELECT s.id, a.title, s.submitted_at, s.file_path FROM submissions s JOIN assignments a ON s.assignment_id = a.id WHERE s.student_id = ? ORDER BY s.submitted_at DESC');
$subs_table_stmt->bind_param('i', $student_id);
$subs_table_stmt->execute();
$subs_table_result = $subs_table_stmt->get_result();
?>
<?php include 'header.php'; ?>
<h2>Upload Assignment</h2>
<div class="form-card">
    <form method="post" action="" enctype="multipart/form-data" id="uploadForm">
        <div class="form-group">
            <label for="assignment_id">Select Assignment</label>
            <select id="assignment_id" name="assignment_id" required>
                <option value="">-- Choose --</option>
                <?php while ($row = $assignments->fetch_assoc()): ?>
                    <?php 
                    $is_submitted = in_array($row['id'], $submitted_assignments);
                    $disabled = $is_submitted ? 'disabled' : '';
                    $submitted_text = $is_submitted ? ' (Already Submitted)' : '';
                    ?>
                    <option value="<?php echo $row['id']; ?>" 
                            data-due-date="<?php echo htmlspecialchars($row['due_date']); ?>"
                            <?php echo $disabled; ?>
                            <?php if ($preselected_assignment_id === (int)$row['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($row['title']); ?> (Due: <?php echo htmlspecialchars($row['due_date']); ?>)<?php echo $submitted_text; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="file">Upload File (PDF/DOC/DOCX)</label>
            <input type="file" id="file" name="file" accept=".pdf,.doc,.docx" required>
        </div>
        <button type="submit" class="btn">Submit</button>
    </form>
</div>

<h3 style="margin-top: 2rem;">Your Submissions</h3>
<div class="table-wrapper" style="margin-top: 0.5rem;">
<table id="mySubmissionsTable" class="display">
    <thead>
        <tr>
            <th>Assignment</th>
            <th>Submitted At</th>
            <th>File</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($sub = $subs_table_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($sub['title']); ?></td>
                <td><?php echo htmlspecialchars($sub['submitted_at']); ?></td>
                <td><a href="<?php echo htmlspecialchars($sub['file_path']); ?>" target="_blank" class="btn-outline">View</a></td>
                <td>
                    <a href="#" class="btn-outline delete-submission" data-id="<?php echo (int)$sub['id']; ?>">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>
<script>
// Initialize DataTables for the submissions table and bind delete actions
$(document).ready(function () {
    $('#mySubmissionsTable').DataTable();

    // Check for late submission when assignment is selected
    $('#assignment_id').on('change', function() {
        const assignmentId = $(this).val();
        if (assignmentId) {
            // Get due date from the dropdown option data attribute
            const dueDate = $(this).find(`option[value="${assignmentId}"]`).data('due-date');
            const currentDate = new Date().toISOString().split('T')[0];
            
            if (dueDate && currentDate > dueDate) {
                Swal.fire({
                    title: 'Late Submission Warning',
                    text: 'This assignment is past its due date. Your submission will be marked as late.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'I Understand'
                });
            }
        }
    });

    $('.delete-submission').on('click', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        if (!id) return;

        Swal.fire({
            title: 'Are you sure?',
            text: 'This will delete your submission.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'upload_assignment.php?delete=' + encodeURIComponent(id);
            }
        });
    });
});

<?php if ($message === 'success'): ?>
Swal.fire({
    icon: 'success',
    title: 'Uploaded!',
    text: 'Your assignment has been submitted.',
}).then(() => {
    window.location.href = 'upload_assignment.php';
});
<?php elseif ($message === 'deleted'): ?>
Swal.fire({
    icon: 'success',
    title: 'Deleted!',
    text: 'Your submission has been deleted.',
}).then(() => {
    window.location.href = 'upload_assignment.php';
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
