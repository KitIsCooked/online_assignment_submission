<?php
require_once 'config.php';
require_login('student');
$conn = get_db_connection();

// Get assignments
$query = 'SELECT a.*
          FROM assignments a 
          ORDER BY a.created_at DESC';
$result = $conn->query($query);

// Get student's submissions to check if already submitted
$student_id = $_SESSION['user_id'];
$submissions_query = 'SELECT assignment_id FROM submissions WHERE student_id = ?';
$submissions_stmt = $conn->prepare($submissions_query);
$submissions_stmt->bind_param('i', $student_id);
$submissions_stmt->execute();
$submissions_result = $submissions_stmt->get_result();
$submitted_assignments = [];
while ($sub_row = $submissions_result->fetch_assoc()) {
    $submitted_assignments[] = $sub_row['assignment_id'];
}
?>
<?php include 'header.php'; ?>

<style>
.classroom-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.classroom-header {
    margin-bottom: 30px;
}

.classroom-header h2 {
    color: #1a73e8;
    margin-bottom: 10px;
}

.welcome-message {
    color: #5f6368;
    font-size: 16px;
    margin-bottom: 20px;
}

.assignments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.assignment-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
    border: 1px solid #dadce0;
    cursor: pointer;
    transition: box-shadow 0.2s ease-in-out;
    overflow: hidden;
}

.assignment-card:hover {
    box-shadow: 0 1px 3px 0 rgba(60,64,67,0.3), 0 4px 8px 3px rgba(60,64,67,0.15);
}

.assignment-card-header {
    padding: 16px 16px 12px 16px;
    border-bottom: 1px solid #f1f3f4;
}

.assignment-title {
    font-size: 16px;
    font-weight: 500;
    color: #3c4043;
    margin-bottom: 8px;
    line-height: 1.4;
}

.assignment-meta {
    font-size: 14px;
    color: #5f6368;
    margin-bottom: 4px;
}

.assignment-card-body {
    padding: 12px 16px 16px 16px;
}

.assignment-details {
    display: none;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f1f3f4;
}

.assignment-details.show {
    display: block;
}

.assignment-description {
    color: #3c4043;
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 12px;
}

.assignment-due {
    color: #d93025;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 12px;
}

.assignment-pdf {
    margin-bottom: 12px;
}

.pdf-link {
    display: inline-flex;
    align-items: center;
    color: #1a73e8;
    text-decoration: none;
    font-size: 14px;
    padding: 8px 12px;
    border-radius: 4px;
    background: #f8f9fa;
    border: 1px solid #dadce0;
    transition: background-color 0.2s;
}

.pdf-link:hover {
    background: #f1f3f4;
}

.assignment-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-upload {
    background: #1a73e8;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-upload:hover {
    background: #1765cc;
}

.btn-upload.submitted {
    background: #34a853;
}

.btn-upload.submitted:hover {
    background: #2d8e47;
}

.no-assignments {
    text-align: center;
    padding: 40px;
    color: #5f6368;
    font-size: 16px;
}

@media (max-width: 768px) {
    .assignments-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .classroom-container {
        padding: 16px;
    }
}
</style>

<div class="classroom-container">
    <div class="classroom-header">
        <h2>Student Dashboard</h2>
        <p class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Student'); ?>!</p>
    </div>

    <div class="assignments-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php 
                $is_submitted = in_array($row['id'], $submitted_assignments);
                ?>
                <div class="assignment-card" onclick="toggleAssignmentDetails(<?php echo $row['id']; ?>)">
                    <div class="assignment-card-header">
                        <div class="assignment-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="assignment-meta">
                            New assignment posted
                        </div>
                        <div class="assignment-meta">
                            Posted: <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                        </div>
                    </div>
                    <div class="assignment-card-body">
                        <div id="details-<?php echo $row['id']; ?>" class="assignment-details">
                            <?php if ($row['description']): ?>
                                <div class="assignment-description">
                                    <strong>Description:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="assignment-due">
                                <strong>Due Date:</strong> <?php echo date('M j, Y', strtotime($row['due_date'])); ?>
                            </div>
                            
                            <?php if (isset($row['pdf_path']) && $row['pdf_path']): ?>
                                <div class="assignment-pdf">
                                    <a href="<?php echo htmlspecialchars($row['pdf_path']); ?>" target="_blank" class="pdf-link">
                                        📄 View Assignment PDF
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="assignment-actions">
                                <a href="upload_assignment.php?assignment_id=<?php echo $row['id']; ?>" class="btn-upload <?php echo $is_submitted ? 'submitted' : ''; ?>">
                                    <?php echo $is_submitted ? '✓ Submitted' : 'Upload Assignment'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-assignments">
                No assignments available yet. Check back later!
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleAssignmentDetails(assignmentId) {
    const details = document.getElementById('details-' + assignmentId);
    const allDetails = document.querySelectorAll('.assignment-details');
    
    // Close all other details
    allDetails.forEach(detail => {
        if (detail.id !== 'details-' + assignmentId) {
            detail.classList.remove('show');
        }
    });
    
    // Toggle current details
    details.classList.toggle('show');
}

// Prevent event bubbling for action buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-upload') || e.target.classList.contains('pdf-link')) {
        e.stopPropagation();
    }
});
</script>

<?php include 'footer.php'; ?>
