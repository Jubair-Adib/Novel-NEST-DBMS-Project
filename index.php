<?php
// index.php - NOVEL NEST Landing Page
include_once 'includes/db_connect.php';
session_start();
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = $is_logged_in && ($_SESSION['role'] === 'admin');
$profile_photo = 'assets/default-profile.png';
if ($is_logged_in) {
    $stmt = $db->prepare("SELECT photo FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $profile_photo = $stmt->fetchColumn() ?: $profile_photo;
    $_SESSION['photo'] = $profile_photo;
}

// Fetch counters
$user_count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$book_count = $db->query("SELECT COUNT(*) FROM books")->fetchColumn();
$sale_count = $db->query("SELECT COUNT(*) FROM buy")->fetchColumn();
$news_count = $db->query("SELECT COUNT(*) FROM news")->fetchColumn();
$writer_count = $db->query("SELECT COUNT(*) FROM authors")->fetchColumn();
$stmt = $db->query("SELECT COUNT(*) FROM books WHERE pdf_type = 'yes' OR pdf_type = 'both'");
$pdf_count = $stmt->fetchColumn();
$type_counts = $db->query("SELECT type, COUNT(*) as cnt FROM books GROUP BY type ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            margin: 0;
            font-family: sans-serif;
            background: #f8f8fa;
            position: relative;
        }
        /* .index-bg {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            z-index: 0;
            pointer-events: none;
        }
        .index-bg::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0.1; 
            background-image: url('assets/index/222.jpeg');
            background-size: cover;
            background-position: center;
        }
        .index-bg::after { display: none; } */
        header, .news-top-bar, .footer, .news-footer-bar, .books-list, .carousel, .profile-container {
            position: relative;
            z-index: 1;
        }
        .header { display:flex; justify-content:space-between; align-items:center; padding:20px 40px; background:#222; color:#fff; }
        .logo { display:flex; align-items:center; }
        .logo img { height:48px; margin-right:16px; }
        .login-btn { background:#ff3366; color:#fff; border:none; padding:10px 24px; border-radius:24px; font-size:1rem; cursor:pointer; }
        .carousel { margin:40px auto; max-width:1200px; }
        .carousel-title { font-size:2rem; margin-bottom:16px; }
        .carousel-row { display:flex; gap:24px; overflow-x:auto; }
        .carousel-item { min-width:200px; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:16px; text-align:center; transition:transform .2s; }
        .carousel-item:hover { transform:scale(1.05) rotateY(8deg); }
        .footer { background:#222; color:#fff; padding:32px 0 16px 0; text-align:center; }
        .footer .socials a { margin:0 12px; color:#fff; font-size:1.5rem; text-decoration:none; }
        .footer .links { margin:16px 0; }
        .footer .links a { margin:0 10px; color:#ff3366; text-decoration:none; }
        .footer .about { margin-top:16px; }
        .news-top-bar {
            background: #ff3366;
            color: #fff;
            padding: 16px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .news-top-bar h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: bold;
        }
        .news-top-bar div {
            margin-top: 8px;
            font-size: 1.1rem;
            opacity: .85;
        }
        .news-footer-bar {
            background: #18181b;
            color: #fff;
            padding: 24px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 24px;
            flex-wrap: wrap;
        }
        .footer-links a {
            color: #ff3366;
            text-decoration: none;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="index-bg"></div>
    <div class="index-bg"></div>
    <div class="index-bg"></div>
    <div class="header">
        <div class="logo">
            <img src="assets/logo.png" alt="Logo" onerror="this.style.display='none'">
            <span style="font-size:2rem;font-weight:bold;letter-spacing:2px;">NOVEL NEST</span>
        </div>
        <?php if ($is_admin): ?>
            <div style="display:flex;align-items:center;gap:24px;">
                <a href="profile.php" title="Profile">
                    <img src="<?= htmlspecialchars($profile_photo) ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover;background:#eee;vertical-align:middle;">
                </a>
                <span style="color:#ff3366;font-weight:bold;">
                    WELCOME admin <?= htmlspecialchars($_SESSION['name'] ?? '') ?>
                </span>
                <a href="admin.php" class="login-btn" style="background:#ff3366;min-width:140px;text-decoration:none;box-shadow:0 2px 8px #ff336633;display:flex;align-items:center;gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" style="vertical-align:middle;"><path fill="#fff" d="M12 15.5A3.5 3.5 0 1 0 12 8.5a3.5 3.5 0 0 0 0 7Zm8.94-2.32-1.66-1.1a7.03 7.03 0 0 0 0-2.16l1.66-1.1a1 1 0 0 0 .33-1.33l-2-3.46a1 1 0 0 0-1.25-.46l-1.96.79a7.03 7.03 0 0 0-1.87-1.08l-.3-2.08A1 1 0 0 0 13 2h-4a1 1 0 0 0-1 .84l-.3 2.08a7.03 7.03 0 0 0-1.87 1.08l-1.96-.79a1 1 0 0 0-1.25.46l-2 3.46a1 1 0 0 0 .33 1.33l1.66 1.1a7.03 7.03 0 0 0 0 2.16l-1.66 1.1a1 1 0 0 0-.33 1.33l2 3.46a1 1 0 0 0 1.25.46l1.96-.79a7.03 7.03 0 0 0 1.87 1.08l.3 2.08A1 1 0 0 0 9 22h4a1 1 0 0 0 1-.84l.3-2.08a7.03 7.03 0 0 0 1.87-1.08l1.96.79a1 1 0 0 0 1.25-.46l2-3.46a1 1 0 0 0-.33-1.33ZM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10Z"/></svg>
                    Admin Dashboard
                </a>
                <a href="logout.php" class="login-btn" style="background:#c00;min-width:100px;text-decoration:none;box-shadow:0 2px 8px #ff336633;display:flex;align-items:center;gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" style="vertical-align:middle;"><path fill="#fff" d="M16 13v-2H7V8l-5 4 5 4v-3h9Zm3-10H5a2 2 0 0 0-2 2v6h2V5h14v14H5v-6H3v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Z"/></svg>
                    Logout
                </a>
            </div>
        <?php elseif ($is_logged_in): ?>
            <div style="display:flex;align-items:center;gap:16px;">
                <a href="profile.php" title="Profile">
                    <img src="<?= htmlspecialchars($profile_photo) ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover;background:#eee;vertical-align:middle;">
                </a>
                <span style="color:#ff3366;font-weight:bold;">
                    WELCOME user <?= htmlspecialchars($_SESSION['name'] ?? '') ?>
                </span>
                <a href="logout.php" class="login-btn" style="background:#444;">Logout</a>
            </div>
        <?php else: ?>
            <button class="login-btn" onclick="window.location.href='login.php'" style="margin-right:0;display:flex;align-items:center;gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" style="vertical-align:middle;"><path fill="#fff" d="M10.09 15.59 8.67 17l-5-5 5-5 1.41 1.41L6.83 11H20v2H6.83l3.26 3.59ZM20 19H4v-2h16v2Zm0-14v2H4V5h16Z"/></svg>
                Login
            </button>
        <?php endif; ?>
    </div>

    <div class="news-top-bar">
        <h1>Welcome to NOVEL NEST</h1>
        <div style="font-size:1.1rem;opacity:.85;">A modern library management portal</div>
    </div>
    <div style="text-align:center;margin:32px 0;max-width:1100px;margin-left:auto;margin-right:auto;">
        <!-- Counters Section -->
        <div style="display:flex;justify-content:center;gap:38px;margin-bottom:32px;flex-wrap:wrap;">
            <div
                style="background:#18181b;color:#ff3366;padding:24px 38px;border-radius:18px;box-shadow:0 2px 8px #0002;min-width:180px;<?php if ($is_admin): ?>cursor:pointer;<?php endif; ?>"
                <?php if ($is_admin): ?>onclick="window.location.href='users.php'"<?php endif; ?>
            >
                <div style="font-size:2.2rem;font-weight:bold;"><?= $user_count ?></div>
                <div style="font-size:1.1rem;">Total Users</div>
            </div>
            <div
                style="background:#18181b;color:#ff3366;padding:24px 38px;border-radius:18px;box-shadow:0 2px 8px #0002;min-width:180px;cursor:pointer;"
                onclick="window.location.href='books.php'"
            >
                <div style="font-size:2.2rem;font-weight:bold;"><?= $book_count ?></div>
                <div style="font-size:1.1rem;">Total Books</div>
            </div>
            <div
                style="background:#18181b;color:#ff3366;padding:24px 38px;border-radius:18px;box-shadow:0 2px 8px #0002;min-width:180px;<?php if ($is_admin): ?>cursor:pointer;<?php endif; ?>"
                <?php if ($is_admin): ?>onclick="window.location.href='sales.php'"<?php endif; ?>
            >
                <div style="font-size:2.2rem;font-weight:bold;"><?= $sale_count ?></div>
                <div style="font-size:1.1rem;">Books Sold</div>
            </div>
            <div
                style="background:#18181b;color:#ff3366;padding:24px 38px;border-radius:18px;box-shadow:0 2px 8px #0002;min-width:180px;cursor:pointer;"
                onclick="window.location.href='news.php'"
            >
                <div style="font-size:2.2rem;font-weight:bold;"><?= $news_count ?></div>
                <div style="font-size:1.1rem;">Total News</div>
            </div>
            <div
                style="background:#18181b;color:#ff3366;padding:24px 38px;border-radius:18px;box-shadow:0 2px 8px #0002;min-width:180px;cursor:pointer;"
                onclick="window.location.href='authors.php'"
            >
                <div style="font-size:2.2rem;font-weight:bold;"><?= $writer_count ?></div>
                <div style="font-size:1.1rem;">Total Authors</div>
            </div>
            <div
                style="background:#18181b;color:#ff3366;padding:24px 38px;border-radius:18px;box-shadow:0 2px 8px #0002;min-width:180px;cursor:pointer;"
                onclick="window.location.href='books.php?pdf=1'"
            >
                <div style="font-size:2.2rem;font-weight:bold;"><?= $pdf_count ?></div>
                <div style="font-size:1.1rem;">PDF Books</div>
            </div>
        </div>
        <!-- Book Types Counter -->
        <div style="max-width:700px;margin:0 auto 32px auto;background:#fff;border-radius:16px;box-shadow:0 2px 8px #0001;padding:18px 0;">
            <div style="font-size:1.2rem;font-weight:bold;color:#222;margin-bottom:10px;">Books by Type</div>
            <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:18px;">
                <?php foreach ($type_counts as $type): ?>
                    <div style="background:#ff3366;color:#fff;padding:10px 22px;border-radius:12px;font-size:1rem;min-width:120px;">
                        <?= htmlspecialchars($type['type']) ?>: <b><?= $type['cnt'] ?></b>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- <a href="books.php?book=<?= $book['id'] ?>" class="book-link"><?= htmlspecialchars($book['title']) ?></a> -->
    </div>
    <div class="news-footer-bar">
        <div class="footer-links">
            <a href="index.php">Home</a>
            <a href="contact.php">Contact</a>
            <a href="about.php">About Us</a>
            <a href="news.php">News</a>
        </div>
        <div style="margin-top:12px;font-size:.95rem;opacity:.8;">&copy; 2025 NOVEL NEST. All rights reserved.</div>
    </div>
</body>
</html>