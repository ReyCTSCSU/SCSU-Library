<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// ✅ DELETE A SINGLE NOTIFICATION
if (isset($_POST['delete_one'])) {
    $notification_id = (int)$_POST['notification_id'];

    $stmt = $conn->prepare(
        "DELETE FROM Notifications 
         WHERE notification_id = ? AND recipient_id = ?"
    );
    $stmt->bind_param("is", $notification_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// ✅ CLEAR ALL NOTIFICATIONS
if (isset($_POST['clear_all'])) {
    $stmt = $conn->prepare(
        "DELETE FROM Notifications WHERE recipient_id = ?"
    );
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->close();
}

// ✅ FETCH NOTIFICATIONS
$stmt = $conn->prepare(
    "SELECT notification_id, message, created_at
     FROM Notifications
     WHERE recipient_id = ?
     ORDER BY created_at DESC"
);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

closeDBConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications - SCSU Library</title>
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

            <li><a href="notifications.php" class="active">Notifications</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">

    <div class="header" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2>🔔 Notifications</h2>
            <p>All your system alerts</p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <form method="POST" onsubmit="return confirmClearAll();">
                <button type="submit" name="clear_all" class="btn btn-danger">
                    Clear All
                </button>
            </form>
        <?php endif; ?>
    </div>

    <div class="card">

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>

                <div class="notification-item">

                <div class="notification-left">
                    <p class="notification-text">
                        <?php echo htmlspecialchars($row['message']); ?>
                    </p>
                    <small>
                        <?php echo date("M j, Y g:i A", strtotime($row['created_at'])); ?>
                    </small>
                </div>

                <form method="POST" style="margin-left:auto;">
                    <input type="hidden" name="notification_id" value="<?php echo $row['notification_id']; ?>">
                    <button type="submit" name="delete_one" class="delete-btn">
                        Delete
                    </button>
                </form>

                </div>



            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; color:#666;">You have no notifications.</p>
        <?php endif; ?>

    </div>
</div>

<script>
function confirmClearAll() {
    return confirm("Are you sure you want to delete ALL notifications? This cannot be undone.");
}
</script>

</body>
</html>
