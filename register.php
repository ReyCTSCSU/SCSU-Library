<?php
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id    = sanitizeInput($_POST['user_id'] ?? '');
    $full_name  = sanitizeInput($_POST['full_name'] ?? '');
    $email      = sanitizeInput($_POST['user_email'] ?? '');
    $role       = sanitizeInput($_POST['role'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($user_id === '' || $full_name === '' || $email === '' || $role === '' || $password === '') {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {

        $conn = getDBConnection();

        $check = $conn->prepare("SELECT user_id FROM Users WHERE user_id = ? OR user_email = ?");
        $check->bind_param("ss", $user_id, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "User ID or email already exists.";
        } else {

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO Users (user_id, user_email, password_hash, full_name, role) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssss", $user_id, $email, $password_hash, $full_name, $role);

            if ($stmt->execute()) {
                $message = "Account created successfully! You can now log in.";
            } else {
                $error = "Registration failed. Please try again.";
            }

            $stmt->close();
        }

        $check->close();
        closeDBConnection($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - SCSU Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-box">
        <div class="logo-text">📚 SCSU Library</div>

        <h2>Create Account</h2>
        <p>Register to access the library system</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">

            <div class="form-group">
                <label>Student ID/Librarian ID</label>
                <input type="text" name="user_id" required>
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>

            <div class="form-group">
                <label>SCSU Email</label>
                <input type="email" name="user_email" required>
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="student">Student</option>
                    <option value="librarian">Librarian</option>
                </select>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">
                Register
            </button>

        </form>

        <div class="auth-links">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>

    </div>
</div>

</body>
</html>
