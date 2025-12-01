<?php
require_once 'config.php';
requireLibrarian();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title            = sanitizeInput($_POST['title'] ?? '');
    $author           = sanitizeInput($_POST['author'] ?? '');
    $isbn             = sanitizeInput($_POST['isbn'] ?? '');
    $genre            = sanitizeInput($_POST['genre'] ?? '');
    $year             = sanitizeInput($_POST['publication_year'] ?? '');
    $location         = sanitizeInput($_POST['location'] ?? '');
    $quantity_total   = (int)($_POST['quantity_total'] ?? 0);

    if (
        $title === '' || $author === '' || $genre === '' ||
        $year === '' || $location === '' || $quantity_total <= 0
    ) {
        $error = "All required fields must be filled out.";
    } else {

        $quantity_available = $quantity_total;
        $status = 'available';
        $is_active = 1;

        $conn = getDBConnection();

        $stmt = $conn->prepare(
            "INSERT INTO Books 
            (title, author, isbn, genre, publication_year, location, quantity_total, quantity_available, status, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
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
            $is_active
        );

        if ($stmt->execute()) {

            $newBookId = $stmt->insert_id;
            $librarian_id = $_SESSION['user_id'];

            $log = $conn->prepare(
                "INSERT INTO Activity_Log (librarian_id, action, book_id, details)
                 VALUES (?, 'Added Book', ?, ?)"
            );
            $details = "Book added: $title by $author";
            $log->bind_param("sis", $librarian_id, $newBookId, $details);
            $log->execute();
            $log->close();

            $noteMsg = "You added a new book: $title";
            $note = $conn->prepare(
                "INSERT INTO Notifications (recipient_id, message, book_id)
                 VALUES (?, ?, ?)"
            );
            $note->bind_param("ssi", $librarian_id, $noteMsg, $newBookId);
            $note->execute();
            $note->close();

            $message = "✅ Book successfully added to the catalog!";
        } else {
            $error = "❌ Failed to add book. Please try again.";
        }

        $stmt->close();
        closeDBConnection($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Book - SCSU Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <h1 class="logo">📚 SCSU Library</h1>
        <ul class="nav-links">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="books.php">Book Catalog</a></li>
            <li><a href="add_book.php" class="active">Add Book</a></li>
            <li><a href="activity_log.php">Activity Log</a></li>
            <li><a href="notifications.php">Notifications</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">

    <div class="header">
        <h2>➕ Add New Book</h2>
        <p>Enter complete book details below</p>
    </div>

    <div class="card">

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">

            <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:20px;">

                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required>
                </div>

                <div class="form-group">
                    <label>Author *</label>
                    <input type="text" name="author" required>
                </div>

                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn">
                </div>

                <div class="form-group">
                    <label>Genre *</label>
                    <input type="text" name="genre" required>
                </div>

                <div class="form-group">
                    <label>Publication Year *</label>
                    <input type="number" name="publication_year" required>
                </div>

                <div class="form-group">
                    <label>Location *</label>
                    <input type="text" name="location" required>
                </div>

                <div class="form-group">
                    <label>Total Copies *</label>
                    <input type="number" name="quantity_total" min="1" required>
                </div>

            </div>

            <div style="margin-top:25px;">
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    ✅ Add Book
                </button>
            </div>

        </form>

    </div>
</div>

</body>
</html>
