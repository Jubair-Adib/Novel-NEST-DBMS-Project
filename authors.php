<?php
include_once 'includes/db_connect.php';

// Handle search
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE name LIKE ? OR writer_id LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$authors = $db->prepare("SELECT * FROM authors $where ORDER BY name ASC");
$authors->execute($params);
$authors = $authors->fetchAll(PDO::FETCH_ASSOC);

// Author details view
if (isset($_GET['author'])) {
    $author_id = intval($_GET['author']);
    $author = $db->prepare("SELECT * FROM authors WHERE id = ?");
    $author->execute([$author_id]);
    $author = $author->fetch(PDO::FETCH_ASSOC);

    if ($author) {
        $books = $db->prepare("SELECT id, title FROM books WHERE author_id = ? ORDER BY title ASC");
        $books->execute([$author_id]);
        $books = $books->fetchAll(PDO::FETCH_ASSOC);

        $book_count = count($books);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title><?= htmlspecialchars($author['name']) ?> - Author Details</title>
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
                .author-detail { max-width:700px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; text-align:center; }
                .author-photo-big { width:140px; height:140px; object-fit:cover; border-radius:50%; background:#eee; margin-bottom:18px; box-shadow:0 2px 8px #0002; }
                .author-name { font-size:2rem; font-weight:bold; color:#ff3366; margin-bottom:8px; }
                .author-id { color:#888; font-size:1.1rem; margin-bottom:8px; }
                .author-bio { font-size:1.05rem; color:#222; margin-bottom:12px; }
                .author-gmail { font-size:1rem; color:#444; margin-bottom:12px; }
                .author-books-list { margin-top:18px; }
                .author-books-list h3 { color:#ff3366; margin-bottom:8px; }
                .book-link { display:inline-block; margin:4px 8px; padding:6px 14px; background:#ff3366; color:#fff; border-radius:12px; text-decoration:none; font-size:1rem; transition:background .2s; }
                .book-link:hover { background:#d92c5c; }
                .back-btn { background:#888; color:#fff; border:none; padding:10px 24px; border-radius:24px; font-size:1rem; cursor:pointer; text-decoration:none; margin-top:24px; display:inline-block; }
                footer { background:#222; color:#fff; padding:24px 0; text-align:center; margin-top:48px; }
                .author-bg {
                    position: fixed;
                    top: 0; left: 0; width: 100vw; height: 100vh;
                    z-index: 0;
                    background: url('<?= $author['photo'] ? htmlspecialchars($author['photo']) : 'assets/Author/' . (($author['id'] - 1) % 75 + 1) . '.png' ?>') center center/cover no-repeat;
                    opacity: 1;
                    filter: blur(2px);
                    pointer-events: none;
                    background-attachment: fixed;
                    background-position: center 0px;
                    transition: background-position 0.2s;
                }
                .author-detail, .header-bar, footer {
                    position: relative;
                    z-index: 1;
                }
                @media (max-width:700px) {
                    .header-bar { flex-direction:column; height:auto; padding:12px 0; }
                    .header-left, .header-right { padding:0; }
                    .header-left { font-size:1.3rem; }
                }
            </style>
        </head>
        <body>
            <div class="author-bg"></div>
            <div class="header-bar">
                <div class="header-left">NOVEL NEST</div>
                <div class="header-right">
                    <a href="authors.php" class="go-back-btn">GO BACK</a>
                </div>
            </div>
            <div class="author-detail">
                <img src="<?= $author['photo'] ? htmlspecialchars($author['photo']) : 'assets/Author/' . (($author['id'] - 1) % 75 + 1) . '.png' ?>" class="author-photo-big">
                <div class="author-name"><?= htmlspecialchars($author['name']) ?></div>
                <div class="author-id">ID: <?= htmlspecialchars($author['writer_id']) ?></div>
                <div class="author-bio"><?= nl2br(htmlspecialchars($author['bio'])) ?></div>
                <div class="author-gmail">Gmail: <?= htmlspecialchars($author['gmail']) ?></div>
                <div class="author-books-list">
                    <h3><?= $book_count ?> Book(s):</h3>
                    <?php foreach ($books as $book): ?>
                        <a href="books.php?id=<?= $book['id'] ?>" class="book-link"><?= htmlspecialchars($book['title']) ?></a>
                    <?php endforeach; ?>
                </div>
                <a href="authors.php" class="back-btn">Back to Author List</a>
            </div>
            <footer>
                &copy; <?= date('Y') ?> NOVEL NEST. All rights reserved.
            </footer>
            <script>
            document.addEventListener('scroll', function() {
                var scrolled = window.scrollY;
                var bg = document.querySelector('.author-bg');
                if(bg) {
                    // Move background image in reverse direction as you scroll
                    bg.style.backgroundPosition = 'center ' + (-scrolled * 0.5) + 'px';
                }
            });
            </script>
        </body>
        </html>
        <?php
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Authors - NOVEL NEST</title>
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
        .author-list { max-width:900px; margin:40px auto; }
        .search-bar { margin-bottom:24px; text-align:center; }
        .search-bar input { padding:8px 12px; border-radius:8px; border:1px solid #ccc; font-size:1rem; }
        .search-bar button { background:#ff3366; color:#fff; border:none; padding:10px 24px; border-radius:24px; font-size:1rem; cursor:pointer; }
        .author-row { display:flex;align-items:center;gap:24px;background:#fff;border-radius:12px;box-shadow:0 2px 8px #0001;padding:18px;margin-bottom:18px;cursor:pointer;transition:box-shadow .2s, background .2s, color .2s; }
        .author-row:hover { box-shadow:0 6px 24px #ff336633; background:#ff3366; color:#fff; }
        .author-row:hover .author-name,
        .author-row:hover .author-id,
        .author-row:hover .author-books { color:#fff; }
        .author-photo { width:60px; height:60px; object-fit:cover; border-radius:50%; background:#eee; }
        .author-info { flex:1; }
        .author-name { font-size:1.2rem; font-weight:bold; color:#ff3366; transition:color .2s; }
        .author-id { color:#888; font-size:.98rem; transition:color .2s; }
        .author-books { font-size:1rem; color:#222; transition:color .2s; }
        .list-title { text-align:center; font-size:1.6rem; font-weight:bold; color:#222; margin-top:18px; margin-bottom:24px; letter-spacing:1px;}
        .author-bg { position:fixed; top:0; left:0; width:100%; height:100%; background:#f8f8fa; z-index:-1; }
        footer {
            background:#18181b;
            color:#ff3366;
            padding:32px 0 18px 0;
            text-align:center;
            font-size:1.1rem;
            font-weight:bold;
            letter-spacing:1px;
            box-shadow:0 -2px 8px #0002;
            margin-top:48px;
        }
        @media (max-width:700px) {
            .header-bar { flex-direction:column; height:auto; padding:12px 0; }
            .header-left, .header-right { padding:0; }
            .header-left { font-size:1.3rem; }
        }
    </style>
</head>
<body>
    <div class="author-bg"></div>
    <div class="header-bar">
        <div class="header-left">NOVEL NEST</div>
        <div class="header-right">
            <a href="index.php" class="go-back-btn">GO BACK</a>
        </div>
    </div>
    <div class="list-title">List of Author</div>
    <div class="author-list">
        <form method="get" class="search-bar">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or writer ID...">
            <button type="submit">Search</button>
        </form>
        <?php foreach ($authors as $a): ?>
            <?php
            $book_count = $db->prepare("SELECT COUNT(*) FROM books WHERE author_id = ?");
            $book_count->execute([$a['id']]);
            $count = $book_count->fetchColumn();
            ?>
            <div class="author-row" onclick="window.location.href='authors.php?author=<?= $a['id'] ?>'">
                <img src="<?= $a['photo'] ? htmlspecialchars($a['photo']) : 'assets/Author/' . (($a['id'] - 1) % 75 + 1) . '.png' ?>" class="author-photo">
                <div class="author-info">
                    <div class="author-name"><?= htmlspecialchars($a['name']) ?></div>
                    <div class="author-id">ID: <?= htmlspecialchars($a['writer_id']) ?></div>
                </div>
                <div class="author-books"><?= $count ?> Book(s)</div>
            </div>
        <?php endforeach; ?>
    </div>
    <footer>        
            <div class="news-footer-bar">
        <div class="footer-links">
            <a href="index.php">Home</a>
            <a href="contact.php">Contact</a>
            <a href="about.php">About Us</a>
            <a href="news.php">News</a>
        </div>
        <div style="margin-top:12px;font-size:.95rem;opacity:.8;">&copy; 2025 NOVEL NEST. All rights reserved.</div>
    </div>
    </footer>
    <script>
        document.addEventListener('scroll', function() {
            var scrolled = window.scrollY;
            var bg = document.querySelector('.author-bg');
            if(bg) {
                // Move background image in reverse direction as you scroll
                bg.style.backgroundPosition = 'center ' + (-scrolled * 0.5) + 'px';
            }
        });
    </script>
</body>                 
    <script>
    document.addEventListener('scroll', function() {
        var scrolled = window.scrollY;
        var bg = document.querySelector('.author-bg');
        if(bg) {
            // Move background image in reverse direction as you scroll
            bg.style.backgroundPosition = 'center ' + (-scrolled * 0.5) + 'px';
        }
    });
    </script>
</body>
</html>
