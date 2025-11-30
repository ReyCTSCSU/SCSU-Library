<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();

// ✅ HANDLE SORTING
$sort = $_GET['sort'] ?? 'title_asc';
$search = $_GET['search'] ?? '';

// ✅ SORT LOGIC
switch ($sort) {
    case 'title_desc':
        $orderBy = "title DESC";
        break;
    case 'year_desc':
        $orderBy = "publication_year DESC";
        break;
    case 'year_asc':
        $orderBy = "publication_year ASC";
        break;
    case 'available':
        $orderBy = "quantity_available DESC";
        break;
    default:
        $orderBy = "title ASC";
        break;
}

// ✅ SEARCH + SORT QUERY
if (!empty($search)) {
    $stmt = $conn->prepare(
        "SELECT * FROM Books 
         WHERE title LIKE ? OR author LIKE ?
         ORDER BY $orderBy"
    );
    $like = "%" . $search . "%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM Books ORDER BY $orderBy";
    $result = $conn->query($sql);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Catalog - SCSU Library</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <h1 class="logo">📚 SCSU Library</h1>
        <ul class="nav-links">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="books.php" class="active">Book Catalog</a></li>

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

    <div class="header">
        <h2>📚 Book Catalog</h2>
        <p>Browse available books in the SCSU Library</p>

        <form method="GET" class="catalog-controls">

    <!-- ✅ SEARCH BAR -->
    <input 
        type="text" 
        name="search" 
        placeholder="Search by title or author..."
        value="<?php echo htmlspecialchars($search); ?>"
        class="search-input"
    >
    <button type="submit" class="btn btn-primary">Search</button>

    <!-- ✅ SORT DROPDOWN -->
    <select name="sort" onchange="this.form.submit()" class="sort-select">
        <option value="title_asc" <?= $sort == 'title_asc' ? 'selected' : '' ?>>Title A–Z</option>
        <option value="title_desc" <?= $sort == 'title_desc' ? 'selected' : '' ?>>Title Z–A</option>
        <option value="year_desc" <?= $sort == 'year_desc' ? 'selected' : '' ?>>Newest First</option>
        <option value="year_asc" <?= $sort == 'year_asc' ? 'selected' : '' ?>>Oldest First</option>
        <option value="available" <?= $sort == 'available' ? 'selected' : '' ?>>Available First</option>
    </select>

</form>

    </div>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>

            <div class="book-card">

                <div class="book-info">
                    <h3 class="book-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="book-author">by <?php echo htmlspecialchars($row['author']); ?></p>

                    <div class="book-meta">
                        <span><strong>Genre:</strong> <?php echo htmlspecialchars($row['genre']); ?></span>
                        <span><strong>Year:</strong> <?php echo htmlspecialchars($row['publication_year']); ?></span>
                        <span><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></span>
                    </div>
                </div>

                <div class="book-actions">

                    <span class="status-badge <?php echo ($row['quantity_available'] > 0 ? 'available' : 'out'); ?>">
                        <?php echo ($row['quantity_available'] > 0 ? 'Available' : 'Out of Stock'); ?>
                    </span>

                    <a href="book_details.php?book_id=<?php echo $row['book_id']; ?>" class="btn-view">
                        View
                    </a>

                    <?php if (isLibrarian()): ?>
                        <a href="edit_book.php?book_id=<?php echo $row['book_id']; ?>" 
                           class="btn-view" 
                           style="margin-left: 10px; background: linear-gradient(135deg, #ff9800, #f57c00);">
                            Edit
                        </a>
                    <?php endif; ?>

                </div>

            </div>

        <?php endwhile; ?>
    <?php else: ?>
        <div class="card">
            <p>No books found in the catalog.</p>
        </div>
    <?php endif; ?>

</div>

</body>
</html>

<?php closeDBConnection($conn); ?>
