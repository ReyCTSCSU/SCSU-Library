<?php
require_once 'config.php';
requireLogin();

// ✅ Only students can access
if (!isStudent()) {
    header('Location: dashboard.php');
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// ✅ REMOVE FAVORITE
if (isset($_POST['remove_favorite'])) {
    $favorite_id = (int)$_POST['favorite_id'];

    $stmt = $conn->prepare("DELETE FROM Favorites WHERE favorite_id = ? AND user_id = ?");
    $stmt->bind_param("is", $favorite_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// ✅ FETCH FAVORITES
$stmt = $conn->prepare(
    "SELECT 
        F.favorite_id,
        B.book_id,
        B.title,
        B.author,
        B.genre,
        B.publication_year,
        B.location
     FROM Favorites F
     JOIN Books B ON F.book_id = B.book_id
     WHERE F.user_id = ?
     ORDER BY F.date_added DESC"
);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Favorites - SCSU Library</title>
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
        <p>Your saved books for quick access</p>
    </div>

    <div class="card">

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>

                <div class="favorite-card">

                    <div class="favorite-info">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p>
                            <?php echo htmlspecialchars($row['author']); ?> •
                            <?php echo htmlspecialchars($row['genre']); ?> •
                            <?php echo htmlspecialchars($row['publication_year']); ?> •
                            <?php echo htmlspecialchars($row['location']); ?>
                        </p>
                    </div>

                    <div class="favorite-actions">
                        <a href="book_details.php?book_id=<?php echo $row['book_id']; ?>" 
                           class="btn btn-primary">
                            View
                        </a>

                        <form method="POST">
                            <input type="hidden" name="favorite_id" value="<?php echo $row['favorite_id']; ?>">
                            <button type="submit" name="remove_favorite" class="btn btn-danger">
                                Remove
                            </button>
                        </form>
                    </div>

                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; color:#666;">You have no favorite books yet.</p>
        <?php endif; ?>

    </div>
</div>

</body>
</html>

<?php closeDBConnection($conn); ?>
