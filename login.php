<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

$error = '';
$success = '';

if (isset($_GET['success']) && $_GET['success'] === 'registered') {
    $success = "✅ Account created successfully! You can now log in.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = sanitizeInput($_POST['user_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Please enter both email and password.";
    } else {

        $conn = getDBConnection();

        $stmt = $conn->prepare("SELECT user_id, user_email, full_name, role, password_hash 
                                FROM Users WHERE user_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($user_id, $user_email, $full_name, $role, $password_hash);

        if ($stmt->num_rows === 1) {
            $stmt->fetch();

            if (password_verify($password, $password_hash)) {

                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_email'] = $user_email;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['role'] = $role;

                closeDBConnection($conn);
                header("Location: dashboard.php");
                exit();

            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with that email.";
        }

        $stmt->close();
        closeDBConnection($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - SCSU Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-box">
        <div class="logo-text">📚 SCSU Library</div>

        <h2>Welcome Back</h2>
        <p>Sign in to manage your library account</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <div class="form-group">
                <label for="user_email">SCSU Email</label>
                <input type="email" id="user_email" name="user_email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
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
