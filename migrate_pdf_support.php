<?php
require_once 'config.php';
$conn = get_db_connection();

echo "<h2>Database Migration: Add PDF Support for Assignments</h2>";
echo "<p style='color: #666; font-size: 14px;'>This migration adds support for PDF attachments to assignments.</p>";

$success = true;
$messages = [];

// Check if pdf_path column exists in assignments table
$check_pdf_column = $conn->query("SHOW COLUMNS FROM assignments LIKE 'pdf_path'");
if ($check_pdf_column->num_rows == 0) {
    // Add pdf_path column
    $alter_query = "ALTER TABLE assignments ADD COLUMN pdf_path VARCHAR(255) NULL AFTER description";
    if ($conn->query($alter_query)) {
        $messages[] = "✓ Added 'pdf_path' column to assignments table";
    } else {
        $messages[] = "✗ Failed to add 'pdf_path' column: " . $conn->error;
        $success = false;
    }
} else {
    $messages[] = "ℹ 'pdf_path' column already exists";
}

// Display results
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
foreach ($messages as $message) {
    $color = strpos($message, '✓') !== false ? 'green' : (strpos($message, 'ℹ') !== false ? 'blue' : 'red');
    echo "<p style='color: $color; margin: 5px 0;'>$message</p>";
}
echo "</div>";

if ($success) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3 style='color: #155724; margin-top: 0;'>✓ Migration Successful!</h3>";
    echo "<p style='color: #155724;'>Your database has been updated for PDF attachments.</p>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol style='color: #155724;'>";
    echo "<li><a href='add_assignment.php' style='color: #007bff;'>Create Assignment with PDF</a></li>";
    echo "<li><a href='student_dashboard.php' style='color: #007bff;'>View Student Dashboard</a></li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3 style='color: #721c24; margin-top: 0;'>✗ Migration Failed</h3>";
    echo "<p style='color: #721c24;'>Please check the error messages above and try again.</p>";
    echo "</div>";
}

echo "<p style='margin-top: 20px;'><a href='index.php' style='color: #007bff; text-decoration: none;'>← Back to Home</a></p>";
?>
