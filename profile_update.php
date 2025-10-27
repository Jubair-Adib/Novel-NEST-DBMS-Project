<?php
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$update_msg = '';
// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $photo = $user['photo'];

    // Use photo URL if provided, otherwise handle file upload
    $photo_url = trim($_POST['photo_url'] ?? '');
    if ($photo_url) {
        $photo = $photo_url;
    } elseif (!empty($_FILES['photo']['name'])) {
        $target = 'assets/uploads/' . basename($_FILES['photo']['name']);
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            $photo = $target;
        }
    }

    $stmt = $db->prepare("UPDATE users SET name=?, phone=?, address=?, photo=? WHERE id=?");
    $stmt->execute([$name, $phone, $address, $photo, $user_id]);
    $update_msg = 'Profile updated!';
    // Refresh user data
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $hashed_password = $stmt->fetchColumn();
    if ($hashed_password && password_verify($current_password, $hashed_password)) {
        if ($new_password === $confirm_password) {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->execute([$new_hashed_password, $user_id]);
            $update_msg = 'Password updated!';
        } else {
            $update_msg = 'New password and confirm password do not match.';
        }
    } else {
        $update_msg = 'Current password is incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Profile - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; }
        .profile-box { max-width:500px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        .profile-box h2 { text-align:center; }
        .profile-photo { display:block; margin:0 auto 16px auto; width:100px; height:100px; border-radius:50%; object-fit:cover; background:#eee; }
        .profile-box input, .profile-box textarea { width:100%; margin-bottom:12px; padding:10px; border-radius:8px; border:1px solid #ccc; }
        .profile-box button { width:100%; background:#ff3366; color:#fff; border:none; padding:12px; border-radius:8px; font-size:1rem; cursor:pointer; }
        .msg { color:#080; text-align:center; }
    </style>
</head>
<body>
    <div class="news-top-bar" style="background:linear-gradient(90deg,#ff3366 0%,#ffb347 100%);color:#fff;padding:32px 0 18px 0;border-radius:0 0 32px 32px;box-shadow:0 4px 24px #ff336633;text-align:center;margin-bottom:0;">
        <h2 style="margin:0;font-size:2.4rem;letter-spacing:2px;font-weight:bold;color:#fff;text-shadow:0 2px 8px #ff336688;">Update Profile</h2>
    </div>
    <div style="display:flex;justify-content:center;gap:18px;margin:32px 0 18px 0;">
        <a href="profile.php" style="background:#ff3366;color:#fff;padding:10px 28px;border-radius:24px;text-decoration:none;font-weight:600;box-shadow:0 2px 8px #ff336633;">&larr; Back to Profile</a>
    </div>
    <div class="profile-box">
        <?php if ($update_msg) echo "<div class='msg'>$update_msg</div>"; ?>
        <form method="post" enctype="multipart/form-data" style="background:#fff0f5;border-radius:12px;padding:18px 24px;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
            <div style="font-size:1.1rem;font-weight:600;color:#ff3366;margin-bottom:10px;">Update Profile</div>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" placeholder="Full Name" required>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="Phone">
            <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>" placeholder="Address">
            <input type="file" name="photo" accept="image/*">
            <input type="text" name="photo_url" value="<?= htmlspecialchars($user['photo']) ?>" placeholder="Photo URL (optional)">
            <button type="submit" name="update_profile">Update Profile</button>
        </form>
        <form method="post" style="background:#fff0f5;border-radius:12px;padding:18px 24px;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
            <div style="font-size:1.1rem;font-weight:600;color:#ff3366;margin-bottom:10px;">Update Password</div>
            <input type="password" name="current_password" placeholder="Current Password" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <button type="submit" name="update_password">Update Password</button>
        </form>
    </div>
</body>
</html>