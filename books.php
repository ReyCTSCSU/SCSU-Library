<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();

/* ===============================
   ✅ HANDLE ACTIVATE / DEACTIVATE
================================ */
if (isLibrarian() && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $book_id = (int)$_POST['book_id'];

    if (isset($_POST['deactivate'])) {
        $stmt = $conn->prepare(
            "UPDATE Books SET is_active = 0 WHERE book_id = ?"
        );
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['reactivate'])) {
        $stmt = $conn->prepare(
            "UPDATE Books SET is_active = 1 WHERE book_id = ?"
        );
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $stmt->close();
    }
}

/* ===============================
   ✅ SORT + SEARCH
================================ */
$sort   = $_GET['sort']   ?? 'title_asc';
$search = $_GET['search'] ?? '';

switch ($sort) {
    case 'title_desc':   $orderBy = "title DESC"; break;
    case 'year_desc':    $orderBy = "publication_year DESC"; break;
    case 'year_asc':     $orderBy = "publication_year ASC"; break;
    case 'available':    $orderBy = "quantity_available DESC"; break;
    default:             $orderBy = "title ASC"; break;
}

$visibilityFilter = isStudent() ? "AND is_active = 1" : "";

if (!empty($search)) {
    $stmt = $conn->prepare(
        "SELECT * FROM Books 
         WHERE (title LIKE ? OR author LIKE ?)
         $visibilityFilter
         ORDER BY $orderBy"
    );
    $like = "%" . $search . "%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM Books WHERE 1=1 $visibilityFilter ORDER BY $orderBy";
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

        <form method="GET" class="catalog-controls">

            <input 
                type="text" 
                name="search" 
                placeholder="Search by title or author..."
                value="<?php echo htmlspecialchars($search); ?>"
                class="search-input"
            >

            <button type="submit" class="btn btn-primary">Search</button>

            <select name="sort" onchange="this.form.submit()" class="sort-select">
                <option value="title_asc">Title A–Z</option>
                <option value="title_desc">Title Z–A</option>
                <option value="year_desc">Newest First</option>
                <option value="year_asc">Oldest First</option>
                <option value="available">Available First</option>
            </select>

        </form>
    </div>

    <?php while ($row = $result->fetch_assoc()): ?>

        <div class="book-card">

            <div class="book-info">
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p>by <?php echo htmlspecialchars($row['author']); ?></p>
            </div>

            <div class="book-actions">

                <?php if ($row['is_active'] == 0): ?>
                    <span class="status-badge inactive">Inactive</span>
                <?php elseif ($row['quantity_available'] > 0): ?>
                    <span class="status-badge available">Available</span>
                <?php else: ?>
                    <span class="status-badge out">Out of Stock</span>
                <?php endif; ?>

                <a href="book_details.php?book_id=<?php echo $row['book_id']; ?>" class="btn-view">
                    View
                </a>

                <?php if (isLibrarian()): ?>

                    <a href="edit_book.php?book_id=<?php echo $row['book_id']; ?>" class="btn-view">
                        Edit
                    </a>

                    <?php if ($row['is_active'] == 1): ?>
                        <form method="POST" style="display:inline;"
                          onsubmit="return confirm('Deactivate this book? Students will NOT be able to borrow it.');">
                            <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                            <button type="submit" name="deactivate" class="btn-danger">
                                Deactivate
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" style="display:inline;"
                          onsubmit="return confirm('Reactivate this book and allow borrowing again?');">
                            <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                            <button type="submit" name="reactivate" class="btn-success">
                                Reactivate
                            </button>
                        </form>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </div>

    <?php endwhile; ?>

</div>

</body>
</html>

<?php closeDBConnection($conn); ?>
