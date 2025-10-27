<?php
session_start();
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle reply
if (isset($_POST['send_reply'])) {
    $msg_id = intval($_POST['msg_id']);
    $reply = trim($_POST['reply']);
    $admin_id = $_SESSION['user_id'];
    if ($admin_id && $reply) {
        $stmt = $db->prepare("UPDATE messages SET replied_message = ?, replied = 1, admin_id = ? WHERE id = ?");
        $stmt->execute([$reply, $admin_id, $msg_id]);
    }
}

// Search/filter logic
$search = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? 'all';
$where = [];
$params = [];

if ($filter == 'replied') {
    $where[] = 'replied = 1';
} elseif ($filter == 'not_replied') {
    $where[] = 'replied = 0';
}

if ($search) {
    $where[] = '(branch LIKE ? OR message LIKE ? OR created_at LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmt = $db->prepare("SELECT * FROM messages $where_sql ORDER BY created_at DESC");
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages from Users - NOVEL NEST Admin</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background: #fafdff; }
        .admin-box { max-width: 1100px; margin: 40px auto; background: #fff; border-radius: 18px; box-shadow: 0 4px 24px #228be622; padding: 32px 28px; }
        h2 { color: #ff3366; margin-bottom: 18px; font-size: 2rem; letter-spacing: 1px; }
        .filter-bar { margin-bottom: 18px; display: flex; gap: 18px; align-items: center; }
        .filter-bar input[type="text"] { padding: 8px 14px; border-radius: 8px; border: 1.5px solid #228be6; font-size: 1rem; }
        .filter-bar button, .filter-bar a { background: linear-gradient(90deg,#228be6 60%,#ff3366 100%); color: #fff; padding: 8px 18px; border-radius: 8px; font-weight: bold; border: none; box-shadow: 0 2px 8px #228be633; text-decoration: none; transition: background .2s; cursor: pointer; }
        .filter-bar .active { background: #ff3366; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { padding: 12px 10px; text-align: left; }
        th { background: #228be6; color: #fff; font-weight: bold; }
        tr:nth-child(even) { background: #f8f8fa; }
        .status-replied { color: #228be6; font-weight: bold; }
        .status-pending { color: #c00; font-weight: bold; }
        .dashboard-link { display: inline-block; margin-bottom: 18px; background: #ff3366; color: #fff; padding: 8px 22px; border-radius: 8px; font-weight: bold; text-decoration: none; box-shadow: 0 2px 8px #228be633; }
        .logo { display: flex; align-items: center; gap: 18px; margin: 32px auto 0 auto; justify-content: center; }
        .logo img { height: 48px; }
        .news-footer-bar { background: #222; color: #fff; padding: 32px 0 16px 0; text-align: center; margin-top: 48px; }
        .footer-links a { color: #ff3366; margin: 0 10px; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <!-- Header -->
    <div style="background:#222;padding:0 0 2px 0;">
        <div style="max-width:900px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:18px 32px 10px 32px;">
            <div style="font-size:2.1rem;font-weight:bold;color:#fff;letter-spacing:2px;font-family:sans-serif;text-shadow:0 2px 8px #0002;">
                NOVEL NEST
            </div>
            <div>
                <a href="index.php" style="background:#ff3366;color:#fff;font-weight:600;padding:8px 22px;border-radius:8px;text-decoration:none;box-shadow:0 2px 8px #0001;transition:background .2s;">Home</a>
            </div>
        </div>
    </div>
    <div class="admin-box">
        <a href="admin.php" class="dashboard-link">&larr; Back to Dashboard</a>
        <h2>Messages from Users</h2>
        <form class="filter-bar" method="get" action="admin_messages.php">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by branch, time, or message text">
            <button type="submit">Search</button>
            <a href="?filter=all" class="<?= $filter=='all'?'active':'' ?>">All</a>
            <a href="?filter=replied" class="<?= $filter=='replied'?'active':'' ?>">Replied</a>
            <a href="?filter=not_replied" class="<?= $filter=='not_replied'?'active':'' ?>">Not Replied</a>
        </form>
        <table>
            <tr>
                <th>User</th>
                <th>Branch</th>
                <th>Message</th>
                <th>Reply</th>
                <th>Status</th>
                <th>Action</th>
                <th>Date</th>
            </tr>
            <?php foreach ($messages as $msg): ?>
            <tr>
                <td><?= htmlspecialchars($msg['user_name'] ?? 'Unknown') ?></td>
                <td><?= htmlspecialchars($msg['branch']) ?></td>
                <td><?= htmlspecialchars($msg['message']) ?></td>
                <td><?= htmlspecialchars($msg['replied_message']) ?></td>
                <td class="<?= $msg['replied'] ? 'status-replied' : 'status-pending' ?>">
                    <?= $msg['replied'] ? 'Replied' : 'Pending' ?>
                </td>
                <td>
                    <?php if (!$msg['replied']): ?>
                    <form method="post" style="margin:0;">
                        <input type="hidden" name="msg_id" value="<?= $msg['id'] ?>">
                        <textarea name="reply" rows="2" style="width:100%;border-radius:6px;border:1px solid #228be6;"></textarea>
                        <button type="submit" name="send_reply" style="margin-top:6px;">Reply</button>
                    </form>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($msg['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <!-- Footer -->
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