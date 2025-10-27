<?php
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$news_id = isset($_GET['news_id']) ? intval($_GET['news_id']) : 0;
if (!$news_id) {
    echo "Invalid news ID.";
    exit;
}

// Fetch news
$stmt = $db->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$news_id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    echo "News not found.";
    exit;
}

// Only allow editing if this admin is the owner
if ($news['admin_id'] != $_SESSION['user_id']) {
    echo "You are not allowed to edit this news.";
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $writer = trim($_POST['writer'] ?? '');

    if ($title && $content && $writer) {
        $stmt = $db->prepare("UPDATE news SET title=?, content=?, writer=? WHERE id=?");
        $stmt->execute([$title, $content, $writer, $news_id]);
        $msg = "News updated successfully!";
        // Refresh news data
        $stmt = $db->prepare("SELECT * FROM news WHERE id = ?");
        $stmt->execute([$news_id]);
        $news = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $msg = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit News - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            background: linear-gradient(120deg, #fffbe6 60%, #ff3366 100%);
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .admin-header {
            background: linear-gradient(90deg, #ff3366 0%, #ffb347 100%);
            color: #fff;
            padding: 32px 0 18px 0;
            border-radius: 0 0 32px 32px;
            box-shadow: 0 4px 24px #ff336633;
            text-align: center;
            margin-bottom: 0;
        }
        .admin-header h1 {
            margin: 0;
            font-size: 2.4rem;
            letter-spacing: 2px;
            font-weight: bold;
            color: #fff;
            text-shadow: 0 2px 8px #ff336688;
        }
        .admin-box {
            max-width: 600px;
            margin: 40px auto 0 auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 24px #ff336633;
            padding: 38px 36px 32px 36px;
        }
        .admin-box h2 {
            color: #ff3366;
            margin-bottom: 18px;
            text-align: center;
            font-size: 2rem;
        }
        .admin-box form input,
        .admin-box form textarea {
            width: 100%;
            margin-bottom: 18px;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ffb347;
            font-size: 1.08rem;
            background: #fffbe6;
            transition: border 0.2s;
        }
        .admin-box form input:focus,
        .admin-box form textarea:focus {
            border: 1.5px solid #ff3366;
            outline: none;
        }
        .admin-box form button {
            width: 100%;
            background: linear-gradient(90deg, #ff3366 0%, #ffb347 100%);
            color: #fff;
            border: none;
            padding: 14px 0;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px #ff336633;
            margin-bottom: 10px;
            transition: background 0.2s;
        }
        .admin-box form button:hover {
            background: linear-gradient(90deg, #ffb347 0%, #ff3366 100%);
        }
        .admin-box form a {
            display: inline-block;
            margin-top: 8px;
            color: #ff3366;
            text-decoration: underline;
            font-weight: 600;
            font-size: 1rem;
        }
        .msg {
            color: #228be6;
            font-weight: bold;
            margin-bottom: 18px;
            text-align: center;
        }
        .admin-footer {
            background: #18181b;
            color: #fff;
            padding: 24px 0 12px 0;
            text-align: center;
            border-radius: 32px 32px 0 0;
            margin-top: 60px;
            font-size: 1.05rem;
            opacity: 0.92;
        }
        .admin-footer a {
            color: #ff3366;
            text-decoration: none;
            margin: 0 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>Edit News</h1>
    </div>
    <div class="admin-box">
        <h2>Edit News</h2>
        <?php if ($msg) echo "<div class='msg'>$msg</div>"; ?>
        <form method="post">
            <input type="text" name="title" value="<?= htmlspecialchars($news['title']) ?>" placeholder="Title" required>
            <input type="text" name="writer" value="<?= htmlspecialchars($news['writer']) ?>" placeholder="Writer" required>
            <textarea name="content" rows="7" placeholder="Content" required><?= htmlspecialchars($news['content']) ?></textarea>
            <button type="submit">Save Changes</button>
            <a href="admin_news.php?news_id=<?= $news_id ?>">&larr; Cancel</a>
        </form>
    </div>
    <div class="admin-footer">
        <div>
            <a href="admin.php">Dashboard</a> |
            <a href="admin_news.php">All News</a> |
            <a href="index.php">Home</a>
        </div>
        <div style="margin-top:8px;">&copy; <?= date('Y') ?> NOVEL NEST. All rights reserved.</div>
    </div>
</body>
</html>