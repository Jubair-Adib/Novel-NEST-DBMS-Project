<?php
// admin_authors.php - NOVEL NEST Admin Author Management
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin.php');
    exit;
}
$msg = '';
// Insert new author
if (isset($_POST['add_author'])) {
    $name = trim($_POST['name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $gmail = trim($_POST['gmail'] ?? '');
    // Generate unique author_id: AU + 5-digit
    $last = $db->query("SELECT writer_id FROM authors WHERE writer_id LIKE 'WR%' OR writer_id LIKE 'AU%' ORDER BY id DESC LIMIT 1")->fetchColumn();
    if ($last && preg_match('/(?:WR|AU)(\d{5})/', $last, $m)) {
        $num = intval($m[1]) + 1;
    } else {
        $num = 1;
    }
    $author_id = 'AU' . str_pad($num, 5, '0', STR_PAD_LEFT);
    // Assign default photo from assets/Author/1.png ... 75.png
    $photo = 'assets/Author/' . (($num - 1) % 75 + 1) . '.png';
    $stmt = $db->prepare("INSERT INTO authors (name, bio, photo, gmail, writer_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $bio, $photo, $gmail, $author_id]);
    $msg = 'Author added!';
}
// Edit author
if (isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $name = trim($_POST['name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $gmail = trim($_POST['gmail'] ?? '');
    $photo = $_POST['old_photo'] ?? null;
    $db->prepare("UPDATE authors SET name=?, bio=?, photo=?, gmail=? WHERE id=?")
        ->execute([$name, $bio, $photo, $gmail, $id]);
    $msg = 'Author updated!';
}
// Delete author(s)
if (isset($_POST['delete_selected']) && isset($_POST['author_ids'])) {
    $ids = $_POST['author_ids'];
    $in = implode(',', array_fill(0, count($ids), '?'));
    $db->prepare("DELETE FROM authors WHERE id IN ($in)")->execute($ids);
    $msg = 'Selected authors deleted!';
}
if (isset($_POST['delete_all'])) {
    $db->exec("DELETE FROM authors");
    $msg = 'All authors deleted!';
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM authors WHERE id = ?")->execute([$id]);
    $msg = 'Author deleted!';
}
$authors = $db->query("SELECT * FROM authors ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Author Management - NOVEL NEST</title>
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
        .admin-box { max-width:1000px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        h2 { text-align:center; color:#ff3366; }
        form { margin-bottom:32px; }
        input, textarea { width:100%; margin-bottom:10px; padding:8px; border-radius:8px; border:1px solid #ccc; }
        .msg { color:#080; text-align:center; font-weight:bold; margin-bottom:18px; }
        table { width:100%; border-collapse:collapse; margin-top:24px; }
        th, td { border:1px solid #ccc; padding:8px; text-align:center; }
        th { background:#eee; }
        .actions a, .actions button { margin:0 6px; }
        .author-photo { width:40px; height:40px; object-fit:cover; border-radius:50%; background:#eee; }
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
        .action-btn {
            background: #0099cc;
            color: #fff;
            border: none;
            padding: 6px 16px;
            border-radius: 8px;
            font-size: 0.95rem;
            cursor: pointer;
            margin: 0 2px;
        }
        .delete-btn {
            background: #ff3366;
        }
        .edit-btn {
            background: #22bb33;
        }
        .select-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
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
    <div class="admin-box">
        <h2>Admin Author Management</h2>
        <?php if ($msg) echo '<div class="msg">'.$msg.'</div>'; ?>
        <form method="post">
            <input type="text" name="name" placeholder="Author Name" required>
            <textarea name="bio" placeholder="Bio"></textarea>
            <input type="email" name="gmail" placeholder="Gmail Address" required>
            <button type="submit" name="add_author" class="login-btn" style="width:100%;margin-top:8px;">Add Author</button>
        </form>
        <form method="post">
        <table>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th>Photo</th>
                <th>Author ID</th>
                <th>Name</th>
                <th>Bio</th>
                <th>Gmail</th>
                <th>Books</th>
                <th>Action</th>
            </tr>
            <?php foreach ($authors as $a): ?>
            <tr>
                <td><input type="checkbox" name="author_ids[]" value="<?= $a['id'] ?>"></td>
                <td><img src="<?= $a['photo'] ? htmlspecialchars($a['photo']) : 'assets/Author/' . (($a['id'] - 1) % 75 + 1) . '.png' ?>" class="author-photo"></td>
                <td><?= htmlspecialchars($a['writer_id']) ?></td>
                <td><?= htmlspecialchars($a['name']) ?></td>
                <td><?= htmlspecialchars($a['bio']) ?></td>
                <td><?= htmlspecialchars($a['gmail']) ?></td>
                <td>
                    <?php
                    $book_count = $db->prepare("SELECT COUNT(*) FROM books WHERE author_id = ?");
                    $book_count->execute([$a['id']]);
                    echo $book_count->fetchColumn();
                    ?>
                </td>
                <td class="actions">
                    <a href="edit_author.php?id=<?= $a['id'] ?>" class="action-btn edit-btn">Edit</a>
                    <a href="admin_authors.php?delete=<?= $a['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Delete this author?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div class="select-actions">
            <button type="submit" name="delete_selected" class="action-btn delete-btn" onclick="return confirm('Delete selected authors?')">Delete Selected</button>
            <button type="submit" name="delete_all" class="action-btn delete-btn" onclick="return confirm('Delete ALL authors?')">Delete All</button>
        </div>
        </form>
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
    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('input[name="author_ids[]"]');
            for (var cb of checkboxes) cb.checked = this.checked;
        });
    </script>
</body>
</html>
