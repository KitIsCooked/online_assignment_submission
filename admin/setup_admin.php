<?php
require_once '../config.php';
$conn = get_db_connection();

echo "<h2>Database Migration: Add Admin Table</h2>";
echo "<p style='color: #666; font-size: 14px;'>This migration adds admin user table for secure admin access.</p>";

$success = true;
$messages = [];

// Create admin table
$create_admin = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($create_admin)) {
    $messages[] = "✓ Created 'admin_users' table";
} else {
    $messages[] = "✗ Failed to create 'admin_users' table: " . $conn->error;
    $success = false;
}

// Insert default admin user
$check_admin = "SELECT id FROM admin_users WHERE username = 'admin'";
$admin_result = $conn->query($check_admin);

if ($admin_result->num_rows === 0) {
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_admin = "INSERT INTO admin_users (username, password) VALUES ('admin', ?)";
    $stmt = $conn->prepare($insert_admin);
    $stmt->bind_param('s', $default_password);
    
    if ($stmt->execute()) {
        $messages[] = "✓ Created default admin user (username: admin, password: admin123)";
    } else {
        $messages[] = "✗ Failed to create default admin user: " . $stmt->error;
        $success = false;
    }
} else {
    $messages[] = "ℹ Admin user already exists";
}

// Display results
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
foreach ($messages as $message) {
    $color = strpos($message, '✓') !== false ? 'green' : (strpos($message, '✗') !== false ? 'red' : 'blue');
    echo "<p style='color: $color; margin: 5px 0;'>$message</p>";
}
echo "</div>";

if ($success) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3 style='color: #155724; margin-top: 0;'>✓ Migration Successful!</h3>";
    echo "<p style='color: #155724;'>Admin database has been set up successfully.</p>";
    echo "<p><strong>Default Admin Credentials:</strong></p>";
    echo "<ul style='color: #155724;'>";
    echo "<li>Username: admin</li>";
    echo "<li>Password: admin123</li>";
    echo "</ul>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol style='color: #155724;'>";
    echo "<li><a href='index.php' style='color: #007bff;'>Access Admin Panel</a></li>";
    echo "<li>Change default password after first login</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3 style='color: #721c24; margin-top: 0;'>✗ Migration Failed</h3>";
    echo "<p style='color: #721c24;'>Please check error messages above and try again.</p>";
    echo "</div>";
}

echo "<p style='margin-top: 20px;'><a href='../index.php' style='color: #007bff; text-decoration: none;'>← Back to Main Site</a></p>";
?>
