<?php
require_once 'config.php';

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fake login for testing
    $_SESSION['user_id'] = 'STU001';
    $_SESSION['user_email'] = $_POST['email'];
    $_SESSION['full_name'] = 'Test Student';
    $_SESSION['role'] = 'student';
    
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SCSU Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="logo-text">📚 SCSU Library</div>
            <h2>Welcome Back</h2>
            <p>Sign in to manage your library account</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="user_email">SCSU Email</label>
                    <input type="email" id="user_email" name="user_email" 
                           placeholder="your.email@southernct.edu" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Sign In
                </button>
            </form>
            
            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</body>
</html>