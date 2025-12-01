<?php
require_once 'config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - SCSU Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <h1 class="logo">📚 SCSU Library</h1>
        <ul class="nav-links">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="books.php">Book Catalog</a></li>

            <?php if (isStudent()): ?>
                <li><a href="borrowings.php">My Borrowings</a></li>
                <li><a href="favorites.php">Favorites</a></li>
            <?php endif; ?>
            
             <?php if (isLibrarian()): ?>
                <li><a href="add_book.php">Add Book</a></li>
                <li><a href="activity_log.php">Activity Log</a></li>
            <?php endif; ?>

            <li><a href="notifications.php">Notifications</a></li>
            <li><a href="profile.php" class="active">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="header">
        <h2>👤 My Profile</h2>
        <p>View your account information</p>
    </div>

    <div class="card profile-card">

        <!-- ✅ PROFILE AVATAR -->
        <div class="profile-avatar">
            <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
        </div>

        <!-- ✅ PROFILE DETAILS -->
        <div class="profile-details">

            <!-- ✅ STUDENT ID / LIBRARIAN ID -->
            <div class="profile-row">
                <span>
                    <?php echo $_SESSION['role'] === 'librarian' ? 'Librarian ID' : 'Student ID'; ?>
                </span>
                <strong><?php echo htmlspecialchars($_SESSION['user_id']); ?></strong>
            </div>

            <div class="profile-row">
                <span>Full Name</span>
                <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>
            </div>

            <div class="profile-row">
                <span>Email</span>
                <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong>
            </div>

            <div class="profile-row">
                <span>Role</span>
                <strong class="role-badge <?php echo $_SESSION['role'] === 'librarian' ? 'librarian' : 'student'; ?>">
                    <?php echo ucfirst($_SESSION['role']); ?>
                </strong>
            </div>

            <!-- ✅ FIXED DATE JOINED -->
            <div class="profile-row">
                <span>Date Joined</span>
                <strong>
                    <?php echo isset($_SESSION['date_joined']) 
                        ? date("F j, Y", strtotime($_SESSION['date_joined'])) 
                        : 'Not available'; ?>
                </strong>
            </div>

        </div>
    </div>

</div>

</body>
</html>
