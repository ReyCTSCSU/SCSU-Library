<?php
require_once 'config.php';
requireLogin();

// ✅ STUDENT ONLY
if (!isStudent()) {
    header("Location: dashboard.php");
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// ===============================
// ✅ HANDLE RETURN BOOK
// ===============================
if (isset($_POST['return_book'])) {

    $borrow_id = (int)$_POST['borrow_id'];
    $book_id   = (int)$_POST['book_id'];

    // ✅ Get book title for notifications
    $titleStmt = $conn->prepare("SELECT title FROM Books WHERE book_id = ?");
    $titleStmt->bind_param("i", $book_id);
    $titleStmt->execute();
    $titleStmt->bind_result($book_title);
    $titleStmt->fetch();
    $titleStmt->close();

    // ✅ Update borrow record
    $stmt = $conn->prepare(
        "UPDATE Borrowings 
         SET return_date = CURDATE(), status = 'returned'
         WHERE borrow_id = ? AND user_id = ?"
    );
    $stmt->bind_param("is", $borrow_id, $user_id);
    $stmt->execute();
    $stmt->close();

    // ✅ Increase availability
    $update = $conn->prepare(
        "UPDATE Books 
         SET quantity_available = quantity_available + 1,
             status = 'available'
         WHERE book_id = ?"
    );
    $update->bind_param("i", $book_id);
    $update->execute();
    $update->close();

    // ✅ Student notification
    $student_msg = "You returned: " . $book_title;
    $note = $conn->prepare(
        "INSERT INTO Notifications (recipient_id, message, book_id)
         VALUES (?, ?, ?)"
    );
    $note->bind_param("ssi", $user_id, $student_msg, $book_id);
    $note->execute();
    $note->close();
}

// ===============================
// ✅ FETCH BORROWED (ACTIVE)
// ===============================
$borrowedStmt = $conn->prepare(
    "SELECT 
        BR.borrow_id,
        BR.book_id,
        B.title,
        B.author,
        BR.borrow_date,
        BR.due_date
     FROM Borrowings BR
     JOIN Books B ON BR.book_id = B.book_id
     WHERE BR.user_id = ? AND BR.status = 'borrowed'
     ORDER BY BR.borrow_id DESC"
);
$borrowedStmt->bind_param("s", $user_id);
$borrowedStmt->execute();
$borrowed = $borrowedStmt->get_result();
$borrowedStmt->close();

// ===============================
// ✅ FETCH RETURNED (HISTORY)
// ===============================
$returnedStmt = $conn->prepare(
    "SELECT 
        BR.book_id,
        B.title,
        B.author,
        BR.borrow_date,
        BR.due_date,
        BR.return_date
     FROM Borrowings BR
     JOIN Books B ON BR.book_id = B.book_id
     WHERE BR.user_id = ? AND BR.status = 'returned'
     ORDER BY BR.return_date DESC, BR.borrow_id DESC"
);

$returnedStmt->bind_param("s", $user_id);
$returnedStmt->execute();
$returned = $returnedStmt->get_result();
$returnedStmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Borrowings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <h1 class="logo">📚 SCSU Library</h1>
        <ul class="nav-links">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="books.php">Book Catalog</a></li>
            <li><a class="active" href="borrowings.php">My Borrowings</a></li>
            <li><a href="favorites.php">Favorites</a></li>
            <li><a href="notifications.php">Notifications</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">

<!-- =============================== -->
<!-- ✅ ACTIVE BORROWINGS -->
<!-- =============================== -->
<div class="card">
    <h2>📘 Active Borrowings</h2>

    <table class="borrow-table">
        <thead>
            <tr>
                <th>Book</th>
                <th>Borrowed</th>
                <th>Due</th>
                <th>View</th>
                <th>Return</th>
            </tr>
        </thead>
        <tbody>

        <?php if ($borrowed->num_rows > 0): ?>
            <?php while ($row = $borrowed->fetch_assoc()): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                    <small><?php echo htmlspecialchars($row['author']); ?></small>
                </td>
                <td><?php echo $row['borrow_date']; ?></td>
                <td><?php echo $row['due_date']; ?></td>
                <td>
                    <a href="book_details.php?book_id=<?php echo $row['book_id']; ?>" class="btn-view">View</a>
                </td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="borrow_id" value="<?php echo $row['borrow_id']; ?>">
                        <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                        <button type="submit" name="return_book" class="return-btn">Return</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">No active borrowings.</td></tr>
        <?php endif; ?>

        </tbody>
    </table>
</div>

<!-- =============================== -->
<!-- ✅ RETURN HISTORY -->
<!-- =============================== -->
<div class="card">
    <h2>✅ Return History</h2>

    <table class="borrow-table">
        <thead>
            <tr>
                <th>Book</th>
                <th>Borrowed</th>
                <th>Due</th>
                <th>Returned</th>
                <th>View</th>
            </tr>
        </thead>
        <tbody>

        <?php if ($returned->num_rows > 0): ?>
            <?php while ($row = $returned->fetch_assoc()): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                    <small><?php echo htmlspecialchars($row['author']); ?></small>
                </td>
                <td><?php echo $row['borrow_date']; ?></td>
                <td><?php echo $row['due_date']; ?></td>
                <td><?php echo $row['return_date']; ?></td>
                <td>
                    <a href="book_details.php?book_id=<?php echo $row['book_id']; ?>" class="btn-view">View</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">No return history yet.</td></tr>
        <?php endif; ?>

        </tbody>
    </table>
</div>

</div>
</body>
</html>

<?php closeDBConnection($conn); ?>
