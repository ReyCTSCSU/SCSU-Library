<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
requireLogin();

$conn = getDBConnection();

$user_id = $_SESSION['user_id'];
$book_id = (int)($_GET['book_id'] ?? 0);

if ($book_id <= 0) {
    header("Location: books.php");
    exit();
}

// ✅ FETCH BOOK
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

/* ============================
   ✅ HANDLE BORROW
=============================== */
if (isset($_POST['borrow'])) {

    if ($book['quantity_available'] <= 0) {
        $error = "This book is currently out of stock.";
    } else {

        // Check stock BEFORE borrowing so we know if this is last copy / low stock
        $was_last_copy     = ($book['quantity_available'] == 1); // will become 0
        $will_be_low_stock = ($book['quantity_available'] == 2); // will become 1

        $borrow_date = date("Y-m-d");
        $due_date    = date("Y-m-d", strtotime("+14 days"));

        $stmt = $conn->prepare(
            "INSERT INTO Borrowings (user_id, book_id, borrow_date, due_date, status)
             VALUES (?, ?, ?, ?, 'borrowed')"
        );
        $stmt->bind_param("siss", $user_id, $book_id, $borrow_date, $due_date);

        if ($stmt->execute()) {

            // ✅ UPDATE BOOK AVAILABILITY
            $update = $conn->prepare(
                "UPDATE Books 
                 SET quantity_available = quantity_available - 1,
                     status = IF(quantity_available - 1 > 0, 'available', 'out_of_stock')
                 WHERE book_id = ?"
            );
            $update->bind_param("i", $book_id);
            $update->execute();
            $update->close();

            // ✅ STUDENT NOTIFICATION
            $msg = "You borrowed: " . $book['title'];
            $note = $conn->prepare(
                "INSERT INTO Notifications (recipient_id, message, book_id)
                 VALUES (?, ?, ?)"
            );
            $note->bind_param("ssi", $user_id, $msg, $book_id);
            $note->execute();
            $note->close();

            // ✅ LIBRARIAN NOTIFICATIONS (LOW STOCK / OUT OF STOCK)
            $lib_msg = null;

            if ($was_last_copy) {
                // After this borrow, copies go from 1 -> 0
                $lib_msg = "❌ OUT OF STOCK: '" . $book['title'] . "' was just borrowed and is now unavailable.";
                $message = "Book successfully borrowed! This was the last available copy and the book is now out of stock.";
            } elseif ($will_be_low_stock) {
                // After this borrow, copies go from 2 -> 1
                $lib_msg = "⚠ LOW STOCK: Only 1 copy left of '" . $book['title'] . "' after a recent borrowing.";
                $message = "Book successfully borrowed!";
            } else {
                $message = "Book successfully borrowed!";
            }

            if ($lib_msg) {
                // Send notification to ALL librarians
                $libs = $conn->query("SELECT user_id FROM Users WHERE role = 'librarian'");
                while ($lib = $libs->fetch_assoc()) {
                    $lib_note = $conn->prepare(
                        "INSERT INTO Notifications (recipient_id, message, book_id)
                         VALUES (?, ?, ?)"
                    );
                    $lib_note->bind_param("ssi", $lib['user_id'], $lib_msg, $book_id);
                    $lib_note->execute();
                    $lib_note->close();
                }
            }

            // Update local copy count for display
            $book['quantity_available']--;

        } else {
            $error = "Failed to borrow book.";
        }

        $stmt->close();
    }
}

/* ============================
   ✅ HANDLE ADD FAVORITE
=============================== */
if (isset($_POST['favorite'])) {

    $check = $conn->prepare(
        "SELECT favorite_id FROM Favorites WHERE user_id = ? AND book_id = ?"
    );
    $check->bind_param("si", $user_id, $book_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {

        $stmt = $conn->prepare(
            "INSERT INTO Favorites (user_id, book_id)
             VALUES (?, ?)"
        );
        $stmt->bind_param("si", $user_id, $book_id);
        $stmt->execute();
        $stmt->close();

        $message = "Book added to your favorites!";
    }

    $check->close();
}

/* ============================
   ✅ HANDLE REMOVE FAVORITE
=============================== */
if (isset($_POST['remove_favorite'])) {
    $favorite_id = (int)$_POST['favorite_id'];

    $stmt = $conn->prepare(
        "DELETE FROM Favorites WHERE favorite_id = ? AND user_id = ?"
    );
    $stmt->bind_param("is", $favorite_id, $user_id);
    $stmt->execute();
    $stmt->close();

    $message = "Book removed from your favorites!";
}

/* ============================
   ✅ CHECK FAVORITE STATUS
=============================== */
$isFavorite = false;
$fav_id = null;

$favCheck = $conn->prepare(
    "SELECT favorite_id FROM Favorites WHERE user_id = ? AND book_id = ?"
);
$favCheck->bind_param("si", $user_id, $book_id);
$favCheck->execute();
$favResult = $favCheck->get_result();

if ($rowFav = $favResult->fetch_assoc()) {
    $isFavorite = true;
    $fav_id = $rowFav['favorite_id'];
}

$favCheck->close();

closeDBConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Details - SCSU Library</title>
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
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">

    <!-- ✅ BOOK HEADER -->
    <div class="card">
        <h2><?php echo htmlspecialchars($book['title']); ?></h2>
        <p>by <?php echo htmlspecialchars($book['author']); ?></p>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
    </div>

    <!-- ✅ DETAILS -->
    <div class="card">
        <div><strong>Genre:</strong> <?php echo htmlspecialchars($book['genre']); ?></div>
        <div><strong>Published:</strong> <?php echo htmlspecialchars($book['publication_year']); ?></div>
        <div><strong>Location:</strong> <?php echo htmlspecialchars($book['location']); ?></div>
        <div>
            <strong>Available Copies:</strong>
            <?php
                if ($book['quantity_available'] > 0) {
                    echo $book['quantity_available'];
                } else {
                    echo 'Out of stock';
                }
            ?>
        </div>
    </div>

    <!-- ✅ ACTION BUTTONS -->
    <?php if (isStudent()): ?>
        <div class="card" style="display:flex; gap:15px;">

            <form method="POST">

                <?php if ($book['quantity_available'] > 0): ?>
                    <button type="submit" name="borrow" class="btn btn-primary">
                        📘 Borrow Book
                    </button>
                <?php endif; ?>

                <?php if (!$isFavorite): ?>
                    <button type="submit" name="favorite" class="btn btn-secondary">
                        ⭐ Add to Favorites
                    </button>
                <?php else: ?>
                    <input type="hidden" name="favorite_id" value="<?php echo $fav_id; ?>">
                    <button type="submit" name="remove_favorite" class="btn btn-danger">
                        ❌ Remove from Favorites
                    </button>
                <?php endif; ?>

            </form>

        </div>
    <?php endif; ?>

</div>

</body>
</html>
