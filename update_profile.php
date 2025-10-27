<?php
// profile.php - NOVEL NEST User Profile & Dashboard
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

// Handle profile update
$update_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $photo = $user['photo'];
    if (!empty($_FILES['photo']['name'])) {
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
    
    // Check if current password matches
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $hashed_password = $stmt->fetchColumn();
    
    if ($hashed_password && password_verify($current_password, $hashed_password)) {
        // Check if new password and confirm password match
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
    <title>Profile - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; }
        .profile-box { max-width:500px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        .profile-box h2 { text-align:center; }
        .profile-photo { display:block; margin:0 auto 16px auto; width:100px; height:100px; border-radius:50%; object-fit:cover; background:#eee; }
        .profile-box input, .profile-box textarea { width:100%; margin-bottom:12px; padding:10px; border-radius:8px; border:1px solid #ccc; }
        .profile-box button { width:100%; background:#ff3366; color:#fff; border:none; padding:12px; border-radius:8px; font-size:1rem; cursor:pointer; }
        .msg { color:#080; text-align:center; }
        .profile-info { margin-bottom:24px; }
    </style>
</head>
<body>
    <div class="news-top-bar" style="background:linear-gradient(90deg,#ff3366 0%,#ffb347 100%);color:#fff;padding:32px 0 18px 0;border-radius:0 0 32px 32px;box-shadow:0 4px 24px #ff336633;text-align:center;margin-bottom:0;">
        <h2 style="margin:0;font-size:2.4rem;letter-spacing:2px;font-weight:bold;color:#fff;text-shadow:0 2px 8px #ff336688;">My Profile</h2>
        <div style="font-size:1.1rem;opacity:.92;">Welcome to your NOVEL NEST dashboard</div>
    </div>
    <div style="display:flex;justify-content:center;gap:18px;margin:32px 0 18px 0;">
        <a href="index.php" style="background:#ff3366;color:#fff;padding:10px 28px;border-radius:24px;text-decoration:none;font-weight:600;box-shadow:0 2px 8px #ff336633;">&larr; Go Back</a>
        <a href="profile_update.php" style="background:#228be6;color:#fff;padding:10px 28px;border-radius:24px;text-decoration:none;font-weight:600;box-shadow:0 2px 8px #228be633;">Update Profile</a>
    </div>
    <div class="profile-box">
        <div style="display:flex;flex-direction:column;align-items:center;gap:10px;margin-bottom:18px;">
            <img src="<?= $user['photo'] ? $user['photo'] : 'assets/default-profile.png' ?>" class="profile-photo" alt="Profile Photo" style="box-shadow:0 4px 24px #ff336633;border:4px solid #fff;">
            <div style="font-size:1.5rem;font-weight:bold;color:#ff3366;letter-spacing:1px;">Welcome, <?= htmlspecialchars($user['name']) ?></div>
            <span style="background:#ff3366;color:#fff;padding:4px 18px;border-radius:8px;font-size:1.05rem;letter-spacing:1px;">ID: <?= htmlspecialchars($user['unique_id']) ?></span>
            <span style="color:#888;font-size:1.05rem;">Role: <b style="color:#ff3366;"><?= htmlspecialchars($user['role']) ?></b></span>
        </div>
        <?php if ($update_msg) echo "<div class='msg'>$update_msg</div>"; ?>
        <div class="profile-info" style="background:#f8f8fa;border-radius:12px;padding:18px 24px;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
            <div style="display:grid;grid-template-columns:120px 1fr;gap:8px 18px;align-items:center;">
                <span style="color:#ff3366;font-weight:600;">Username:</span> <span><?= htmlspecialchars($user['username']) ?></span>
                <span style="color:#ff3366;font-weight:600;">Email:</span> <span><?= htmlspecialchars($user['email']) ?></span>
                <span style="color:#ff3366;font-weight:600;">Phone:</span> <span><?= htmlspecialchars($user['phone']) ?></span>
                <span style="color:#ff3366;font-weight:600;">Address:</span> <span><?= htmlspecialchars($user['address']) ?></span>
            </div>
        </div>
        <form method="post" enctype="multipart/form-data" style="background:#fff0f5;border-radius:12px;padding:18px 24px;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
            <div style="font-size:1.1rem;font-weight:600;color:#ff3366;margin-bottom:10px;">Update Profile</div>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" placeholder="Full Name" required>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="Phone">
            <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>" placeholder="Address">
            <button type="submit">Update Profile</button>
        </form>
        <form method="post" style="background:#fff0f5;border-radius:12px;padding:18px 24px;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
            <div style="font-size:1.1rem;font-weight:600;color:#ff3366;margin-bottom:10px;">Update Password</div>
            <input type="password" name="current_password" placeholder="Current Password" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <button type="submit" name="update_password">Update Password</button>
        </form>
        <div style="margin:32px 0 18px 0;border-bottom:2px solid #ff3366;"></div>
        <h3 style="color:#ff3366;text-align:center;margin-bottom:18px;">My Lend History</h3>
        <ul style="padding-left:0;list-style:none;background:#f8f8fa;border-radius:12px;padding:18px 24px;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
        <?php
        $stmt = $db->prepare("SELECT lend.*, books.title FROM lend LEFT JOIN books ON lend.books = books.id WHERE lend.user_id = ? ORDER BY lend.received_time DESC");
        $stmt->execute([$user_id]);
        foreach ($stmt as $row) {
            echo '<li style="margin-bottom:8px;"><b style="color:#ff3366;">' . htmlspecialchars($row['title']) . '</b> | Branch: ' . htmlspecialchars($row['branch']) . ' | Received: ' . htmlspecialchars($row['received_time']) . ' | Return by: ' . htmlspecialchars($row['return_time']) . ' | Method: ' . htmlspecialchars($row['method']) . '</li>';
        }
        ?>
        </ul>
        <h3 style="color:#ff3366;text-align:center;margin-bottom:18px;">My Buy History</h3>
        <ul style="padding-left:0;list-style:none;background:#f8f8fa;border-radius:12px;padding:18px 24px;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
        <?php
        $stmt = $db->prepare("SELECT buy.*, books.title FROM buy LEFT JOIN books ON buy.books = books.id WHERE buy.user_id = ? ORDER BY buy.time DESC");
        $stmt->execute([$user_id]);
        foreach ($stmt as $row) {
            echo '<li style="margin-bottom:8px;"><b style="color:#ff3366;">' . htmlspecialchars($row['title']) . '</b> | Branch: ' . htmlspecialchars($row['branch']) . ' | Time: ' . htmlspecialchars($row['time']) . ' | Method: ' . htmlspecialchars($row['method']) . ' | Payment: $' . number_format($row['payment_total'],2) . '</li>';
        }
        ?>
        </ul>
        <h3 style="color:#ff3366;text-align:center;margin-bottom:18px;">My Attendance</h3>
        <ul style="padding-left:0;list-style:none;background:#f8f8fa;border-radius:12px;padding:18px 24px;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
        <?php
        $stmt = $db->prepare("SELECT attendance.*, books.title FROM attendance LEFT JOIN books ON attendance.books = books.id WHERE attendance.user_id = ? ORDER BY attendance.entry_time DESC");
        $stmt->execute([$user_id]);
        foreach ($stmt as $row) {
            echo '<li style="margin-bottom:8px;"><b style="color:#ff3366;">' . htmlspecialchars($row['title']) . '</b> | Branch: ' . htmlspecialchars($row['branch']) . ' | Entry: ' . htmlspecialchars($row['entry_time']) . ' | Exit: ' . htmlspecialchars($row['exit_time']) . '</li>';
        }
        ?>
        </ul>
        <h3 style="color:#ff3366;text-align:center;margin-bottom:18px;">My Amounts</h3>
        <ul style="padding-left:0;list-style:none;background:#f8f8fa;border-radius:12px;padding:18px 24px;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
        <?php
        $stmt = $db->prepare("SELECT * FROM amounts WHERE user_id = ? ORDER BY date DESC");
        $stmt->execute([$user_id]);
        foreach ($stmt as $row) {
            $sign = $row['change'] >= 0 ? '+' : '-';
            echo '<li style="margin-bottom:8px;">' . $sign . '$' . number_format(abs($row['change']),2) . ' | Reason: ' . htmlspecialchars($row['reason']) . ' | Date: ' . htmlspecialchars($row['date']) . ' | Final: $' . number_format($row['final_amount'],2) . '</li>';
        }
        ?>
        </ul>
        <h3 style="color:#ff3366;text-align:center;margin-bottom:18px;">Add Amount</h3>
        <form method="post" style="background:#fff0f5;border-radius:12px;padding:18px 24px;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
            <input type="number" name="add_amount" step="0.01" min="1" placeholder="Enter amount to add" required>
            <button type="submit">Add Amount</button>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_amount'])) {
            $add = floatval($_POST['add_amount']);
            if ($add > 0) {
                // Get last final_amount
                $stmt = $db->prepare("SELECT final_amount FROM amounts WHERE user_id = ? ORDER BY date DESC LIMIT 1");
                $stmt->execute([$user_id]);
                $last = $stmt->fetchColumn();
                $final = ($last !== false ? $last : 0) + $add;
                $stmt = $db->prepare("INSERT INTO amounts (user_id, change, reason, date, final_amount) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $add, 'User added funds', date('Y-m-d H:i:s'), $final]);
                echo '<div class="msg">Amount added successfully!</div>';
                echo '<script>setTimeout(()=>location.reload(), 1000);</script>';
            }
        }
        ?>
        <h3 style="color:#ff3366;text-align:center;margin-bottom:18px;">My Receipts</h3>
        <ul style="padding-left:0;list-style:none;background:#f8f8fa;border-radius:12px;padding:18px 24px;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
        <?php
        $stmt = $db->prepare("SELECT * FROM buy WHERE user_id = ? ORDER BY time DESC");
        $stmt->execute([$user_id]);
        foreach ($stmt as $row) {
            echo '<li style="margin-bottom:8px;">Book ID: ' . htmlspecialchars($row['books']) . ' | Payment ID: ' . htmlspecialchars($row['payment_id']) . ' | Amount: $' . number_format($row['payment_total'],2) . ' | <a href="receipt.php?payment_id=' . urlencode($row['payment_id']) . '" target="_blank">View/Print</a></li>';
        }
        ?>
        </ul>
        <h3 style="color:#ff3366;text-align:center;margin-bottom:18px;">Download Statement</h3>
        <form method="get" action="statement.php" target="_blank" style="background:#fff0f5;border-radius:12px;padding:18px 24px;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
            <button type="submit">Download/Print Full Statement</button>
        </form>
        <div style="text-align:center;margin-top:12px;">
            <a href="logout.php" style="color:#fff;background:#ff3366;padding:8px 22px;border-radius:8px;text-decoration:none;font-weight:600;">Logout</a>
        </div>
    </div>
    <div class="news-footer-bar" style="background:linear-gradient(90deg,#18181b 0%,#ff3366 100%);color:#fff;padding:32px 0 18px 0;border-radius:32px 32px 0 0;box-shadow:0 -4px 24px #ff336633;text-align:center;margin-top:48px;">
        <div class="footer-links">
            <a href="index.php" style="color:#ffb347;margin:0 18px;font-weight:bold;text-decoration:none;font-size:1.1rem;">Home</a>
            <a href="contact.php" style="color:#ffb347;margin:0 18px;font-weight:bold;text-decoration:none;font-size:1.1rem;">Contact</a>
            <a href="about.php" style="color:#ffb347;margin:0 18px;font-weight:bold;text-decoration:none;font-size:1.1rem;">About Us</a>
            <a href="news.php" style="color:#ffb347;margin:0 18px;font-weight:bold;text-decoration:none;font-size:1.1rem;">News</a>
        </div>
        <div style="margin-top:12px;font-size:.95rem;opacity:.8;">&copy; 2025 NOVEL NEST. All rights reserved.</div>
    </div>
</body>
</html>
