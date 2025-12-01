<?php
require_once 'config.php';
requireLibrarian();

$conn = getDBConnection();

// ✅ FETCH ACTIVITY LOG
$stmt = $conn->prepare(
    "SELECT 
        U.user_id AS librarian_id,
        A.action,
        A.timestamp,
        A.details,
        U.full_name AS librarian_name,
        B.title AS book_title
     FROM Activity_Log A
     JOIN Users U ON A.librarian_id = U.user_id
     LEFT JOIN Books B ON A.book_id = B.book_id
     ORDER BY A.timestamp DESC"
);

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Log - SCSU Library</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <li><a href="activity_log.php" class="active">Activity Log</a></li>
            <?php endif; ?>

            <li><a href="notifications.php">Notifications</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">

    <div class="header">
        <h2>📋 Activity Log</h2>
        <p>All catalog actions performed by librarians</p>
    </div>

    <div class="card">

        <?php if ($result->num_rows > 0): ?>

            <div style="overflow-x:auto;">
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Librarian ID</th>
                            <th>Name</th>
                            <th>Action</th>
                            <th>Book</th>
                            <th>Details</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['librarian_id']); ?></strong></td>

                            <td class="name-cell">
                                <?php echo htmlspecialchars($row['librarian_name']); ?>
                            </td>

                            <td>
                                <span class="badge badge-blue">
                                    <?php echo htmlspecialchars($row['action']); ?>
                                </span>
                            </td>

                            <td>
                                <?php echo $row['book_title']
                                    ? '<strong>' . htmlspecialchars($row['book_title']) . '</strong>'
                                    : '—'; ?>
                            </td>

                            <td class="details-cell">
                                <?php echo htmlspecialchars($row['details']); ?>
                            </td>

                            <td class="date-cell">
                                <?php echo date("M j, Y g:i A", strtotime($row['timestamp'])); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <p>No activity has been logged yet.</p>
        <?php endif; ?>

    </div>
</div>

</body>
</html>

<?php closeDBConnection($conn); ?>
