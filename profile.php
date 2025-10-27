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

function calculate_user_rating($db, $user_id, $role) {
    // Start from 800
    $rating = 800;

    // Buy: +10 per book
    $stmt = $db->prepare("SELECT COUNT(*) FROM buy WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $buy_count = $stmt->fetchColumn();
    $rating += $buy_count * 10;

    // Lend: +50 per book
    $stmt = $db->prepare("SELECT id, received_time, return_time, returned_time FROM lend WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $lends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($lends as $lend) {
        $rating += 50;
        // Early return bonus
        if ($lend['received_time'] && $lend['return_time'] && $lend['returned_time']) {
            $start = strtotime($lend['received_time']);
            $end = strtotime($lend['return_time']);
            $actual = strtotime($lend['returned_time']);
            $total_days = max(1, round(($end - $start) / 86400));
            $used_days = max(1, round(($actual - $start) / 86400));
            if ($actual <= $end) {
                // Early return: bonus proportional to unused days
                $bonus = 50 * (($end - $actual) / 86400) / $total_days;
                $rating += round($bonus);
            } else {
                // Late: penalty per late day
                $late_days = round(($actual - $end) / 86400);
                $rating -= $late_days * 50;
            }
        }
    }

    // Admin: +1 per news post
    $stmt = $db->prepare("SELECT COUNT(*) FROM news WHERE admin_id = ?");
    $stmt->execute([$user_id]);
    $news_count = $stmt->fetchColumn();
    $rating += $news_count;

    // Prevent negative rating
    if ($rating < 0) $rating = 0;

    // Update in DB
    $db->prepare("UPDATE users SET rating=? WHERE id=?")->execute([$rating, $user_id]);
    return $rating;
}

$rating = calculate_user_rating($db, $user_id, $user['role']);
$user['rating'] = $rating;

function get_rating_title($rating, $role) {
    if ($rating >= 4000) return ($role === 'admin' ? 'Admin_tourist' : 'User_tourist');
    if ($rating >= 3000) return ($role === 'admin' ? 'Admin_Legendary Grandmaster' : 'User_Legendary Grandmaster');
    if ($rating >= 2600) return ($role === 'admin' ? 'Admin_International Grandmaster' : 'User_International Grandmaster');
    if ($rating >= 2400) return ($role === 'admin' ? 'Admin_Grandmaster' : 'User_Grandmaster');
    if ($rating >= 2300) return ($role === 'admin' ? 'Admin_International Master' : 'User_International Master');
    if ($rating >= 2100) return ($role === 'admin' ? 'Admin_Master' : 'User_Master');
    if ($rating >= 1900) return ($role === 'admin' ? 'Admin_Candidate Master' : 'User_Candidate Master');
    if ($rating >= 1600) return ($role === 'admin' ? 'Admin_Expert' : 'User_Expert');
    if ($rating >= 1400) return ($role === 'admin' ? 'Admin_Specialist' : 'User_Specialist');
    if ($rating >= 1200) return ($role === 'admin' ? 'Admin_Pupil' : 'User_Pupil');
    return ($role === 'admin' ? 'Admin_Newbie' : 'User_Newbie');
}
$rating_title = get_rating_title($user['rating'], $user['role']);
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
        <!-- <div style="display:flex;align-items:center;justify-content:center;gap:32px;margin-bottom:18px;">
            <img src="<?= $user['photo'] ? $user['photo'] : 'assets/default-profile.png' ?>" class="profile-photo" alt="Profile Photo" style="box-shadow:0 4px 24px #ff336633;border:4px solid #fff;">
            <div>
                <div style="font-size:2.5rem;font-weight:bold;color:#ff3366;"><?= htmlspecialchars($user['rating']) ?></div>
                <div style="font-size:1.3rem;font-weight:700;color:#228be6;"><?= htmlspecialchars($rating_title) ?></div>
            </div>
        </div> -->
        <div style="display:flex;flex-direction:column;align-items:center;gap:10px;margin-bottom:18px;">
            <img src="<?= $user['photo'] ? $user['photo'] : 'assets/default-profile.png' ?>" class="profile-photo" alt="Profile Photo" style="box-shadow:0 4px 24px #ff336633;border:4px solid #fff;">
            <div style="font-size:1.5rem;font-weight:bold;color:#ff3366;letter-spacing:1px;">Welcome, <?= htmlspecialchars($user['name']) ?></div>
                  <div>
                <div style="font-size:2.5rem;font-weight:bold;color:#ff3366;"><?= htmlspecialchars($user['rating']) ?></div>
                <div style="font-size:1.3rem;font-weight:700;color:#228be6;"><?= htmlspecialchars($rating_title) ?></div>
            </div>
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
        <!-- <form method="post" enctype="multipart/form-data" style="background:#fff0f5;border-radius:12px;padding:18px 24px;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
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
        </form> -->
        <div style="margin:32px 0 18px 0;border-bottom:2px solid #ff3366;"></div>
        <h3 style="color:#ff3366;text-align:center;margin-bottom:18px;">📚 My Lend History</h3>
        <table style="width:100%;background:#f8f8fa;border-radius:12px;padding:0 0 18px 0;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
            <thead>
                <tr style="color:#ff3366;font-weight:bold;">
                    <th style="padding:8px;">Book</th>
                    <th style="padding:8px;">Branch</th>
                    <th style="padding:8px;">Received</th>
                    <th style="padding:8px;">Return by</th>
                    <th style="padding:8px;">Returned</th>
                    <th style="padding:8px;">Method</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $stmt = $db->prepare("SELECT lend.*, books.title FROM lend LEFT JOIN books ON lend.books = books.id WHERE lend.user_id = ? ORDER BY lend.received_time DESC");
            $stmt->execute([$user_id]);
            $has_lend = false;
            foreach ($stmt as $row) {
                $has_lend = true;
                echo '<tr>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['title'] ?? '-') . '</td>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['branch'] ?? '-') . '</td>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['received_time'] ?? '-') . '</td>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['return_time'] ?? '-') . '</td>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['returned_time'] ?? '-') . '</td>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['method'] ?? '-') . '</td>';
                echo '</tr>';
            }
            if (!$has_lend) echo '<tr><td colspan="6" style="color:#888;text-align:center;">No lend history found.</td></tr>';
            ?>
            </tbody>
        </table>

        <h3 style="color:#ff3366;text-align:center;margin-bottom:18px;">🛒 My Buy History</h3>
        <table style="width:100%;background:#f8f8fa;border-radius:12px;padding:0 0 18px 0;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
            <thead>
                <tr style="color:#ff3366;font-weight:bold;">
                    <th style="padding:8px;">Book</th>
                    <th style="padding:8px;">Branch</th>
                    <th style="padding:8px;">Time</th>
                    <th style="padding:8px;">Method</th>
                    <th style="padding:8px;">Payment</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $stmt = $db->prepare("SELECT buy.*, books.title FROM buy LEFT JOIN books ON buy.books = books.id WHERE buy.user_id = ? ORDER BY buy.time DESC");
            $stmt->execute([$user_id]);
            $has_buy = false;
            foreach ($stmt as $row) {
                $has_buy = true;
                echo '<tr>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['title'] ?? '-') . '</td>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['branch'] ?? '-') . '</td>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['time'] ?? '-') . '</td>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['method'] ?? '-') . '</td>';
                echo '<td style="padding:8px;">$' . number_format($row['payment_total'],2) . '</td>';
                echo '</tr>';
            }
            if (!$has_buy) echo '<tr><td colspan="5" style="color:#888;text-align:center;">No buy history found.</td></tr>';
            ?>
            </tbody>
        </table>

        <h3 style="color:#ff3366;text-align:center;margin-bottom:18px;">🕒 My Attendance</h3>
        <table style="width:100%;background:#f8f8fa;border-radius:12px;padding:0 0 18px 0;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
            <thead>
                <tr style="color:#ff3366;font-weight:bold;">
                    <th style="padding:8px;">Branch</th>
                    <th style="padding:8px;">Entry</th>
                    <th style="padding:8px;">Exit</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $stmt = $db->prepare("SELECT branch_entries.*, branches.name AS branch_name FROM branch_entries LEFT JOIN branches ON branch_entries.branch_id = branches.id WHERE branch_entries.user_id = ? ORDER BY branch_entries.entry_time DESC");
            $stmt->execute([$user_id]);
            $has_att = false;
            foreach ($stmt as $row) {
                $has_att = true;
                echo '<tr>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['branch_name'] ?? '-') . '</td>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['entry_time'] ?? '-') . '</td>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['leave_time'] ?? '-') . '</td>';
                echo '</tr>';
            }
            if (!$has_att) echo '<tr><td colspan="3" style="color:#888;text-align:center;">No attendance found.</td></tr>';
            ?>
            </tbody>
        </table>

        <h3 style="color:#ff3366;text-align:center;margin-bottom:18px;">💰 My Amounts</h3>
        <table style="width:100%;background:#f8f8fa;border-radius:12px;padding:0 0 18px 0;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
            <thead>
                <tr style="color:#ff3366;font-weight:bold;">
                    <th style="padding:8px;">Change</th>
                    <th style="padding:8px;">Reason</th>
                    <th style="padding:8px;">Date</th>
                    <th style="padding:8px;">Final</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $stmt = $db->prepare("SELECT * FROM amounts WHERE user_id = ? ORDER BY date DESC");
            $stmt->execute([$user_id]);
            $has_amt = false;
            foreach ($stmt as $row) {
                $has_amt = true;
                $sign = $row['change'] >= 0 ? '+' : '-';
                echo '<tr>';
                echo '<td style="padding:8px;">' . $sign . '$' . number_format(abs($row['change']),2) . '</td>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['reason']) . '</td>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['date']) . '</td>';
                echo '<td style="padding:8px;">$' . number_format($row['final_amount'],2) . '</td>';
                echo '</tr>';
            }
            if (!$has_amt) echo '<tr><td colspan="4" style="color:#888;text-align:center;">No amount history found.</td></tr>';
            ?>
            </tbody>
        </table>

        <h3 style="color:#ff3366;text-align:center;margin-bottom:18px;">🧾 My Receipts</h3>
        <table style="width:100%;background:#f8f8fa;border-radius:12px;padding:0 0 18px 0;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;">
            <thead>
                <tr style="color:#ff3366;font-weight:bold;">
                    <th style="padding:8px;">Book</th>
                    <th style="padding:8px;">Payment ID</th>
                    <th style="padding:8px;">Amount</th>
                    <th style="padding:8px;">Receipt</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $stmt = $db->prepare("SELECT buy.*, books.title FROM buy LEFT JOIN books ON buy.books = books.id WHERE buy.user_id = ? ORDER BY buy.time DESC");
            $stmt->execute([$user_id]);
            $has_receipt = false;
            foreach ($stmt as $row) {
                $has_receipt = true;
                echo '<tr>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['title'] ?? '-') . '</td>';
                echo '<td style="padding:8px;">' . htmlspecialchars($row['payment_id']) . '</td>';
                echo '<td style="padding:8px;">$' . number_format($row['payment_total'],2) . '</td>';
                echo '<td style="padding:8px;"><a href="receipt.php?payment_id=' . urlencode($row['payment_id']) . '" target="_blank" style="color:#228be6;text-decoration:underline;">View/Print</a></td>';
                echo '</tr>';
            }
            if (!$has_receipt) echo '<tr><td colspan="4" style="color:#888;text-align:center;">No receipts found.</td></tr>';
            ?>
            </tbody>
        </table>

        <h3 style="color:#ff3366;text-align:center;margin-bottom:18px;">⬇️ Download Statement</h3>
        <form method="get" action="statement.php" target="_blank" style="background:#fff0f5;border-radius:12px;padding:18px 24px;margin-bottom:24px;box-shadow:0 2px 8px #ff336611;text-align:center;">
            <button type="submit" style="background:#228be6;color:#fff;padding:10px 28px;border-radius:8px;font-weight:bold;border:none;cursor:pointer;">Download/Print Full Statement</button>
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
