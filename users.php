<?php
// users.php - NOVEL NEST Admin: List of All Users
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Handle search, filter, and pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$where = [];
$params = [];
if ($search !== '') {
    $where[] = "(name LIKE :s OR email LIKE :s OR id LIKE :s)";
    $params[':s'] = "%$search%";
}
if ($role_filter === 'user' || $role_filter === 'admin') {
    $where[] = "role = :role";
    $params[':role'] = $role_filter;
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$total = $db->prepare("SELECT COUNT(*) FROM users $where_sql");
$total->execute($params);
$total_users = $total->fetchColumn();
$total_pages = ceil($total_users / $per_page);
$offset = ($page - 1) * $per_page;
$sql = "SELECT id, name, email, role, photo, unique_id, rating FROM users $where_sql ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Users - NOVEL NEST</title>
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
        .user-list { max-width:900px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
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
    <div class="user-list">
        <h2>All Users</h2>
        <form method="get" style="text-align:center;margin-bottom:18px;">
            <input type="hidden" name="role" value="user">
            <input type="text" name="search" placeholder="Search by name or email" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" style="padding:7px 12px;width:260px;border-radius:6px;border:1px solid #ccc;">
            <button type="submit" class="login-btn" style="background:#0099cc;padding:7px 18px;">Search</button>
        </form>
        <form method="post" action="delete_user.php" id="user-list-form">
        <table>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th>Photo</th>
                <th>Name</th>
                <th>Email</th>
                <th>User ID</th>
                <th>Rating</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
            <?php foreach ($users as $u): ?>
            <?php if ($u['role'] === 'user'): ?>
            <tr>
                <td><input type="checkbox" name="user_ids[]" value="<?= $u['id'] ?>"></td>
                <td><img src="<?= htmlspecialchars($u['photo'] ?: 'assets/default-profile.png') ?>" class="user-photo"></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['id']) ?></td>
                <td><?= htmlspecialchars($u['rating'] ?? '800') ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td>
                    <a href="update_user.php?id=<?= $u['id'] ?>" class="login-btn" style="background:#0099cc;padding:4px 12px;font-size:13px;">Edit</a>
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                    <a href="delete_user.php?id=<?= $u['id'] ?>" class="login-btn" style="background:#ff3366;padding:4px 12px;font-size:13px;" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
        </table>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;">
            <button type="submit" class="login-btn" style="background:#ff3366;padding:6px 18px;" onclick="return confirm('Delete selected users?');">Delete Selected</button>
        </div>
        </form>
        <form method="post" action="delete_user.php" style="margin-top:10px; text-align:right;" 
              onsubmit="return confirm('Are you absolutely sure you want to delete ALL users? This action cannot be undone!');">
            <input type="hidden" name="delete_all" value="1">
            <button type="submit" class="login-btn" style="background:#ff3366;padding:6px 18px;">Delete All</button>
        </form>
        <script>
        document.getElementById('select-all').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('input[name="user_ids[]"]');
            for (var cb of checkboxes) cb.checked = this.checked;
        });
        </script>
        <?php if ($total_pages > 1): ?>
        <div style="text-align:center;margin-top:18px;">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>&role=user" class="login-btn" style="background:<?= $i==$page?'#ff3366':'#eee' ?>;color:<?= $i==$page?'#fff':'#333' ?>;margin:0 2px;padding:4px 12px;font-size:13px;"> <?= $i ?> </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
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
