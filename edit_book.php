<?php
require_once 'config.php';
requireLibrarian();

$conn = getDBConnection();

$book_id = (int)($_GET['book_id'] ?? 0);

if ($book_id <= 0) {
    header("Location: books.php");
    exit();
}

// ✅ FETCH BOOK TO EDIT
$stmt = $conn->prepare("SELECT * FROM Books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();
$stmt->close();

if (!$book) {
    closeDBConnection($conn);
    header("Location: books.php");
    exit();
}

$message = '';
$error = '';

// ✅ HANDLE UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title            = sanitizeInput($_POST['title']);
    $author           = sanitizeInput($_POST['author']);
    $isbn             = sanitizeInput($_POST['isbn']);
    $genre            = sanitizeInput($_POST['genre']);
    $year             = sanitizeInput($_POST['publication_year']);
    $location         = sanitizeInput($_POST['location']);
    $quantity_total   = (int)$_POST['quantity_total'];

    if ($title === '' || $author === '' || $genre === '' || $year === '' || $location === '' || $quantity_total < 0) {
        $error = "All required fields must be valid.";
    } else {

        // ✅ RECALCULATE AVAILABLE & STATUS
        $borrowed = $book['quantity_total'] - $book['quantity_available'];
        $quantity_available = max(0, $quantity_total - $borrowed);
        $status = ($quantity_available > 0) ? 'available' : 'out_of_stock';

        $update = $conn->prepare(
            "UPDATE Books SET
                title = ?,
                author = ?,
                isbn = ?,
                genre = ?,
                publication_year = ?,
                location = ?,
                quantity_total = ?,
                quantity_available = ?,
                status = ?
             WHERE book_id = ?"
        );

        $update->bind_param(
            "ssssisiisi",
            $title,
            $author,
            $isbn,
            $genre,
            $year,
            $location,
            $quantity_total,
            $quantity_available,
            $status,
            $book_id
        );

        if ($update->execute()) {

            // ✅ LOG ACTIVITY
            $librarian_id = $_SESSION['user_id'];
            $details = "Edited book: $title";

            $log = $conn->prepare(
                "INSERT INTO Activity_Log (librarian_id, action, book_id, details)
                 VALUES (?, 'Edited Book', ?, ?)"
            );
            $log->bind_param("sis", $librarian_id, $book_id, $details);
            $log->execute();
            $log->close();

            $message = "Book successfully updated!";

            // ✅ REFRESH BOOK DATA
            $stmt = $conn->prepare("SELECT * FROM Books WHERE book_id = ?");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $book = $stmt->get_result()->fetch_assoc();
            $stmt->close();

        } else {
            $error = "Failed to update book.";
        }

        $update->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book - SCSU Library</title>
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
                    <li><a href="notifications.php">Notifications</a></li>
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
            <h2>Edit Book</h2>
        </div>

        <div class="card">

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Author *</label>
                    <input type="text" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required>
                </div>

                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>">
                </div>

                <div class="form-group">
                    <label>Genre *</label>
                    <input type="text" name="genre" value="<?php echo htmlspecialchars($book['genre']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Publication Year *</label>
                    <input type="number" name="publication_year" value="<?php echo htmlspecialchars($book['publication_year']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Location *</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($book['location']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Total Quantity *</label>
                    <input type="number" name="quantity_total" value="<?php echo htmlspecialchars($book['quantity_total']); ?>" min="0" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Save Changes
                </button>

            </form>

        </div>
    </div>
</body>
</html>

<?php closeDBConnection($conn); ?>
