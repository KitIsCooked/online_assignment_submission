<?php
require_once 'config.php';
require_login('student');
$conn = get_db_connection();

// get assignments for info
$result = $conn->query('SELECT * FROM assignments ORDER BY due_date ASC');
?>
<?php include 'header.php'; ?>
<h2>Student Dashboard</h2>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Student'); ?>!</p>
<p>Use the navigation menu to upload assignments and view your submission status.</p>

<h3>Available Assignments</h3>
<div class="table-wrapper">
<table id="assignmentsTable" class="display">
    <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Due Date</th>
            <th>Upload</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                <td><a href="upload_assignment.php?assignment_id=<?php echo $row['id']; ?>" class="btn">Upload</a></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>
<script>
$(document).ready(function () {
    $('#assignmentsTable').DataTable();
});
</script>
<?php include 'footer.php'; ?>
