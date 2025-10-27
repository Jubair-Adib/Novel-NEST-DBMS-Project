<?php
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin.php');
    exit;
}
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $bio = trim($_POST['bio']);
    $gmail = trim($_POST['gmail']);
    $photo = trim($_POST['photo']);
    $stmt = $db->prepare("UPDATE authors SET name=?, bio=?, gmail=?, photo=? WHERE id=?");
    $stmt->execute([$name, $bio, $gmail, $photo, $id]);
    $msg = 'Author updated!';
    header("Location: admin_authors.php?msg=" . urlencode($msg));
    exit;
} elseif (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $author = $db->query("SELECT * FROM authors WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
    if (!$author) { echo 'Author not found.'; exit; }
} else {
    echo 'Invalid request.'; exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Author - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; }
        .edit-form { max-width:400px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        h2 { text-align:center; color:#ff3366; }
        label { display:block; margin-top:12px; }
        input, textarea { width:100%; padding:8px; margin-top:4px; border-radius:6px; border:1px solid #ccc; }
        .btn { background:#ff3366; color:#fff; border:none; padding:10px 24px; border-radius:6px; margin-top:18px; cursor:pointer; }
    </style>
</head>
<body>
    <div class="edit-form">
        <h2>Edit Author</h2>
        <form method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($author['id']) ?>">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($author['name']) ?>" required>
            <label>Bio</label>
            <textarea name="bio"><?= htmlspecialchars($author['bio']) ?></textarea>
            <label>Gmail</label>
            <input type="email" name="gmail" value="<?= htmlspecialchars($author['gmail']) ?>" required>
            <label>Photo URL</label>
            <input type="text" name="photo" value="<?= htmlspecialchars($author['photo']) ?>">
            <button class="btn" type="submit">Update</button>
        </form>
        <div style="text-align:center;margin-top:18px;">
            <a href="admin_authors.php">Back to Author List</a>
        </div>
    </div>
</body>
</html>