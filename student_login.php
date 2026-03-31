<?php
require_once 'config.php';
$conn = get_db_connection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT id, name, password_hash, role FROM users WHERE email = ? AND role = "student"');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role'];
            header('Location: student_dashboard.php');
            exit;
        }
    }
    $message = 'Invalid credentials.';
}
?>
<?php include 'header.php'; ?>
<h2>Student Login</h2>
<div class="form-card">
    <?php if (isset($_GET['registered'])): ?>
        <p style="color:green; margin-bottom:0.5rem;">Registration successful. Please login.</p>
    <?php endif; ?>
    <?php if ($message): ?>
        <p style="color:red; margin-bottom:0.5rem;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
        <a href="student_register.php" class="btn-outline">Register</a>
    </form>
</div>
<?php include 'footer.php'; ?>
