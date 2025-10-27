<?php
// forgot_password.php - NOVEL NEST Password Reset Request
// Add columns for password reset code and expiry if not present
// ALTER TABLE users ADD COLUMN reset_code TEXT;
// ALTER TABLE users ADD COLUMN reset_expiry TEXT;

include_once 'includes/db_connect.php';
$step = 1;
$msg = '';
$err = '';
$email = '';
$phone = '';
$matched_users = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if (!$email || !$phone) {
        $err = 'Please enter both email and mobile number.';
    } else {
        $stmt = $db->prepare("SELECT id, name, unique_id FROM users WHERE email = ? AND phone = ?");
        $stmt->execute([$email, $phone]);
        $matched_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($matched_users && count($matched_users) > 0) {
            $step = 2;
        } else {
            $err = 'No user found with that email and mobile number.';
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_user'])) {
    $selected_user_id = intval($_POST['selected_user_id'] ?? 0);
    $stmt = $db->prepare("SELECT id, name, unique_id FROM users WHERE id = ?");
    $stmt->execute([$selected_user_id]);
    $selected_user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($selected_user) {
        $step = 3;
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $selected_user_id = $selected_user['id'];
    } else {
        $err = 'Invalid user selection.';
        $step = 1;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $selected_user_id = intval($_POST['selected_user_id'] ?? 0);
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    if ($new_password === $confirm_password && strlen($new_password) >= 6) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET password=? WHERE id=?")
            ->execute([$hash, $selected_user_id]);
        $msg = 'Password reset successful! <a href="login.php">Login</a>';
        $step = 4;
    } else {
        $err = 'Passwords do not match or are too short (min 6 chars).';
        $step = 3;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; }
        .reset-box { max-width:400px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        .reset-box h2 { text-align:center; }
        .reset-box input { width:100%; margin-bottom:12px; padding:10px; border-radius:8px; border:1px solid #ccc; }
        .reset-box button { width:100%; background:#ff3366; color:#fff; border:none; padding:12px; border-radius:8px; font-size:1rem; cursor:pointer; }
        .msg { color:#080; text-align:center; }
        .err { color:#c00; text-align:center; }
    </style>
</head>
<body>
    <div class="reset-box">
        <h2>Forgot Password</h2>
        <?php if ($msg) echo "<div class='msg'>$msg</div>"; ?>
        <?php if ($err) echo "<div class='err'>$err</div>"; ?>
        <?php if ($step === 1): ?>
        <form method="post">
            <input type="email" name="email" placeholder="Enter your email" required value="<?= htmlspecialchars($email) ?>">
            <input type="text" name="phone" placeholder="Enter your mobile number" required value="<?= htmlspecialchars($phone) ?>">
            <button type="submit" name="request_reset" style="width:100%;background:#ff3366;color:#fff;border:none;padding:12px;border-radius:8px;font-size:1rem;cursor:pointer;margin-bottom:10px;">Find My Account</button>
            <button type="button" onclick="window.location.href='login.php'" style="width:100%;background:#18181b;color:#fff;border:none;padding:12px;border-radius:8px;font-size:1rem;cursor:pointer;">Go to Login Page</button>
        </form>
        <?php elseif ($step === 2): ?>
        <form method="post">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <input type="hidden" name="phone" value="<?= htmlspecialchars($phone) ?>">
            <div style="margin-bottom:12px;font-weight:600;">Select your account:</div>
            <?php foreach ($matched_users as $u): ?>
                <div style="margin-bottom:10px;padding:10px 16px;background:#f8f8fa;border-radius:8px;box-shadow:0 2px 8px #ff336611;display:flex;align-items:center;gap:16px;">
                    <input type="radio" name="selected_user_id" value="<?= $u['id'] ?>" required>
                    <span style="font-size:1.1rem;color:#ff3366;font-weight:bold;">ID: <?= htmlspecialchars($u['unique_id']) ?></span>
                    <span style="font-size:1.1rem;">Name: <?= htmlspecialchars($u['name']) ?></span>
                </div>
            <?php endforeach; ?>
            <button type="submit" name="select_user">Continue</button>
        </form>
        <?php elseif ($step === 3): ?>
        <form method="post">
            <input type="hidden" name="selected_user_id" value="<?= htmlspecialchars($selected_user_id) ?>">
            <div style="margin-bottom:12px;font-weight:600;">Set a new password for your account:</div>
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <button type="submit" name="reset_password">Reset Password</button>
        </form>
        <?php elseif ($step === 4): ?>
        <div style="text-align:center;margin-top:18px;">
            <a href="login.php" style="color:#fff;background:#ff3366;padding:8px 22px;border-radius:8px;text-decoration:none;font-weight:600;">Back to Login</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
