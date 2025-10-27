<?php
// admin_news.php - NOVEL NEST Admin News Management
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}
$err = $msg = '';

// Generate unique news_id for new news
function generate_news_id($db) {
    $last = $db->query("SELECT news_id FROM news WHERE news_id LIKE 'NNN%' ORDER BY id DESC LIMIT 1")->fetchColumn();
    if ($last && preg_match('/NNN(\\d{6})/', $last, $m)) {
        $num = intval($m[1]) + 1;
    } else {
        $num = 1;
    }
    return 'NNN' . str_pad($num, 6, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $headline = trim($_POST['headline'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $writer = trim($_POST['writer'] ?? ($_SESSION['name'] ?? 'Admin'));
    $admin_id = $_SESSION['user_id'];
    $date = date('Y-m-d H:i');
    $news_id = generate_news_id($db);
    if (!$headline || !$content || !$writer) {
        $err = 'Please fill all fields!';
    } else {
        $stmt = $db->prepare("INSERT INTO news (news_id, title, content, date, writer, admin_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$news_id, $headline, $content, $date, $writer, $admin_id]);
        // Increase admin rating by 1
        $db->prepare("UPDATE users SET rating = COALESCE(rating,800) + 1 WHERE id = ?")->execute([$admin_id]);
        $msg = 'News added!';
    }
}
// Handle delete news (admin only)
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $db->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$delete_id]);
    $msg = 'News deleted!';
}

if (isset($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    $stmt = $db->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$delete_id]);
    $msg = 'News deleted!';
}

// News search for delete section
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
if ($search) {
    $where = "WHERE title LIKE ? OR content LIKE ? OR news_id LIKE ? OR writer LIKE ? OR date LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"];
}
$news_list = $db->prepare("SELECT * FROM news $where ORDER BY date DESC, id DESC LIMIT 20");
$news_list->execute($params);
$news_list = $news_list->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin News Management - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; }
        .news-top-bar {
            background: linear-gradient(90deg, #ff3366 0%, #ffb347 100%);
            color: #fff;
            padding: 32px 0 18px 0;
            border-radius: 0 0 32px 32px;
            box-shadow: 0 4px 24px #ff336633;
            text-align: center;
            margin-bottom: 0;
        }
        .news-top-bar h2 {
            margin: 0;
            font-size: 2.4rem;
            letter-spacing: 2px;
            font-weight: bold;
            color: #fff;
            text-shadow: 0 2px 8px #ff336688;
        }
        .admin-box { max-width:600px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        form { margin-bottom:32px; }
        input, textarea { width:100%; margin-bottom:12px; padding:10px; border-radius:8px; border:1px solid #ccc; }
        button { background:#ff3366; color:#fff; border:none; padding:12px 24px; border-radius:8px; font-size:1rem; cursor:pointer; }
        .err { color:#c00; text-align:center; }
        .msg { color:#080; text-align:center; }
        .colorbox-home {
            display:inline-block;
            background:linear-gradient(90deg,#ff3366 0,#ffb347 100%);
            color:#fff;
            padding:12px 32px;
            border-radius:12px;
            font-size:1.1rem;
            font-weight:bold;
            text-decoration:none;
            margin-top:18px;
            box-shadow:0 2px 12px #ff336633;
            border:2px solid #ff3366;
            transition:background .2s;
        }
        .colorbox-home:hover {
            background:linear-gradient(90deg,#ffb347 0,#ff3366 100%);
            color:#fff;
        }
        .news-footer-bar {
            background: linear-gradient(90deg, #18181b 0%, #ff3366 100%);
            color: #fff;
            padding: 32px 0 18px 0;
            border-radius: 32px 32px 0 0;
            box-shadow: 0 -4px 24px #ff336633;
            text-align: center;
            margin-top: 48px;
        }
        .news-footer-bar .footer-links a {
            color: #ffb347;
            margin: 0 18px;
            font-weight: bold;
            text-decoration: none;
            font-size: 1.1rem;
        }
        .news-footer-bar .footer-links a:hover {
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f4f4f8;
            color: #333;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .admin-news-detail {
            max-width: 750px;
            margin: 32px auto;
            background: linear-gradient(120deg, #fffbe6 80%, #ffb347 100%);
            border-radius: 22px;
            box-shadow: 0 6px 32px #ff336644;
            border: 2.5px solid #ff3366;
            padding: 36px 32px 28px 32px;
            position: relative;
            text-align: center;
        }
        .admin-news-detail .news-content {
            text-align: left;
        }
        .edit-news-btn {
            background: #228be6;
            color: #fff;
            border: none;
            padding: 10px 28px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            margin-left: 12px;
            margin-bottom: 8px;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .edit-news-btn:hover {
            background: #1864ab;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="news-top-bar">
        <h2>Post News</h2>
        <div style="font-size:1.1rem;opacity:.85;">Share the latest updates with NOVEL NEST</div>
    </div>
    <div class="admin-box">
        <div style="text-align:center;margin-bottom:18px;color:#228be6;font-weight:bold;">
            If you post a news, your rating will be increased by one.
        </div>
        <?php if ($err) echo "<div class='err'>$err</div>"; ?>
        <?php if ($msg) echo "<div class='msg'>$msg</div>"; ?>
        <form method="post">
            <input type="text" name="headline" placeholder="Headline (Title)" required>
            <input type="text" name="writer" placeholder="Writer Name" value="" required>
            <textarea name="content" placeholder="Detailed News" rows="6" required></textarea>
            <button type="submit">Post News</button>
        </form>
        <hr style="margin:32px 0;">
        <h3 style="text-align:center;color:#ff3366;">Delete News</h3>
        <form method="get" style="max-width:400px;margin:0 auto 24px auto;display:flex;gap:8px;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search news by title, content, News ID, or writer...">
            <button type="submit">Search</button>
        </form>
        <?php
        // Show selected news details at the top after search bar
        if (isset($_GET['news_id']) && is_numeric($_GET['news_id'])) {
            $news_id = intval($_GET['news_id']);
            $stmt = $db->prepare("SELECT * FROM news WHERE id = ?");
            $stmt->execute([$news_id]);
            $news = $stmt->fetch(PDO::FETCH_ASSOC);

            $up_count = $db->query("SELECT COUNT(*) FROM news_reacts WHERE news_id = $news_id AND react_type = 'up'")->fetchColumn();
            $down_count = $db->query("SELECT COUNT(*) FROM news_reacts WHERE news_id = $news_id AND react_type = 'down'")->fetchColumn();

            // Fetch comments
            $comments = $db->prepare("SELECT * FROM news_comments WHERE news_id = ? AND parent_id IS NULL ORDER BY created_at DESC");
            $comments->execute([$news_id]);
            $comments = $comments->fetchAll(PDO::FETCH_ASSOC);

            $comment_count = $db->prepare("SELECT COUNT(*) FROM news_comments WHERE news_id = ?");
            $comment_count->execute([$news_id]);
            $comment_count = $comment_count->fetchColumn();
        ?>
        <div class="admin-news-detail">
            <div style="font-size:2rem;font-weight:800;color:#ff3366;letter-spacing:1px;margin-bottom:8px;">
                <?= htmlspecialchars($news['title']) ?>
            </div>
            <div style="font-size:1.1rem;color:#228be6;font-weight:600;margin-bottom:8px;">
                #<?= htmlspecialchars($news['news_id']) ?>
            </div>
            <div style="margin-bottom:10px;">
                <span style="background:#ff3366;color:#fff;padding:6px 18px;border-radius:8px;font-weight:bold;display:inline-block;margin:2px;">
                    Writer: <?= htmlspecialchars($news['writer']) ?>
                </span>
                <span style="background:#18181b;color:#fff;padding:6px 18px;border-radius:8px;font-weight:bold;display:inline-block;margin:2px;">
                    Admin ID: <?= htmlspecialchars($news['admin_id']) ?>
                </span>
                <span style="background:#ffb347;color:#18181b;padding:6px 18px;border-radius:8px;font-weight:bold;display:inline-block;margin:2px;">
                    <?= date('M d, Y H:i', strtotime($news['date'])) ?>
                </span>
                <a href="admin_news_edit.php?news_id=<?= $news['id'] ?>" class="edit-news-btn">Edit News</a>
            </div>
            <div class="news-content" style="font-size:1.13rem;line-height:1.7;color:#222;background:#fff;border-radius:12px;padding:18px 14px;margin:18px 0 22px 0;box-shadow:0 2px 8px #ff336611;">
                <?= nl2br(htmlspecialchars($news['content'])) ?>
            </div>
            <div style="display:flex;justify-content:center;gap:18px;margin-bottom:18px;">
                <span style="background:#ff3366;color:#fff;padding:6px 18px;border-radius:8px;font-weight:bold;">
                    👍 Up: <?= $up_count ?>
                </span>
                <span style="background:#18181b;color:#fff;padding:6px 18px;border-radius:8px;font-weight:bold;">
                    👎 Down: <?= $down_count ?>
                </span>
                <span style="background:#ffb347;color:#18181b;padding:6px 18px;border-radius:8px;font-weight:bold;">
                    💬 Comments: <?= $comment_count ?>
                </span>
            </div>
            <div style="text-align:left;">
                <h4 style="color:#ff3366;margin-bottom:10px;">Comments</h4>
                <?php foreach ($comments as $c): ?>
                    <div style="background:#f8f8fa;border-radius:8px;padding:10px 14px;margin-bottom:10px;">
                        <b>User #<?= $c['user_id'] ?>:</b> <?= htmlspecialchars($c['comment']) ?>
                        <?php
                        $replies = $db->prepare("SELECT * FROM news_comments WHERE parent_id = ? ORDER BY created_at ASC");
                        $replies->execute([$c['id']]);
                        foreach ($replies->fetchAll(PDO::FETCH_ASSOC) as $r):
                        ?>
                            <div style="background:#fff;border-radius:6px;padding:8px 12px;margin:6px 0 0 18px;">
                                <b>Reply #<?= $r['user_id'] ?>:</b> <?= htmlspecialchars($r['comment']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <hr style="margin:32px 0;">
        <?php } ?>
        <table>
            <tr><th>News ID</th><th>Title</th><th>Date</th><th>Writer</th><th>Admin ID</th><th>Action</th></tr>
            <?php foreach ($news_list as $n): ?>
            <tr>
                <td>
                    <a href="admin_news.php?news_id=<?= $n['id'] ?>" style="font-size:.95rem;background:#ff3366;color:#fff;padding:2px 10px;border-radius:8px;font-weight:bold;letter-spacing:1px;min-width:90px;display:inline-block;text-decoration:none;">
                        <?= htmlspecialchars($n['news_id']) ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($n['title']) ?></td>
                <td>
                    <?php
                    $date_str = $n['date'] ?? '';
                    $date = $time = '';
                    if ($date_str && preg_match('/^(\d{4}-\d{2}-\d{2})[ T](\d{2}:\d{2})/', $date_str, $m)) {
                        $date = $m[1];
                        $time = $m[2];
                        $dt = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time, new DateTimeZone('UTC'));
                        if ($dt) {
                            $dt->setTimezone(new DateTimeZone('Asia/Dhaka'));
                            $date = $dt->format('Y-m-d');
                            $time = $dt->format('H:i');
                        }
                    } else if ($date_str) {
                        $date = $date_str;
                    }
                    ?>
                    <?= htmlspecialchars($date) ?><br><span style="color:#888;font-size:.95em;"><?= htmlspecialchars($time) ?> <span style="font-size:.9em;">GMT+6</span></span>
                </td>
                <td><?= htmlspecialchars($n['writer']) ?></td>
                <td><?= htmlspecialchars($n['admin_id']) ?></td>
                <td>
                    <form method="post" action="admin_news.php" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?= $n['id'] ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this news?')" style="color:#fff;background:#c00;font-weight:bold;padding:6px 16px;border-radius:6px;border:none;cursor:pointer;">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($news_list) === 0): ?>
            <tr><td colspan="6" style="text-align:center;color:#888;">No news found.</td></tr>
            <?php endif; ?>
        </table>
        <div style="text-align:center;margin-top:12px;">
            <a href="admin.php" class="colorbox-home">&larr; Back to Dashboard</a>
        </div>
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
