<?php
// admin_password.php - NOVEL NEST Admin Registration Password Step
include_once 'includes/db_connect.php';
session_start();
$err = '';
if (!isset($_SESSION['pending_admin'])) {
    header('Location: register.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_pass = $_POST['admin_pass'] ?? '';
    if ($admin_pass !== '13579') {
        $err = 'Incorrect admin password!';
    } else {
        // Retrieve data from session
        $data = $_SESSION['pending_admin'];
        unset($_SESSION['pending_admin']);
        $name = $data['name'];
        $username = $data['username'];
        $email = $data['email'];
        $phone = $data['phone'];
        $address = $data['address'];
        $password = $data['password'];
        $confirm = $data['confirm'];
        $photo = null;
        $unique_id = $data['unique_id'];
        // Handle photo upload if present
        if ($data['photo'] && !empty($data['photo']['name'])) {
            $target = 'assets/uploads/' . basename($data['photo']['name']);
            if (move_uploaded_file($data['photo']['tmp_name'], $target)) {
                $photo = $target;
            }
        }
        if ($password !== $confirm) {
            $err = 'Passwords do not match!';
        } elseif (!$name || !$username || !$email || !$password) {
            $err = 'Please fill all required fields!';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $db->prepare("INSERT INTO users (name, username, photo, email, phone, address, password, role, unique_id) VALUES (?, ?, ?, ?, ?, ?, ?, 'admin', ?)");
                $stmt->execute([$name, $username, $photo, $email, $phone, $address, $hash, $unique_id]);
                $msg = 'Admin registration successful! You can now <a href="login.php">login</a>.';
            } catch (PDOException $e) {
                $err = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Registration - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; }
        .reg-box { max-width:400px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        .reg-box h2 { text-align:center; }
        .reg-box input { width:100%; margin-bottom:12px; padding:10px; border-radius:8px; border:1px solid #ccc; }
        .reg-box button { width:100%; background:#ff3366; color:#fff; border:none; padding:12px; border-radius:8px; font-size:1rem; cursor:pointer; }
        .err { color:#c00; text-align:center; }
        .msg { color:#080; text-align:center; }
    </style>
</head>
<body>
    <div class="reg-box">
        <h2>Admin Registration</h2>
        <?php if (!empty($err)) echo "<div class='err'>$err</div>"; ?>
        <?php if (!empty($msg)) { echo "<div class='msg'>$msg</div>"; exit; } ?>
        <form method="post">
            <label>Enter Admin Password:</label>
            <input type="password" name="admin_pass" required autofocus>
            <button type="submit">Submit</button>
        </form>
        <div style="text-align:center;margin-top:12px;">
            <a href="register.php">Back to Register</a>
        </div>
    </div>
</body>
</html>
