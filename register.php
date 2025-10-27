<?php
// register.php - NOVEL NEST User Registration
include_once 'includes/db_connect.php';

$err = $msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_admin = isset($_POST['is_admin']) && $_POST['is_admin'] === '1';
    $admin_pass = $_POST['admin_pass'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $photo = null;
    // Check if username already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $err = '<div class="colorbox-err">This username is already taken. Please choose another.</div>';
    }
    if ($is_admin) {
        if ($admin_pass !== '1234') {
            $err = 'Incorrect admin password!';
        } else {
            $role = 'admin';
            // Generate admin Library ID: NNA + 6-digit
            $last = $db->query("SELECT unique_id FROM users WHERE role='admin' AND unique_id LIKE 'NNA%' ORDER BY id DESC LIMIT 1")->fetchColumn();
            if ($last && preg_match('/NNA(\\d{6})/', $last, $m)) {
                $num = intval($m[1]) + 1;
            } else {
                $num = 1;
            }
            $unique_id = 'NNA' . str_pad($num, 6, '0', STR_PAD_LEFT);
        }
    } else {
        $role = 'user';
        // Generate user Library ID: NNU + 6-digit
        $last = $db->query("SELECT unique_id FROM users WHERE role='user' AND unique_id LIKE 'NNU%' ORDER BY id DESC LIMIT 1")->fetchColumn();
        if ($last && preg_match('/NNU(\\d{6})/', $last, $m)) {
            $num = intval($m[1]) + 1;
        } else {
            $num = 1;
        }
        $unique_id = 'NNU' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }
    if (!$err) {
        // Handle photo upload
        if (!empty($_FILES['photo']['name'])) {
            $target = 'assets/uploads/' . basename($_FILES['photo']['name']);
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                $photo = $target;
            }
        } else {
            // Assign a random profile photo from assets/profiles/1.jpg ... 25.jpg
            $photo = 'assets/profiles/' . rand(1, 25) . '.jpg';
        }
        // Hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $db->prepare("INSERT INTO users (name, username, photo, email, phone, address, password, role, unique_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $username, $photo, $email, $phone, $address, $hash, $role, $unique_id]);
            $msg = 'Registration successful! You can now <a href="login.php">login</a>.';
        } catch (PDOException $e) {
            $err = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - NOVEL NEST</title>
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
        .reg-box {
            max-width: 400px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 8px #0001;
            padding: 32px;
        }
        .reg-box h2 { text-align: center; }
        .reg-box input, .reg-box textarea {
            width: 100%;
            margin-bottom: 12px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        .reg-box button {
            width: 100%;
            background: #ff3366;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
        }
        .colorbox-err {
            background: linear-gradient(90deg, #ff3366 0%, #ffb347 100%);
            color: #fff;
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-size: 1.1rem;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 2px 12px #ff336633;
            border: 2px solid #ff3366;
        }
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
        <?php if (isset($_SESSION['user_id'])): ?>
            <div style="display:flex;align-items:center;gap:16px;">
                <a href="profile.php" title="Profile">
                    <img src="<?= htmlspecialchars($_SESSION['photo'] ?? 'assets/default-profile.png') ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover;background:#eee;vertical-align:middle;">
                </a>
                <span style="color:#ff3366;font-weight:bold;">
                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                        WELCOME admin <?= htmlspecialchars($_SESSION['name'] ?? '') ?>
                    <?php else: ?>
                        welcome user <?= htmlspecialchars($_SESSION['name'] ?? '') ?>
                    <?php endif; ?>
                </span>
                <a href="logout.php" class="login-btn" style="background:#444;">Logout</a>
            </div>
        <?php else: ?>
            <button class="login-btn" onclick="window.location.href='login.php'">Login</button>
        <?php endif; ?>
    </div>
    <div class="reg-box">
        <h2>Register</h2>
        <?php if ($err) echo $err; ?>
        <?php if ($msg) echo "<div class='msg'>$msg</div>"; ?>
        <form method="post" enctype="multipart/form-data" id="regForm">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone" placeholder="Phone">
            <input type="text" name="address" placeholder="Address">
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm" placeholder="Confirm Password" required>
            <label style="display:block;margin-bottom:12px;text-align:left;">
                <input type="checkbox" name="is_admin" value="1" id="is_admin_cb" onchange="document.getElementById('admin_pass_field').style.display=this.checked?'block':'none'"> Register as Admin
            </label>
            <input type="password" name="admin_pass" id="admin_pass_field" placeholder="Admin Password (1234)" style="display:none;">
            <button type="submit">Register</button>
        </form>
        <div style="text-align:center;margin-top:12px;">
            Already have an account? <a href="login.php" style="color:#ff3366;font-weight:bold;">Login</a>
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
