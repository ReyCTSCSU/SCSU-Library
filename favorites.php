<?php
require_once 'config.php';
requireLogin();

// Only students can access
if (!isStudent()) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites - SCSU Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">📚 SCSU Library</h1>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="books.php">Book Catalog</a></li>
                <li><a href="borrowings.php">My Borrowings</a></li>
                <li><a href="favorites.php" class="active">Favorites</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h2>My Favorites</h2>
        </div>
        
        <div class="card">
            <h3>Favorites Coming Soon!</h3>
        </div>
    </div>
</body>
</html>