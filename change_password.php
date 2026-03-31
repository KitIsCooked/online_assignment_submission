<?php
require_once 'config.php';
require_login(); // Require any logged-in user

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all password fields';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long';
    } else {
        // Verify current password
        $conn = get_db_connection();
        $user_id = $_SESSION['user_id'];
        
        $stmt = $conn->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($current_password, $user['password_hash'])) {
                // Current password is correct, update to new password
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $update_stmt = $conn->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                $update_stmt->bind_param('si', $hashed_new_password, $user_id);
                
                if ($update_stmt->execute()) {
                    $message = 'Password changed successfully!';
                } else {
                    $error = 'Error updating password. Please try again.';
                }
            } else {
                $error = 'Current password is incorrect';
            }
        } else {
            $error = 'User not found';
        }
    }
}
?>
<?php include 'header.php'; ?>

<div class="form-container" style="max-width: 500px; margin: 40px auto;">
    <h2>Change Password</h2>
    <p>Update your account password</p>
    
    <?php if ($message): ?>
        <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form method="post" class="form-card">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" required 
                   placeholder="Enter your current password">
        </div>
        
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required 
                   placeholder="Enter your new password (min 6 characters)">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required 
                   placeholder="Confirm your new password">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn">Change Password</button>
            <a href="<?php echo is_student() ? 'student_dashboard.php' : 'faculty_dashboard.php'; ?>" 
               class="btn-outline">Cancel</a>
        </div>
    </form>
</div>

<style>
.form-container {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #e0e0e0;
}

.form-container h2 {
    color: #333;
    margin-bottom: 10px;
}

.form-container p {
    color: #666;
    margin-bottom: 30px;
}

.form-card {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.form-group input {
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 16px;
    transition: border-color 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #4f46e5;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 10px;
}

.btn {
    background: linear-gradient(90deg, #4f46e5, #0ea5e9);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
    display: inline-block;
}

.btn:hover {
    background: linear-gradient(90deg, #4338ca, #0284c7);
    transform: translateY(-1px);
}

.btn-outline {
    background: transparent;
    color: #4f46e5;
    padding: 12px 24px;
    border: 2px solid #4f46e5;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
    display: inline-block;
}

.btn-outline:hover {
    background: #4f46e5;
    color: white;
    transform: translateY(-1px);
}
</style>

<?php include 'footer.php'; ?>
