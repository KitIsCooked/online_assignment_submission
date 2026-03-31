<?php
require_once 'config.php';
require_login('faculty');
$conn = get_db_connection();

$query = 'SELECT s.*, a.title AS assignment_title, a.due_date, u.name AS student_name, u.email AS student_email
          FROM submissions s
          JOIN assignments a ON s.assignment_id = a.id
          JOIN users u ON s.student_id = u.id
          ORDER BY s.submitted_at DESC';
$result = $conn->query($query);
?>
<?php include 'header.php'; ?>
<h2>All Submissions</h2>
<div class="table-wrapper">
<table id="allSubmissionsTable" class="display">
    <thead>
        <tr>
            <th>Assignment</th>
            <th>Student</th>
            <th>Email</th>
            <th>Due Date</th>
            <th>Submitted At</th>
            <th>Late Status</th>
            <th>Status</th>
            <th>Marks</th>
            <th>Feedback</th>
            <th>File</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): 
            $is_late = is_submission_late($row['submitted_at'], $row['due_date']);
            $late_status = get_late_submission_status($row['submitted_at'], $row['due_date']);
        ?>
            <tr>
                <td><?php echo htmlspecialchars($row['assignment_title']); ?></td>
                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                <td><?php echo htmlspecialchars($row['student_email']); ?></td>
                <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                <td><?php echo htmlspecialchars($row['submitted_at']); ?></td>
                <td>
                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $late_status)); ?>">
                        <?php echo $late_status; ?>
                    </span>
                </td>
                <td>
                    <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                        <?php echo htmlspecialchars($row['status']); ?>
                    </span>
                </td>
                <td><?php echo $row['marks'] !== null ? (int)$row['marks'] : '-'; ?></td>
                <td><?php echo $row['feedback'] ? htmlspecialchars($row['feedback']) : '-'; ?></td>
                <td><a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="btn-outline">View</a></td>
                <td>
                    <button class="btn" onclick="openUpdateModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['marks'] ?? '', ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['feedback'] ?? '', ENT_QUOTES); ?>', '<?php echo $row['status']; ?>')">Update</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>

<script>
$(document).ready(function () {
    $('#allSubmissionsTable').DataTable();
});

function openUpdateModal(id, marks, feedback, status) {
    Swal.fire({
        title: 'Update Submission',
        html: `
            <input id="swal-marks" class="swal2-input" placeholder="Marks" value="${marks}">
            <textarea id="swal-feedback" class="swal2-textarea" placeholder="Feedback">${feedback}</textarea>
            <select id="swal-status" class="swal2-select">
                <option value="Pending" ${status === 'Pending' ? 'selected' : ''}>Pending</option>
                <option value="Reviewed" ${status === 'Reviewed' ? 'selected' : ''}>Reviewed</option>
            </select>
        `,
        focusConfirm: false,
        preConfirm: () => {
            return {
                marks: document.getElementById('swal-marks').value,
                feedback: document.getElementById('swal-feedback').value,
                status: document.getElementById('swal-status').value
            };
        },
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('faculty_update_submission.php', {
                id: id,
                marks: result.value.marks,
                feedback: result.value.feedback,
                status: result.value.status
            }, function (response) {
                try {
                    const data = JSON.parse(response);
                    if (data.success) {
                        Swal.fire('Saved!', 'Submission updated.', 'success').then(() => window.location.reload());
                    } else {
                        Swal.fire('Error', data.message || 'Update failed.', 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Unexpected server response.', 'error');
                }
            });
        }
    });
}
</script>
<?php include 'footer.php'; ?>
