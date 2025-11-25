<?php
require_once 'config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Catalog - SCSU Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">📚 SCSU Library</h1>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="books.php" class="active">Book Catalog</a></li>
                <li><a href="borrowings.php">My Borrowings</a></li>
                <li><a href="favorites.php">Favorites</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h2>Book Catalog</h2>
            <p>Search and browse available books</p>
        </div>
        
        <div class="card">
            <h3>Book Catalog Coming Soon!</h3>
            <p>This page will display all books with search functionality.</p>
        </div>
    </div>
</body>
</html>