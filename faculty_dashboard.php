<?php
require_once 'config.php';
require_login('faculty');
$conn = get_db_connection();

$stats = [
    'total_submissions' => 0,
    'pending' => 0,
    'reviewed' => 0,
    'total_assignments' => 0,
];
$res = $conn->query("SELECT COUNT(*) AS c FROM submissions");
if ($row = $res->fetch_assoc()) $stats['total_submissions'] = $row['c'];
$res = $conn->query("SELECT COUNT(*) AS c FROM submissions WHERE status='Pending'");
if ($row = $res->fetch_assoc()) $stats['pending'] = $row['c'];
$res = $conn->query("SELECT COUNT(*) AS c FROM submissions WHERE status='Reviewed'");
if ($row = $res->fetch_assoc()) $stats['reviewed'] = $row['c'];
$res = $conn->query("SELECT COUNT(*) AS c FROM assignments");
if ($row = $res->fetch_assoc()) $stats['total_assignments'] = $row['c'];
?>
<?php include 'header.php'; ?>
<h2>Faculty Dashboard</h2>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Faculty'); ?>!</p>
<p>Use this dashboard to create assignments and review submissions.</p>

<p>
    <a href="add_assignment.php" class="btn">Create New Assignment</a>
    <a href="faculty_submissions.php" class="btn-outline">View Submissions</a>
    </p>

<div class="card-grid">
    <div class="card">
        <h3>Total Submissions</h3>
        <p style="font-size:2rem; font-weight:bold;"><?php echo $stats['total_submissions']; ?></p>
    </div>
    <div class="card">
        <h3>Pending Review</h3>
        <p style="font-size:2rem; font-weight:bold;"><?php echo $stats['pending']; ?></p>
    </div>
    <div class="card">
        <h3>Reviewed</h3>
        <p style="font-size:2rem; font-weight:bold;"><?php echo $stats['reviewed']; ?></p>
    </div>
    <div class="card">
        <h3>Total Assignments</h3>
        <p style="font-size:2rem; font-weight:bold;"><?php echo $stats['total_assignments']; ?></p>
    </div>
</div>
<?php include 'footer.php'; ?>
