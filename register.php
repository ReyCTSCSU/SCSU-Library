<?php
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$error = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: Add registration logic here
    $message = 'Registration functionality coming soon!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SCSU Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="logo-text">📚 SCSU Library</div>
            <h2>Create Account</h2>
            <p>Register to access the library system</p>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label>User ID *</label>
                    <input type="text" name="user_id" placeholder="e.g., STU001" required>
                </div>
                
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label>SCSU Email *</label>
                    <input type="email" name="user_email" placeholder="your.email@southernct.edu" required>
                </div>
                
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" required>
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="librarian">Librarian</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Register
                </button>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>
        </div>
    </div>
</body>
</html>