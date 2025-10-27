<?php
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = ["role = 'admin'"];
$params = [];
if ($search !== '') {
    $where[] = "(name LIKE :s OR email LIKE :s OR id LIKE :s)";
    $params[':s'] = "%$search%";
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$stmt = $db->prepare("SELECT * FROM users $where_sql ORDER BY id DESC");
$stmt->execute($params);
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Admins - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; margin:0; font-family:sans-serif; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: linear-gradient(90deg, #18181b 60%, #ff3366 100%);
            color: #fff;
            box-shadow: 0 2px 8px #ff336633;
        }
        .logo {
            display: flex;
            align-items: center;
        }
        .logo img {
            height: 48px;
            margin-right: 16px;
        }
        .login-btn {
            background: #ff3366;
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 24px;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 2px 8px #ff336633;
            text-decoration: none;
            margin-left: 12px;
        }
        .admin-list { max-width:900px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        h2 { text-align:center; color:#ff3366; }
        table { width:100%; border-collapse:collapse; margin-top:24px; }
        th, td { border:1px solid #ccc; padding:8px; text-align:center; }
        th { background:#eee; }
        .user-photo { width:36px; height:36px; border-radius:50%; object-fit:cover; background:#eee; }
        .footer {
            background: linear-gradient(90deg, #18181b 60%, #ff3366 100%);
            color: #fff;
            padding: 32px 0 16px 0;
            text-align: center;
        }
        .footer .socials a {
            margin: 0 12px;
            color: #fff;
            font-size: 1.5rem;
            text-decoration: none;
            transition: color .2s;
        }
        .footer .socials a:hover {
            color: #ff3366;
        }
        .footer .links {
            margin: 16px 0;
        }
        .footer .links a {
            margin: 0 10px;
            color: #ff3366;
            text-decoration: none;
            font-weight: bold;
        }
        .footer .links a:hover {
            text-decoration: underline;
            color: #fff;
        }
        .footer .about {
            margin-top: 16px;
        }
        .footer .about a {
            color: #fff;
            text-decoration: underline;
        }
        .footer .about a:hover {
            color: #ff3366;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="assets/logo.png" alt="Logo" onerror="this.style.display='none'">
            <span style="font-size:2rem;font-weight:bold;letter-spacing:2px;">NOVEL NEST</span>
        </div>
        <button class="login-btn" onclick="window.location.href='admin.php'">Go Back</button>
    </div>
    <div class="admin-list">
        <h2>All Admins</h2>
        <form method="get" style="text-align:center;margin-bottom:18px;">
            <input type="text" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>" style="padding:7px 12px;width:260px;border-radius:6px;border:1px solid #ccc;">
            <button type="submit" class="login-btn" style="background:#0099cc;padding:7px 18px;">Search</button>
        </form>
        <table>
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Email</th>
                <th>Admin ID</th>
            </tr>
            <?php foreach ($admins as $a): ?>
            <tr>
                <td><img src="<?= htmlspecialchars($a['photo'] ?: 'assets/default-profile.png') ?>" class="user-photo"></td>
                <td><?= htmlspecialchars($a['name']) ?></td>
                <td><?= htmlspecialchars($a['email']) ?></td>
                <td><?= htmlspecialchars($a['id']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div style="text-align:center;margin-top:24px;">
            <a href="admin.php" class="login-btn" style="background:#ff3366;">Back to Admin Dashboard</a>
        </div>
    </div>
    <div class="footer">
        <div class="socials">
            <a href="#" title="Facebook">&#x1F426;</a>
            <a href="#" title="YouTube">&#x1F4FA;</a>
            <a href="#" title="Twitter">&#x1F426;</a>
            <a href="#" title="Instagram">&#x1F33A;</a>
        </div>
        <div class="links">
            <a href="contact.php">Contact List</a> 
            <a href="news.php">News</a>
        </div>
        <div class="about">
            <a href="about.php">About Us (Click to know)</a>
        </div>
        <div style="margin-top:16px;font-size:.9rem;opacity:.7;">&copy; 2025 NOVEL NEST. All rights reserved.</div>
    </div>
</body>
</html>