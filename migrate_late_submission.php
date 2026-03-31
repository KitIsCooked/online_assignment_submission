<?php
require_once 'config.php';
$conn = get_db_connection();

echo "<h2>Database Migration: Add Late Submission Feature</h2>";

// Check if is_late column exists
$check_column = $conn->query("SHOW COLUMNS FROM submissions LIKE 'is_late'");
if ($check_column->num_rows == 0) {
    // Add is_late column
    $alter_query = "ALTER TABLE submissions ADD COLUMN is_late TINYINT(1) DEFAULT 0 AFTER status";
    if ($conn->query($alter_query)) {
        echo "<p style='color: green;'>✓ Added 'is_late' column to submissions table</p>";
        
        // Auto-flag existing submissions
        if (auto_flag_late_submissions($conn)) {
            echo "<p style='color: green;'>✓ Auto-flagged existing submissions as late/on-time</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Could not auto-flag existing submissions</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Failed to add 'is_late' column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ 'is_late' column already exists</p>";
    
    // Update existing submissions
    if (auto_flag_late_submissions($conn)) {
        echo "<p style='color: green;'>✓ Updated existing submissions with late status</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Could not update existing submissions</p>";
    }
}

echo "<p><a href='index.php'>Back to Home</a></p>";
?>
