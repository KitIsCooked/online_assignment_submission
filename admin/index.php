<?php
require_once '../config.php';

// Check if admin session exists
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Handle admin login form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
        $username = trim($_POST['admin_username'] ?? '');
        $password = trim($_POST['admin_password'] ?? '');
        
        if ($username && $password) {
            $conn = get_db_connection();
            $stmt = $conn->prepare('SELECT id, password FROM admin_users WHERE username = ?');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password'])) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $username;
                    // Redirect to prevent form resubmission
                    header('Location: index.php');
                    exit;
                }
            }
            $login_error = 'Invalid username or password';
        } else {
            $login_error = 'Please enter username and password';
        }
    }
    
    // Show admin login form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login</title>
        <style>
        body { font-family: Arial, sans-serif; background: radial-gradient(circle at top left, #fdf2ff 0, #f5f7ff 40%, #f3f3f3 100%); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .admin-login { background: #fff; padding: 50px; border-radius: 10px; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25); text-align: center; width: 400px; border: 1px solid #dadce0; }
        .admin-login h2 { color: #1f2933; margin-bottom: 30px; font-size: 28px; font-weight: 500; }
        .admin-login .logo { margin-bottom: 30px; }
        .admin-login .logo img { max-width: 150px; height: auto; }
        .admin-login input { width: 100%; padding: 15px; margin: 15px 0; border: 2px solid #dadce0; border-radius: 6px; font-size: 16px; transition: border-color 0.3s; }
        .admin-login input:focus { outline: none; border-color: #4f46e5; }
        .admin-login button { width: 100%; padding: 15px; background: linear-gradient(90deg, #4f46e5, #0ea5e9); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 500; transition: all 0.2s; }
        .admin-login button:hover { background: linear-gradient(90deg, #4338ca, #0284c7); transform: translateY(-1px); }
        .error { color: #ea4335; margin: 15px 0; padding: 10px; background: #fce8e6; border-radius: 6px; border-left: 4px solid #ea4335; }
        .info { color: #5f6368; margin-top: 20px; font-size: 14px; }
        .info strong { color: #1f2933; }
    </style>
    </head>
    <body>
        <div class="admin-login">
            <div class="logo">
                <img src="../assets/images/DonBosco-Color_200px.png" alt="Admin Logo">
            </div>
            <h2>🔐 Admin Login</h2>
            
            <?php if (isset($login_error)): ?>
                <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            
            <form method="post">
                <input type="hidden" name="admin_login" value="1">
                <input type="text" name="admin_username" placeholder="Admin Username" required>
                <input type="password" name="admin_password" placeholder="Admin Password" required>
                <button type="submit">Login to Admin Panel</button>
            </form>
            
            <!-- <div class="info">
                <strong>Default Credentials:</strong><br>
                Username: admin<br>
                Password: admin123
            </div> -->
            
            <div style="margin-top: 30px;">
                <a href="../index.php" style="color: #667eea; text-decoration: none;">← Back to Main Site</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Get database connection
$conn = get_db_connection();

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_faculty':
                $name = trim($_POST['name']);
                $email = trim($_POST['email']);
                $password = trim($_POST['password']);
                
                if ($name && $email && $password) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,"faculty")');
                    $stmt->bind_param('sss', $name, $email, $hashed_password);
                    if ($stmt->execute()) {
                        $message = 'Faculty added successfully!';
                    } else {
                        $message = 'Error adding faculty.';
                    }
                }
                break;
                
            case 'delete_user':
                $user_id = intval($_POST['user_id']);
                $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
                $stmt->bind_param('i', $user_id);
                if ($stmt->execute()) {
                    $message = 'User deleted successfully!';
                } else {
                    $message = 'Error deleting user.';
                }
                break;
                
            case 'change_admin_password':
                $new_password = trim($_POST['new_password']);
                $confirm_password = trim($_POST['confirm_password']);
                
                if (empty($new_password) || empty($confirm_password)) {
                    $message = 'Please enter both password fields';
                } elseif ($new_password !== $confirm_password) {
                    $message = 'Passwords do not match';
                } elseif (strlen($new_password) < 6) {
                    $message = 'Password must be at least 6 characters';
                } else {
                    $admin_id = $_SESSION['admin_id'];
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare('UPDATE admin_users SET password = ? WHERE id = ?');
                    $stmt->bind_param('si', $hashed_password, $admin_id);
                    
                    if ($stmt->execute()) {
                        $message = 'Admin password changed successfully!';
                    } else {
                        $message = 'Error changing password.';
                    }
                }
                break;
        }
    }
}

// Get all users
$users_query = 'SELECT * FROM users ORDER BY role, name';
$users_result = $conn->query($users_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Assignment Portal</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: radial-gradient(circle at top left, #fdf2ff 0, #f5f7ff 40%, #f3f3f3 100%); color: #1f2933; }
        .admin-header { background: linear-gradient(90deg, #4f46e5, #0ea5e9); color: white; padding: 1rem 2rem; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25); display: flex; align-items: center; gap: 20px; }
        .admin-header .logo { max-width: 60px; height: auto; }
        .admin-header .header-content { flex: 1; }
        .admin-header h1 { margin: 0; font-size: 24px; }
        .admin-header p { margin: 5px 0 0 0; opacity: 0.9; font-size: 14px; }
        .admin-container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .admin-section { background: white; margin: 20px 0; padding: 30px; border-radius: 8px; box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15); border: 1px solid #dadce0; }
        .admin-section h2 { color: #1f2933; margin-bottom: 20px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; font-weight: 500; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; color: #3c4043; }
        .form-group input { width: 100%; padding: 12px; border: 2px solid #dadce0; border-radius: 6px; font-size: 16px; transition: border-color 0.2s; }
        .form-group input:focus { outline: none; border-color: #4f46e5; }
        .btn { padding: 12px 24px; background: linear-gradient(90deg, #4f46e5, #0ea5e9); color: white; border: none; border-radius: 6px; cursor: pointer; margin-right: 10px; font-weight: 500; transition: all 0.2s; }
        .btn:hover { background: linear-gradient(90deg, #4338ca, #0284c7); transform: translateY(-1px); }
        .btn-danger { background: #ea4335; }
        .btn-danger:hover { background: #d33b2c; }
        .btn-success { background: #34a853; }
        .btn-success:hover { background: #2d8e47; }
        .message { padding: 15px; margin: 10px 0; border-radius: 6px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-left: 4px solid #28a745; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-left: 4px solid #ea4335; }
        .users-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .users-table th, .users-table td { padding: 12px; text-align: left; border-bottom: 1px solid #dadce0; }
        .users-table th { background: #f8f9fa; font-weight: 500; color: #3c4043; }
        .users-table tr:hover { background: #f8f9fa; }
        .role-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .role-badge.student { background: #e8f5e9; color: #2e7d32; }
        .role-badge.faculty { background: #fff3e0; color: #f57c00; }
        .admin-actions { margin-top: 20px; }
        .admin-actions a { color: #4f46e5; text-decoration: none; margin-right: 15px; font-weight: 500; }
        .admin-actions a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="admin-header">
        <img src="../assets/images/DonBosco-Color_200px.png" alt="DonBosco Logo" class="logo">
        <div class="header-content">
            <h1>Admin Panel</h1>
            <p>Assignment Portal Management System</p>
        </div>
    </div>
    
    <div class="admin-container">
        
        <!-- Add Faculty Section -->
        <div class="admin-section">
            <h2>➕ Add Faculty</h2>
            <form method="post">
                <input type="hidden" name="action" value="add_faculty">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-success">Add Faculty</button>
            </form>
        </div>
        
        <!-- Manage Users Section -->
        <div class="admin-section">
            <h2>👥 Manage Users</h2>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span class="role-badge <?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirmDelete(event, this)">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Admin Settings Section -->
        <div class="admin-section">
            <h2>⚙️ Admin Settings</h2>
            <form method="post">
                <input type="hidden" name="action" value="change_admin_password">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="Enter new password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                </div>
                <button type="submit" class="btn">Change Password</button>
            </form>
            <p style="margin-top: 10px; color: #666; font-size: 14px;">
                Logged in as: <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
            </p>
        </div>
        
        <div class="admin-actions">
            <a href="../index.php">🏠 Back to Main Site</a>
            <a href="logout.php">🚪 Logout Admin</a>
        </div>
    </div>
    
    <script>
        function confirmDelete(event, button) {
            event.preventDefault();
            const form = button.closest('form');
            const userName = button.closest('tr').querySelector('td:nth-child(2)').textContent;
            
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to delete user "${userName}"? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33b2c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete user!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
        
        // Show SweetAlert for messages
        <?php if ($message): ?>
            <?php if (strpos($message, 'success') !== false): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?php echo addslashes($message); ?>',
                    confirmButtonColor: '#4f46e5'
                });
            <?php else: ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '<?php echo addslashes($message); ?>',
                    confirmButtonColor: '#4f46e5'
                });
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html>
