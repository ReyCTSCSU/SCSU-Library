<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];
$role = $_SESSION['role'];

// ✅ STUDENT DASHBOARD STATS
if (isStudent()) {

    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM Borrowings 
         WHERE user_id = ? AND status = 'borrowed'"
    );
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($borrowed_count);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT MIN(due_date) FROM Borrowings 
         WHERE user_id = ? AND status = 'borrowed'"
    );
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($next_due_date);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM Favorites WHERE user_id = ?"
    );
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($favorites_count);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM Notifications WHERE recipient_id = ?"
    );
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($notification_count);
    $stmt->fetch();
    $stmt->close();
}

// ✅ LIBRARIAN DASHBOARD STATS
if (isLibrarian()) {

    $total_books = $conn->query(
        "SELECT COUNT(*) AS total FROM Books"
    )->fetch_assoc()['total'];

    $active_borrowings = $conn->query(
        "SELECT COUNT(*) AS total FROM Borrowings WHERE status = 'borrowed'"
    )->fetch_assoc()['total'];

    $out_of_stock = $conn->query(
        "SELECT COUNT(*) AS total FROM Books WHERE quantity_available = 0"
    )->fetch_assoc()['total'];

    $recent_activity = $conn->query(
        "SELECT action, timestamp FROM Activity_Log 
         ORDER BY timestamp DESC LIMIT 5"
    );
}

closeDBConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SCSU Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <h1 class="logo">📚 SCSU Library</h1>
        <ul class="nav-links">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
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
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">

    <!-- ✅ WELCOME CARD -->
    <div class="card">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?> 👋</h2>
        <p><?php echo ucfirst($role); ?> Dashboard</p>
    </div>

    <!-- ✅ DASHBOARD GRID -->
    <div class="dashboard-grid">

        <!-- ✅ STUDENT DASHBOARD -->
        <?php if (isStudent()): ?>

            <div class="stat-card">
                <h3>📘 Borrowed</h3>
                <p><?php echo $borrowed_count; ?></p>
            </div>

            <div class="stat-card">
                <h3>⏰ Next Due</h3>
                <p><?php echo $next_due_date ?? "None"; ?></p>
            </div>

            <div class="stat-card">
                <h3>⭐ Favorites</h3>
                <p><?php echo $favorites_count; ?></p>
            </div>

            <div class="stat-card">
                <h3>🔔 Alerts</h3>
                <p><?php echo $notification_count; ?></p>
            </div>

            <div class="card quick-actions">
                <h3>⚡ Quick Actions</h3>
                <a href="books.php" class="btn btn-primary">Browse Books</a>
                <a href="borrowings.php" class="btn btn-secondary">My Borrowings</a>
                <a href="favorites.php" class="btn btn-secondary">My Favorites</a>
            </div>

        <?php endif; ?>

        <!-- ✅ LIBRARIAN DASHBOARD -->
        <?php if (isLibrarian()): ?>

            <div class="stat-card">
                <h3>📚 Total Books</h3>
                <p><?php echo $total_books; ?></p>
            </div>

            <div class="stat-card">
                <h3>📤 Active Loans</h3>
                <p><?php echo $active_borrowings; ?></p>
            </div>

            <div class="stat-card">
                <h3>⚠️ Out of Stock</h3>
                <p><?php echo $out_of_stock; ?></p>
            </div>

            <div class="card">
                <h3>🧾 Recent Activity</h3>
                <?php if ($recent_activity->num_rows > 0): ?>
                    <ul class="activity-list">
                        <?php while ($row = $recent_activity->fetch_assoc()): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($row['action']); ?></strong>
                                <small><?php echo date("M j, g:i A", strtotime($row['timestamp'])); ?></small>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No recent activity.</p>
                <?php endif; ?>
            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>
