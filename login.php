<?php
// login.php - NOVEL NEST User Login
include_once 'includes/db_connect.php';
session_start();
// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
// Initialize error message        

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$username || !$password) {
        $err = 'Please enter both username/email and password!';
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['unique_id'] = $user['unique_id'];
            header('Location: index.php');
            exit;
        } else {
            $err = 'Invalid credentials!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - NOVEL NEST</title>
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
        .login-box {
            max-width: 400px;
            margin: 48px auto 32px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px #ff336633;
            padding: 36px 32px 32px 32px;
        }
        .login-box h2 {
            text-align: center;
            color: #ff3366;
            font-size: 2rem;
            margin-bottom: 18px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .login-box input {
            width: 100%;
            margin-bottom: 14px;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1.08rem;
            transition: border .2s;
        }
        .login-box input:focus {
            border: 1.5px solid #ff3366;
            outline: none;
        }
        .login-box button {
            width: 100%;
            background: linear-gradient(90deg, #ff3366 0%, #ffb347 100%);
            color: #fff;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 1.08rem;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px #ff336633;
            transition: background .2s, box-shadow .2s;
        }
        .login-box button:hover {
            background: #18181b;
            color: #ff3366;
            box-shadow: 0 4px 16px #18181b44;
        }
        .err {
            background: linear-gradient(90deg, #ff3366 0%, #ffb347 100%);
            color: #fff;
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 1.08rem;
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
        <button class="login-btn" onclick="window.location.href='index.php'">Home</button>
    </div>
    <div style="display:flex;justify-content:center;align-items:center;min-height:70vh;">
        <div class="login-box">
            <h2>Login</h2>
            <?php if ($err) echo "<div class='err'>$err</div>"; ?>
            <form method="post" style="width:100%;">
                <input type="text" name="username" placeholder="Username or Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;margin-top:28px;gap:18px;width:100%;">
                <a href="forgot_password.php" style="display:inline-block;background:linear-gradient(90deg,#ff3366 0%,#ffb347 100%);color:#fff;font-weight:700;padding:10px 32px;border-radius:8px;text-decoration:none;box-shadow:0 2px 8px #ff336633;transition:box-shadow .2s,background .2s;">Forgot Password?</a>
                <span style="font-size:1.08rem;color:#222;">
                    Don't have an account?
                    <a href="register.php" style="color:#ff3366;font-weight:600;text-decoration:none;padding:2px 10px;border-radius:6px;transition:background .2s;">Register</a>
                </span>
            </div>
        </div>
    </div>
    <div class="footer" style="background:#222; color:#fff; padding:32px 0 16px 0; text-align:center;">
        <div class="socials">
            <a href="#" title="Facebook" style="margin:0 12px; color:#fff; font-size:1.5rem; text-decoration:none;">&#x1F426;</a>
            <a href="#" title="YouTube" style="margin:0 12px; color:#fff; font-size:1.5rem; text-decoration:none;">&#x1F4FA;</a>
            <a href="#" title="Twitter" style="margin:0 12px; color:#fff; font-size:1.5rem; text-decoration:none;">&#x1F426;</a>
            <a href="#" title="Instagram" style="margin:0 12px; color:#fff; font-size:1.5rem; text-decoration:none;">&#x1F33A;</a>
        </div>
        <div class="links" style="margin:16px 0;">
            <a href="contact.php" style="margin:0 10px; color:#ff3366; text-decoration:none; font-weight:bold;">Contact List</a> 
            <a href="news.php" style="margin:0 10px; color:#ff3366; text-decoration:none; font-weight:bold;">News</a>
        </div>
        <div class="about" style="margin-top:16px;">
            <a href="about.php" style="color:#fff; text-decoration:underline;">About Us (Click to know)</a>
        </div>
        <div style="margin-top:16px;font-size:.9rem;opacity:.7;">&copy; 2025 NOVEL NEST. All rights reserved.</div>
    </div>
</body>
</html>
