<!DOCTYPE html>
<html>
<head>
    <title>Test Late Submission Policy</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; color: #856404; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .late { background-color: #fff3e0; color: #f57c00; font-weight: bold; }
        .on-time { background-color: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
    <h1>Late Submission Policy Test</h1>
    
    <div class="test-section info">
        <h3>Test Instructions:</h3>
        <ol>
            <li>First, run the database migration: <a href="migrate_late_submission.php">Migrate Database</a></li>
            <li>Create an assignment with a past due date to test late submissions</li>
            <li>Submit an assignment after the due date to verify late flagging</li>
            <li>Check faculty and student submission views to see late status</li>
        </ol>
    </div>
    
    <div class="test-section">
        <h3>Current Submissions Test:</h3>
        <?php
        require_once 'config.php';
        $conn = get_db_connection();
        
        // Test the late submission functions
        $query = 'SELECT s.*, a.title, a.due_date, u.name AS student_name
                  FROM submissions s
                  JOIN assignments a ON s.assignment_id = a.id
                  JOIN users u ON s.student_id = u.id
                  ORDER BY s.submitted_at DESC LIMIT 10';
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>Assignment</th><th>Student</th><th>Due Date</th><th>Submitted At</th><th>Late Status</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                $is_late = is_submission_late($row['submitted_at'], $row['due_date']);
                $late_status = get_late_submission_status($row['submitted_at'], $row['due_date']);
                $css_class = $is_late ? 'late' : 'on-time';
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['due_date']) . "</td>";
                echo "<td>" . htmlspecialchars($row['submitted_at']) . "</td>";
                echo "<td class='$css_class'>" . $late_status . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warning'>No submissions found to test. Please create some test submissions first.</p>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h3>Function Test Results:</h3>
        <?php
        // Test the functions with sample dates
        $test_cases = [
            ['submitted' => '2024-01-15 10:00:00', 'due' => '2024-01-14', 'expected' => 'Late'],
            ['submitted' => '2024-01-13 23:59:59', 'due' => '2024-01-14', 'expected' => 'On Time'],
            ['submitted' => '2024-01-14 23:59:59', 'due' => '2024-01-14', 'expected' => 'On Time'],
            ['submitted' => '2024-01-15 00:00:01', 'due' => '2024-01-14', 'expected' => 'Late'],
        ];
        
        echo "<table>";
        echo "<tr><th>Submitted At</th><th>Due Date</th><th>Expected</th><th>Actual</th><th>Test Result</th></tr>";
        
        foreach ($test_cases as $test) {
            $actual = get_late_submission_status($test['submitted'], $test['due']);
            $passed = $actual === $test['expected'];
            $result_class = $passed ? 'success' : 'error';
            $result_text = $passed ? 'PASS' : 'FAIL';
            
            echo "<tr>";
            echo "<td>" . $test['submitted'] . "</td>";
            echo "<td>" . $test['due'] . "</td>";
            echo "<td>" . $test['expected'] . "</td>";
            echo "<td>" . $actual . "</td>";
            echo "<td class='$result_class'>" . $result_text . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        ?>
    </div>
    
    <div class="test-section">
        <h3>Quick Links:</h3>
        <ul>
            <li><a href="migrate_late_submission.php">Run Database Migration</a></li>
            <li><a href="faculty_submissions.php">Faculty Submissions View</a></li>
            <li><a href="student_submissions.php">Student Submissions View</a></li>
            <li><a href="upload_assignment.php">Upload Assignment (with late warning)</a></li>
            <li><a href="add_assignment.php">Create Assignment</a></li>
        </ul>
    </div>
</body>
</html>
