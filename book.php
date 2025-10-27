<?php
// book.php - NOVEL NEST Book Details Page
include_once 'includes/db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: books.php');
    exit;
}

// Handle delete action
if (isset($_GET['delete']) && isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    $stmt = $db->prepare("DELETE FROM books WHERE id = ?");
    $stmt->execute([$id]);
    echo '<script>alert("Book deleted successfully!");window.location.href="books.php";</script>';
    exit;
}

$stmt = $db->prepare("SELECT books.*, authors.name AS author_name FROM books LEFT JOIN authors ON books.author_id = authors.id WHERE books.id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$book) {
    echo '<h2>Book not found.</h2>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book['title']) ?> - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; }
        .book-detail { max-width:700px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; display:flex; gap:32px; }
        .book-cover { width:180px; height:250px; object-fit:cover; border-radius:12px; background:#eee; }
        .book-info { flex:1; }
        .book-title { font-size:2rem; font-weight:bold; margin-bottom:8px; }
        .book-author { color:#888; font-size:1.1rem; margin-bottom:8px; }
        .book-type { font-size:1rem; color:#555; margin-bottom:8px; }
        .book-desc { font-size:1.05rem; margin-bottom:12px; }
        .book-price { color:#ff3366; font-weight:bold; font-size:1.2rem; margin-bottom:8px; }
        .book-stock { font-size:1rem; color:#080; margin-bottom:8px; }
        .book-actions { margin-top:16px; }
        .book-actions button { background:#ff3366; color:#fff; border:none; padding:10px 28px; border-radius:8px; cursor:pointer; margin:0 8px; font-size:1rem; }
        .book-actions .delete-btn { background:#c00; }
    </style>
</head>
<body>
    <div class="book-detail">
        <img src="<?= $book['cover'] ? htmlspecialchars($book['cover']) : 'assets/default-book.png' ?>" class="book-cover" alt="Book Cover">
        <div class="book-info">
            <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
            <div class="book-author">by <?= htmlspecialchars($book['author_name'] ?? 'Unknown') ?></div>
            <div class="book-type">Type: <?= htmlspecialchars($book['type']) ?></div>
            <div class="book-desc"><?= htmlspecialchars($book['description']) ?></div>
            <div class="book-price">$<?= number_format($book['price'], 2) ?></div>
            <div class="book-stock">Stock: <?= (int)$book['stock'] ?></div>
            <div class="book-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="buy">
                        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                        <button type="submit">Buy</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="lend">
                        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                        <button type="submit">Lend</button>
                    </form>
                <?php else: ?>
                    <button disabled title="Login to buy">Buy</button>
                    <button disabled title="Login to lend">Lend</button>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="book.php?id=<?= $book['id'] ?>&delete=1" onclick="return confirm('Delete this book?')">
                        <button type="button" class="delete-btn">Delete</button>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div style="text-align:center;margin-top:24px;">
        <a href="books.php" style="color:#ff3366;text-decoration:underline;">&larr; Back to Books</a>
    </div>
</body>
</html>
<?php
// Handle Buy/Lend actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $action = $_POST['action'] ?? '';
    $book_id = (int)($_POST['book_id'] ?? 0);
    $user_id = $_SESSION['user_id'];
    $now = date('Y-m-d H:i:s');
    if ($action === 'buy') {
        // Insert into buy table (simulate online method, no payment for now)
        $stmt = $db->prepare("INSERT INTO buy (user_id, branch, time, books, method, payment_total, payment_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, 'Main', $now, $book['id'], 'online', $book['price'], uniqid('PAY')]);
        echo '<script>alert("Book bought successfully!");window.location.href="profile.php";</script>';
        exit;
    } elseif ($action === 'lend') {
        // Insert into lend table (simulate online method)
        $return_time = date('Y-m-d H:i:s', strtotime('+14 days'));
        $stmt = $db->prepare("INSERT INTO lend (user_id, branch, received_time, return_time, books, method) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, 'Main', $now, $return_time, $book['id'], 'online']);
        echo '<script>alert("Book lent successfully! Please return by ' . $return_time . '");window.location.href="profile.php";</script>';
        exit;
    }
}
?>
