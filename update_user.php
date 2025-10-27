<?php
// update_user.php - Handles user info update by admin
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $photo = trim($_POST['photo']);
    $stmt = $db->prepare("UPDATE users SET name=?, username=?, email=?, address=?, phone=?, photo=? WHERE id=?");
    $stmt->execute([$name, $username, $email, $address, $phone, $photo, $id]);
    header('Location: users.php?msg=updated');
    exit;
} else if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user = $db->query("SELECT * FROM users WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
    if (!$user) { echo 'User not found.'; exit; }
} else {
    echo 'Invalid request.'; exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; }
        .edit-form { max-width:400px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        h2 { text-align:center; color:#ff3366; }
        label { display:block; margin-top:12px; }
        input { width:100%; padding:8px; margin-top:4px; border-radius:6px; border:1px solid #ccc; }
        .btn { background:#ff3366; color:#fff; border:none; padding:10px 24px; border-radius:6px; margin-top:18px; cursor:pointer; }
    </style>
</head>
<body>
    <div class="edit-form">
        <h2>Edit User</h2>
        <form method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            <label>Address</label>
            <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>">
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
            <label>Photo URL (optional)</label>
            <input type="text" name="photo" value="<?= htmlspecialchars($user['photo']) ?>">
            <button class="btn" type="submit">Update</button>
        </form>
        <div style="text-align:center;margin-top:18px;">
            <a href="users.php">Back to User List</a>
        </div>
    </div>
</body>
</html>
