<?php
require_once 'config.php';
require_login('student');
$conn = get_db_connection();
$student_id = $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT s.*, a.title, a.due_date FROM submissions s JOIN assignments a ON s.assignment_id = a.id WHERE s.student_id = ? ORDER BY s.submitted_at DESC');
$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php include 'header.php'; ?>
<h2>My Submissions</h2>
<div class="table-wrapper">
<table id="submissionsTable" class="display">
    <thead>
        <tr>
            <th>Assignment</th>
            <th>Due Date</th>
            <th>Submitted At</th>
            <th>Late Status</th>
            <th>Status</th>
            <th>Marks</th>
            <th>Feedback</th>
            <th>File</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): 
            $is_late = is_submission_late($row['submitted_at'], $row['due_date']);
            $late_status = get_late_submission_status($row['submitted_at'], $row['due_date']);
        ?>
            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
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
                <td><a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="btn-outline">Download</a></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>
<script>
$(document).ready(function () {
    $('#submissionsTable').DataTable();
});
</script>
<?php include 'footer.php'; ?>
