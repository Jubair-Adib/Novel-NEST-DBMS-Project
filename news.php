<?php
session_start();
include_once 'includes/db_connect.php';

// Search logic
$search = trim($_GET['q'] ?? '');
$where = '';
$params = [];
if ($search) {
    $where = "WHERE title LIKE ? OR content LIKE ? OR date LIKE ? OR writer LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

// Detail view logic
$selected_news = null;
$selected_id = isset($_GET['id']) ? intval($_GET['id']) : null;
if ($selected_id) {
    $stmt_detail = $db->prepare("SELECT * FROM news WHERE id = ?");
    $stmt_detail->execute([$selected_id]);
    $selected_news = $stmt_detail->fetch(PDO::FETCH_ASSOC);
}

// Count up/down reacts
$up_count = $db->prepare("SELECT COUNT(*) FROM news_reacts WHERE news_id = ? AND react_type = 'up'");
$up_count->execute([$selected_id]);
$up_count = $up_count->fetchColumn();

$down_count = $db->prepare("SELECT COUNT(*) FROM news_reacts WHERE news_id = ? AND react_type = 'down'");
$down_count->execute([$selected_id]);
$down_count = $down_count->fetchColumn();

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_SESSION['user_id']) && $selected_id) {
    $comment = trim($_POST['comment']);
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    if ($comment) {
        $stmt = $db->prepare("INSERT INTO news_comments (news_id, user_id, comment, parent_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$selected_id, $_SESSION['user_id'], $comment, $parent_id]);
    }
    header("Location: news.php?id=$selected_id");
    exit;
}

// Handle up/down vote POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['react_type'], $_SESSION['user_id'])) {
    $react_type = $_POST['react_type'];
    $news_id = isset($_GET['id']) ? intval($_GET['id']) : null;
    if ($news_id) {
        // Check if user already reacted
        $stmt = $db->prepare("SELECT id, react_type FROM news_reacts WHERE news_id = ? AND user_id = ?");
        $stmt->execute([$news_id, $_SESSION['user_id']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($react_type === 'remove') {
            // De-react (remove react)
            if ($existing) {
                $stmt = $db->prepare("DELETE FROM news_reacts WHERE id = ?");
                $stmt->execute([$existing['id']]);
            }
        } else {
            // Add or update react
            if ($existing) {
                if ($existing['react_type'] !== $react_type) {
                    $stmt = $db->prepare("UPDATE news_reacts SET react_type = ? WHERE id = ?");
                    $stmt->execute([$react_type, $existing['id']]);
                }
            } else {
                $stmt = $db->prepare("INSERT INTO news_reacts (news_id, user_id, react_type) VALUES (?, ?, ?)");
                $stmt->execute([$news_id, $_SESSION['user_id'], $react_type]);
            }
        }
        header("Location: news.php?id=$news_id");
        exit;
    }
}

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;
$total_news = $db->prepare("SELECT COUNT(*) FROM news $where");
$total_news->execute($params);
$total_news = $total_news->fetchColumn();
$total_pages = ceil($total_news / $per_page);

// Fetch news for current page (exclude selected if present)
$news_query = "SELECT * FROM news $where ";
if ($selected_id) {
    $news_query .= ($where ? " AND " : "WHERE ") . "id != ? ";
    $params_with_id = $params;
    $params_with_id[] = $selected_id;
} else {
    $params_with_id = $params;
}
$news_query .= "ORDER BY date DESC, id DESC LIMIT ? OFFSET ?";
$stmt = $db->prepare($news_query);
foreach ($params_with_id as $i => $p) $stmt->bindValue($i+1, $p, PDO::PARAM_STR);
$stmt->bindValue(count($params_with_id)+1, $per_page, PDO::PARAM_INT);
$stmt->bindValue(count($params_with_id)+2, $offset, PDO::PARAM_INT);
$stmt->execute();
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOVEL NEST</title>
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
        .news-container { max-width:800px; margin:0 auto 40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        .news-item { background:linear-gradient(90deg,#ff3366 0,#18181b 100%); color:#fff; border-radius:16px; box-shadow:0 2px 8px #0002; margin-bottom:22px; padding:22px 28px; transition:box-shadow .2s; border:2px solid #ff3366; }
        .news-item:hover { box-shadow:0 6px 24px #ff336666; }
        .news-title { font-size:1.2rem; font-weight:bold; color:#fff; }
        .news-date { color:#ffd6e0; font-size:.95rem; margin-bottom:6px; }
        .news-content { color:#fff; margin-top:6px; }
        .news-detail { font-size:1.3rem; background:linear-gradient(90deg,#18181b 0,#ff3366 100%); border:3px solid #ff3366; box-shadow:0 6px 24px #ff336666; margin-bottom:32px; padding:32px 36px; }
        .news-detail .news-title { font-size:1.5rem; }
        .news-detail .news-content { font-size:1.1rem; }
        .back-link { display:inline-block; margin-bottom:18px; background:#ff3366; color:#fff; padding:8px 18px; border-radius:8px; text-decoration:none; font-weight:bold; }
        .pagination { display:flex; justify-content:center; gap:8px; margin:32px 0 0 0; }
        .pagination a, .pagination span { padding:8px 14px; border-radius:6px; background:#222; color:#fff; text-decoration:none; font-weight:600; }
        .pagination .active { background:#ff3366; }
        .pagination .disabled { background:#888; color:#fff; pointer-events:none; }
        .search-bar { max-width:400px; margin:0 auto 32px auto; display:flex; gap:0; }
        .search-bar input { flex:1; padding:10px 14px; border-radius:8px 0 0 8px; border:1px solid #ccc; font-size:1rem; }
        .search-bar button { background:#ff3366; color:#fff; border:none; padding:10px 24px; border-radius:0 8px 8px 0; font-size:1rem; cursor:pointer; }
        @media (max-width:900px) { .news-container { padding:10px; } }
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
    </style>
</head>
<body>
    <div class="news-top-bar">
        <h2>Library News</h2>
        <div style="font-size:1.1rem;opacity:.85;">Stay updated with the latest from NOVEL NEST</div>
    </div>
    <div class="news-container">
        <form method="get" class="search-bar">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search news by title, content, or date...">
            <button type="submit">Search</button>
        </form>
        <?php if ($selected_news): ?>
            <div class="news-detail">
                <?php
                $back_link = 'news.php';
                if ($search) {
                    $back_link .= '?q=' . urlencode($search);
                    if (isset($_GET['page'])) {
                        $back_link .= '&page=' . intval($_GET['page']);
                    }
                } elseif (isset($_GET['page'])) {
                    $back_link .= '?page=' . intval($_GET['page']);
                }
                ?>
                <a href="<?= $back_link ?>" class="back-link">&larr; Back to News List</a>
                <div class="news-title"><?= htmlspecialchars($selected_news['title']) ?></div>
                <?php
                // Format date and time for detail view
                $date_str = $selected_news['date'] ?? '';
                $date = $time = '';
                if ($date_str && preg_match('/^(\d{4}-\d{2}-\d{2})[ T](\d{2}:\d{2})/', $date_str, $m)) {
                    $date = $m[1];
                    $time = $m[2];
                    // Convert to GMT+6 (Bangladesh Time)
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
                <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                    <div class="news-date">
                        Date: <?= htmlspecialchars($date) ?> | Time: <span><?= htmlspecialchars($time) ?> <span style="font-size:.9em;">GMT+6</span></span><?php if (!empty($selected_news['news_id'])): ?>
                            | <span style="font-size:.95rem;background:#ff3366;color:#fff;padding:2px 10px;border-radius:8px;font-weight:bold;letter-spacing:1px;min-width:90px;display:inline-block;">ID: <?= htmlspecialchars($selected_news['news_id']) ?></span>
                        <?php endif; ?>
                        | Writer: <?= htmlspecialchars($selected_news['writer']) ?>
                    </div>
                </div>
                <div class="news-content"><?= nl2br(htmlspecialchars($selected_news['content'])) ?></div>
                <?php
                $user_react = null;
                if (isset($_SESSION['user_id']) && $selected_id) {
                    $stmt = $db->prepare("SELECT react_type FROM news_reacts WHERE news_id = ? AND user_id = ?");
                    $stmt->execute([$selected_id, $_SESSION['user_id']]);
                    $user_react = $stmt->fetchColumn();
                }
                ?>
                                <div style="margin:18px 0;display:flex;gap:12px;align-items:center;">
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="react_type" value="up">
                                        <button type="submit" style="background:<?= $user_react==='up'?'#ff3366':'#fff' ?>;color:<?= $user_react==='up'?'#fff':'#ff3366' ?>;padding:6px 18px;border-radius:8px;font-weight:bold;border:2px solid #ff3366;">
                                            👍 Up (<?= $up_count ?>)
                                        </button>
                                    </form>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="react_type" value="down">
                                        <button type="submit" style="background:<?= $user_react==='down'?'#18181b':'#fff' ?>;color:<?= $user_react==='down'?'#fff':'#18181b' ?>;padding:6px 18px;border-radius:8px;font-weight:bold;border:2px solid #18181b;">
                                            👎 Down (<?= $down_count ?>)
                                        </button>
                                    </form>
                                    <?php if ($user_react): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="react_type" value="remove">
                                        <button type="submit" style="background:#888;color:#fff;padding:6px 18px;border-radius:8px;font-weight:bold;border:2px solid #888;">
                                            Remove React
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    
                                </div>
                <?php
                // Fetch comments
                $stmt = $db->prepare("SELECT * FROM news_comments WHERE news_id = ? AND parent_id IS NULL ORDER BY created_at DESC");
                $stmt->execute([$selected_id]);
                $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Count comments
                $comment_count = $db->prepare("SELECT COUNT(*) FROM news_comments WHERE news_id = ?");
                $comment_count->execute([$selected_id]);
                $comment_count = $comment_count->fetchColumn();
                ?>
                <div style="margin-top:24px;">
                    <h3 style="color:#fff;background:#ff3366;padding:8px 18px;border-radius:8px;display:inline-block;margin-bottom:12px;">
                        Comments (<?= $comment_count ?>)
                    </h3>
                    <?php foreach ($comments as $c): ?>
                        <div style="background:linear-gradient(90deg,#fff 80%,#ff3366 100%);border-radius:12px;padding:14px 18px;margin-bottom:14px;box-shadow:0 2px 8px #ff336633;">
                            <div style="font-weight:bold;color:#ff3366;">User #<?= $c['user_id'] ?></div>
                            <div style="margin:8px 0 0 0;font-size:1.08rem;"><?= htmlspecialchars($c['comment']) ?></div>
                            <div style="margin-left:18px;margin-top:10px;">
                                <?php
                                $stmt2 = $db->prepare("SELECT * FROM news_comments WHERE parent_id = ? ORDER BY created_at ASC");
                                $stmt2->execute([$c['id']]);
                                foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $r):
                                ?>
                                    <div style="background:#f8f8fa;border-radius:8px;padding:10px;margin:6px 0;">
                                        <span style="font-weight:bold;color:#228be6;">Reply #<?= $r['user_id'] ?>:</span>
                                        <span><?= htmlspecialchars($r['comment']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                <form method="post" style="margin-top:6px;">
                                    <input type="hidden" name="parent_id" value="<?= $c['id'] ?>">
                                    <textarea name="comment" rows="1" style="width:80%;border-radius:8px;border:1px solid #ff3366;padding:8px;font-size:1rem;"></textarea>
                                    <button type="submit" style="background:#ff3366;color:#fff;padding:6px 18px;border-radius:8px;font-weight:bold;margin-top:6px;">Reply</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="post" style="margin-top:12px;background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 8px #ff336633;">
                        <textarea name="comment" rows="2" style="width:100%;border-radius:8px;border:1px solid #ff3366;padding:10px;font-size:1.1rem;" placeholder="Add a comment..."></textarea>
                        <button type="submit" style="background:#ff3366;color:#fff;padding:8px 22px;border-radius:8px;font-weight:bold;margin-top:8px;">Send</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!$news): ?>
            <div style="text-align:center;color:#888;">No news found.</div>
        <?php else: ?>
            <?php foreach ($news as $n): ?>
                <?php
                // Format date and time for list view
                $date_str = $n['date'] ?? '';
                $date = $time = '';
                if ($date_str && preg_match('/^(\d{4}-\d{2}-\d{2})[ T](\d{2}:\d{2})/', $date_str, $m)) {
                    $date = $m[1];
                    $time = $m[2];
                    // Convert to GMT+6 (Bangladesh Time)
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
                <?php
                $up_count = $db->prepare("SELECT COUNT(*) FROM news_reacts WHERE news_id = ? AND react_type = 'up'");
                $up_count->execute([$n['id']]);
                $up_count = $up_count->fetchColumn();

                $down_count = $db->prepare("SELECT COUNT(*) FROM news_reacts WHERE news_id = ? AND react_type = 'down'");
                $down_count->execute([$n['id']]);
                $down_count = $down_count->fetchColumn();
                ?>
                <?php
                $comment_count = $db->prepare("SELECT COUNT(*) FROM news_comments WHERE news_id = ?");
                $comment_count->execute([$n['id']]);
                $comment_count = $comment_count->fetchColumn();
                ?>
                <a href="news.php?id=<?= $n['id'] ?><?= $search ? '&q=' . urlencode($search) : '' ?><?= isset($_GET['page']) ? '&page=' . intval($_GET['page']) : '' ?>" style="text-decoration:none;">
                    <div class="news-item">
                        <div class="news-title" style="min-height:1.2em;"><?= htmlspecialchars($n['title']) ?></div>
                        <div class="news-date">
                            <?php
                            $date_str = $n['date'] ?? '';
                            $date = $time = '';
                            if ($date_str && preg_match('/^(\d{4}-\d{2}-\d{2})[ T](\d{2}:\d{2})/', $date_str, $m)) {
                                $date = $m[1];
                                $time = $m[2];
                                // Convert to GMT+6 (Bangladesh Time)
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
                            Date: <?= htmlspecialchars($date) ?> | Time: <span><?= htmlspecialchars($time) ?> <span style="font-size:.9em;">GMT+6</span></span><?php if (!empty($n['news_id'])): ?> | ID: <span style="font-size:.85rem;background:#ff3366;color:#fff;padding:2px 8px;border-radius:8px;font-weight:bold;letter-spacing:1px;min-width:80px;display:inline-block;"><?= htmlspecialchars($n['news_id']) ?></span><?php endif; ?> | Writer: <?= htmlspecialchars($n['writer']) ?>
                        </div>
                        <div class="news-content"><?= nl2br(htmlspecialchars(mb_strimwidth($n['content'],0,120,'...'))) ?></div>
                        <div>
                            👍 <?= $up_count ?> &nbsp; 👎 <?= $down_count ?> &nbsp; 💬 <?= $comment_count ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?q=<?= urlencode($search) ?>&page=1">&laquo; First</a>
                <a href="?q=<?= urlencode($search) ?>&page=<?= $page-1 ?>">&lt; Prev</a>
            <?php else: ?>
                <span class="disabled">&laquo; First</span>
                <span class="disabled">&lt; Prev</span>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php elseif ($i == 1 || $i == $total_pages || abs($i-$page) <= 2): ?>
                    <a href="?q=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                <?php elseif ($i == $page-3 || $i == $page+3): ?>
                    <span>...</span>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?q=<?= urlencode($search) ?>&page=<?= $page+1 ?>">Next &gt;</a>
                <a href="?q=<?= urlencode($search) ?>&page=<?= $total_pages ?>">Last &raquo;</a>
            <?php else: ?>
                <span class="disabled">Next &gt;</span>
                <span class="disabled">Last &raquo;</span>
            <?php endif; ?>
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
