<?php
include_once 'includes/db_connect.php';

// Get all cover images for random fallback
$cover_dir = __DIR__ . '/assets/Covers/';
$cover_files = array_filter(glob($cover_dir . '*'), 'is_file');
$cover_urls = array_map(function($f) {
    return 'assets/Covers/' . basename($f);
}, $cover_files);
$bg_cover = $cover_urls ? $cover_urls[array_rand($cover_urls)] : 'assets/Covers/default.jpg';

// Get all unique book types for dropdown
$type_stmt = $db->query("SELECT DISTINCT type FROM books WHERE type IS NOT NULL AND type != '' ORDER BY type ASC");
$all_types = $type_stmt->fetchAll(PDO::FETCH_COLUMN);

// Handle filters and search
$where = [];
$params = [];
$pdf_mode = false;
if (isset($_GET['pdf']) && $_GET['pdf'] == 1) {
    $where[] = "(pdf_type = 'yes' OR pdf_type = 'both')";
    $pdf_mode = true;
}
if (isset($_GET['type'])) {
    $where[] = 'LOWER(books.type) LIKE ?';
    $params[] = '%' . strtolower($_GET['type']) . '%';
}
if (isset($_GET['q']) && trim($_GET['q'])) {
    $q = trim($_GET['q']);
    $where[] = '(
        LOWER(books.title) LIKE ? OR
        LOWER(books.type) LIKE ? OR
        LOWER(authors.name) LIKE ? OR
        books.price LIKE ?
    )';
    $params[] = '%' . strtolower($q) . '%';
    $params[] = '%' . strtolower($q) . '%';
    $params[] = '%' . strtolower($q) . '%';
    $params[] = '%' . strtolower($q) . '%';
}
if (isset($_GET['pricemin']) && is_numeric($_GET['pricemin'])) {
    $where[] = 'books.price >= ?';
    $params[] = floatval($_GET['pricemin']);
}
if (isset($_GET['pricemax']) && is_numeric($_GET['pricemax'])) {
    $where[] = 'books.price <= ?';
    $params[] = floatval($_GET['pricemax']);
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$stmt = $db->prepare("SELECT books.*, authors.name AS author_name FROM books LEFT JOIN authors ON books.author_id = authors.id $where_sql ORDER BY books.title ASC");
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
$title = $pdf_mode ? 'All PDF Books' : 'All Books';

// Helper: get cover or random fallback
function getBookCover($book, $cover_urls) {
    if (!empty($book['cover']) && file_exists(__DIR__ . '/' . $book['cover'])) {
        return $book['cover'];
    }
    if ($cover_urls) {
        return $cover_urls[array_rand($cover_urls)];
    }
    return 'assets/Covers/default.jpg';
}

if (isset($_GET['id'])) {
    $book_id = intval($_GET['id']);
    $stmt = $db->prepare("SELECT books.*, authors.name AS author_name, authors.writer_id, authors.photo AS author_photo FROM books LEFT JOIN authors ON books.author_id = authors.id WHERE books.id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($book) {
        // Fetch branch-wise stock for this book
        $branch_stmt = $db->prepare("
            SELECT branches.name AS branch_name, IFNULL(book_branches.amount, 0) AS stock
            FROM branches
            LEFT JOIN book_branches ON branches.id = book_branches.branch_id AND book_branches.book_id = ?
            ORDER BY branches.name ASC
        ");
        $branch_stmt->execute([$book_id]);
        $branch_stocks = $branch_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate total stock from all branches
        $total_stock = 0;
        foreach ($branch_stocks as $bs) {
            $total_stock += (int)$bs['stock'];
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title><?= htmlspecialchars($book['title']) ?> - Book Details</title>
            <style>
                body { background:#f8f8fa; font-family:sans-serif; margin:0; }
                .header-bar {
                    background:#222; color:#fff; display:flex; align-items:center; justify-content:space-between; height:72px; padding:0 32px;
                }
                .header-left {
                    font-size:2rem; font-weight:bold; color:#ff3366; letter-spacing:2px;
                }
                .header-right {
                    text-align:right;
                }
                .go-back-btn {
                    background:#ff3366; color:#fff; border:none; padding:10px 24px; border-radius:24px; font-size:1rem; cursor:pointer; text-decoration:none;
                    font-weight:600; box-shadow:0 2px 8px #0002;
                }
                .book-detail { max-width:700px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; text-align:center; }
                .book-cover { width:140px; height:200px; object-fit:cover; border-radius:12px; background:#eee; margin-bottom:18px; box-shadow:0 2px 8px #0002; }
                .book-title { font-size:2rem; font-weight:bold; color:#ff3366; margin-bottom:8px; }
                .book-author { color:#888; font-size:1.1rem; margin-bottom:8px; }
                .book-desc { font-size:1.05rem; color:#222; margin-bottom:12px; }
                .book-meta { font-size:1rem; color:#444; margin-bottom:12px; }
                .back-btn { background:#888; color:#fff; border:none; padding:10px 24px; border-radius:24px; font-size:1rem; cursor:pointer; text-decoration:none; margin-top:24px; display:inline-block; }
                footer { background:#222; color:#fff; padding:24px 0; text-align:center; margin-top:48px; }
            </style>
        </head>
        <body>
            <div class="header-bar">
                <div class="header-left">NOVEL NEST</div>
                <div class="header-right">
                    <a href="books.php" class="go-back-btn">GO BACK</a>
                </div>
            </div>
            <div class="book-detail">
                <img src="<?= htmlspecialchars(getBookCover($book, $cover_urls)) ?>" class="book-cover">
                <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                <div class="book-author">
                    Author: 
                    <?php if ($book['author_name']): ?>
                        <a href="authors.php?author=<?= $book['author_id'] ?>" style="color:#228be6;text-decoration:underline;">
                            <?= htmlspecialchars($book['author_name']) ?>
                        </a>
                    <?php else: ?>
                        Unknown
                    <?php endif; ?>
                </div>
                <div class="book-desc"><?= nl2br(htmlspecialchars($book['description'])) ?></div>
                <div class="book-meta">
                    Type: <?= htmlspecialchars($book['type']) ?><br>
                    Price: <?= htmlspecialchars($book['price']) ?> | Stock: <?= htmlspecialchars($book['stock']) ?><br>
                    <?php if ($book['pdf_type'] === 'yes' || $book['pdf_type'] === 'both'): ?>
                        <span style="color:#228be6;font-weight:bold;">PDF Available</span>
                        <?php if (!empty($book['pdf_url'])): ?>
                            <br>
                            <a href="<?= htmlspecialchars($book['pdf_url']) ?>" target="_blank" style="background:#228be6;color:#fff;padding:8px 18px;border-radius:8px;text-decoration:none;font-weight:bold;display:inline-block;margin-top:8px;">
                                Download PDF
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:#c00;font-weight:bold;">PDF Not Available</span>
                    <?php endif; ?>
                </div>
                <h3 style="margin-top:28px;color:#228be6;">Branch-wise Stock</h3>
                <table style="margin:0 auto 18px auto;border-collapse:collapse;min-width:320px;">
                    <tr style="background:#228be6;color:#fff;">
                        <th style="padding:8px 18px;">Branch</th>
                        <th style="padding:8px 18px;">Stock</th>
                    </tr>
                    <?php foreach ($branch_stocks as $bs): ?>
                    <tr>
                        <td style="padding:8px 18px;"><?= htmlspecialchars($bs['branch_name']) ?></td>
                        <td style="padding:8px 18px;"><?= (int)$bs['stock'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="background:#f8f8fa;font-weight:bold;">
                        <td style="padding:8px 18px;">Total</td>
                        <td style="padding:8px 18px;"><?= $total_stock ?></td>
                    </tr>
                </table>
                <a href="books.php" class="back-btn">Back to Book List</a>
            </div>
            <footer>
                <div class="news-footer-bar">
                    <div class="footer-links">
                        <a href="index.php">Home</a>
                        <a href="contact.php">Contact</a>
                        <a href="about.php">About Us</a>
                        <a href="news.php">News</a>
                    </div>
                    <div style="margin-top:12px;font-size:.95rem;opacity:.8;">&copy; <?= date('Y') ?> NOVEL NEST. All rights reserved.</div>
                </div>
            </footer>
        </body>
        </html>
        <?php
        exit;
    }
}

// Group books by type
$books_by_type = [];
foreach ($books as $b) {
    $type = $b['type'] ?: 'Other';
    $books_by_type[$type][] = $b;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Books - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; font-family:sans-serif; margin:0; }
        .books-bg {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            z-index: 0;
            background: url('<?= $bg_cover ?>') center center/cover no-repeat;
            opacity: 0.13;
            filter: blur(3px) brightness(1.2);
            pointer-events: none;
        }
        header, .books-list, footer {
            position: relative;
            z-index: 1;
        }
        header, footer { background:#222; color:#fff; padding:24px 0; text-align:center; }
        .books-list { max-width:1200px; margin:40px auto; }
        .books-list h2 { text-align:center; margin-bottom:24px; }
        .book-grid { display:grid; grid-template-columns:repeat(4, 1fr); gap:32px; margin-top:32px; }
        .book-card { background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:18px; text-align:center; transition:transform .2s; display:flex; flex-direction:column; align-items:center; }
        .book-card:hover { transform:scale(1.04) rotateY(6deg); box-shadow:0 6px 24px #ff336633; }
        .book-cover { width:120px; height:170px; object-fit:cover; border-radius:10px; background:#eee; margin-bottom:14px; box-shadow:0 2px 8px #0002; }
        .book-title { font-weight:bold; font-size:1.15rem; margin-bottom:6px; color:#ff3366; }
        .book-author { color:#888; font-size:.98rem; margin-bottom:8px; }
        .book-type { font-size:.95rem; color:#555; margin-bottom:8px; }
        .book-desc { font-size:.97rem; min-height:40px; margin-bottom:8px; color:#444; }
        .book-price { color:#ff3366; font-weight:bold; margin-bottom:8px; }
        .book-stock { font-size:.95rem; color:#080; margin-bottom:8px; }
        .book-actions { margin-top:10px; }
        .book-actions button { background:#ff3366; color:#fff; border:none; padding:7px 18px; border-radius:8px; cursor:pointer; margin:0 4px; }
        .search-bar { margin-bottom:24px;display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:center; }
        .search-bar input { padding:8px 12px;border-radius:8px;border:1px solid #ccc;font-size:1rem; }
        .search-bar button { background:#ff3366; color:#fff; border:none; padding:10px 24px; border-radius:24px; font-size:1rem; cursor:pointer; }
        @media (max-width:900px) {
            .book-grid { grid-template-columns:repeat(2, 1fr); }
        }
        @media (max-width:600px) {
            .book-grid { grid-template-columns:1fr; }
            .books-list { padding:8px; }
        }
    </style>
</head>
<body>
    <div class="books-bg"></div>
    <header>
        <h1 style="font-size:2.2rem;letter-spacing:2px;font-weight:bold;color:#ff3366;text-shadow:0 2px 8px #ff336688;">
            NOVEL NEST Book Gallery
        </h1>
        <div style="font-size:1.1rem;opacity:.92;">Browse <?= htmlspecialchars($title) ?></div>
    </header>
    <div class="books-list">
        <form method="get" class="search-bar">
            <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Search by title, type, writer, price, etc.">
            <select name="type" style="padding:8px 12px;border-radius:8px;border:1px solid #ccc;font-size:1rem;">
                <option value="">-- All Types --</option>
                <?php foreach ($all_types as $type_option): ?>
                    <option value="<?= htmlspecialchars($type_option) ?>" <?= (isset($_GET['type']) && $_GET['type'] === $type_option) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type_option) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="pricemin" value="<?= htmlspecialchars($_GET['pricemin'] ?? '') ?>" placeholder="Min Price" style="width:110px;">
            <input type="number" name="pricemax" value="<?= htmlspecialchars($_GET['pricemax'] ?? '') ?>" placeholder="Max Price" style="width:110px;">
            <button type="submit">Search</button>
        </form>
        <?php if (!$books): ?>
            <div style="text-align:center;color:#888;">No books found.</div>
        <?php else: ?>
            <?php foreach ($books_by_type as $type => $type_books): ?>
                <h2 style="color:#228be6;margin-top:36px;margin-bottom:18px;font-size:1.4rem;letter-spacing:1px;">
                    <?= htmlspecialchars($type) ?> Books
                </h2>
                <div class="book-grid">
                    <?php foreach ($type_books as $b): ?>
                        <a href="books.php?id=<?= $b['id'] ?>" style="text-decoration:none;">
                            <div class="book-card">
                                <img src="<?= htmlspecialchars(getBookCover($b, $cover_urls)) ?>" alt="cover" class="book-cover">
                                <div class="book-title">
                                    <?= htmlspecialchars($b['title']) ?>
                                    <?php if ($b['pdf_type'] === 'yes' || $b['pdf_type'] === 'both'): ?>
                                        <span style="background:#ff3366;color:#fff;padding:2px 10px;border-radius:8px;font-size:.9em;margin-left:8px;">PDF</span>
                                    <?php endif; ?>
                                </div>
                                <div class="book-author"><?= htmlspecialchars($b['author_name']) ?></div>
                                <div class="book-type">Type: <?= htmlspecialchars($b['type']) ?> | Amount: <?= (int)$b['amount'] ?></div>
                                <div class="book-price">Price: $<?= number_format($b['price'],2) ?></div>
                                <div class="book-desc"><?= nl2br(htmlspecialchars($b['description'])) ?></div>
                                <div class="book-stock">Stock: <?= (int)$b['stock'] ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <footer>
        &copy; <?= date('Y') ?> NOVEL NEST. All rights reserved.
    </footer>
    <a href="index.php" style="display:inline-block;margin:24px 0 0 24px;background:#ff3366;color:#fff;font-weight:600;padding:10px 26px;border-radius:24px;text-decoration:none;box-shadow:0 2px 8px #0001;transition:background .2s;">
        &larr; Back to Home
    </a>
</body>
</html>
