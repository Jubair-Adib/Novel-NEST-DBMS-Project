<?php
session_start();
require_once 'includes/db_connect.php';

// Handle user message submission
if (isset($_POST['send_message']) && isset($_SESSION['user_id'])) {
    $branch = trim($_POST['branch']);
    $message = trim($_POST['message']);
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? '';
    if ($branch && $message) {
        $stmt = $db->prepare("INSERT INTO messages (user_id, user_name, branch, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $user_name, $branch, $message]);
        $success = "Message sent!";
    }
}

// Filter example
$filter = $_GET['filter'] ?? 'all';
$where = '';
if ($filter == 'unread') $where = 'WHERE read = 0';
elseif ($filter == 'replied') $where = 'WHERE replied = 1';
elseif ($filter == 'not_replied') $where = 'WHERE replied = 0';

$stmt = $db->query("SELECT * FROM messages $where ORDER BY created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['send_reply'])) {
    $msg_id = intval($_POST['msg_id']);
    $reply = trim($_POST['reply']);
    $admin_id = $_SESSION['admin_id'] ?? null;
    if ($admin_id && $reply) {
        $stmt = $db->prepare("UPDATE messages SET replied_message = ?, replied = 1, admin_id = ? WHERE id = ?");
        $stmt->execute([$reply, $admin_id, $msg_id]);
    }
}

// List of all branches
$branches = [
    "Dhaka Central", "Uttara", "Dhanmondi", "Mirpur", "Banani", "Gulshan", "Bashundhara", "Mohakhali",
    "Chittagong Central", "Khulna Main", "Rajshahi", "Sylhet", "Barisal", "Rangpur", "Mymensingh",
    "Comilla", "Jessore", "Bogura", "Faridpur", "Gazipur", "Narayanganj", "Feni"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact List - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background: #f8f8fa; }
        .contact-container { max-width: 900px; margin: 60px auto; background: #fff; border-radius: 20px; box-shadow: 0 4px 24px #0002; padding: 40px; }
        h2 { text-align: center; margin-bottom: 32px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #228be6; color: #fff; }
        tr:nth-child(even) { background: #f3f7fa; }
        @media (max-width: 800px) {
            .contact-container { padding: 10px; }
            table, th, td { font-size: 0.95rem; }
        }
    </style>
</head>
<body>
    <div style="background:#222;padding:0 0 2px 0;">
        <div style="max-width:900px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:18px 32px 10px 32px;">
            <div style="font-size:2.1rem;font-weight:bold;color:#fff;letter-spacing:2px;font-family:sans-serif;text-shadow:0 2px 8px #0002;">
                NOVEL NEST
            </div>
            <div>
                <a href="index.php" style="background:#ff3366;color:#fff;font-weight:600;padding:8px 22px;border-radius:8px;text-decoration:none;box-shadow:0 2px 8px #0001;transition:background .2s;">Home</a>
            </div>
        </div>
    </div>
    <div class="contact-container">
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="contact-message-box" style="margin-top:40px;max-width:500px;margin-left:auto;margin-right:auto;background:linear-gradient(120deg,#f8f8fa 70%,#228be6 100%);border-radius:18px;box-shadow:0 4px 24px #228be622;padding:32px 28px;">
            <h3 style="color:#228be6;text-align:center;margin-bottom:18px;font-size:1.4rem;letter-spacing:1px;">Send Message to Branch Admin</h3>
            <?php if (!empty($success)): ?>
                <div style="color:green;margin-bottom:10px;text-align:center;font-weight:bold;"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <form method="post" action="contact.php" style="display:flex;flex-direction:column;gap:18px;">
                <div>
                    <label for="branch" style="font-weight:600;color:#222;">Select Branch:</label>
                    <select name="branch" id="branch" required style="width:100%;padding:10px 12px;border-radius:8px;border:1.5px solid #228be6;font-size:1rem;margin-top:6px;">
                        <option value="">--Select Branch--</option>
                        <?php foreach ($branches as $b): ?>
                            <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="message" style="font-weight:600;color:#222;">Your Message:</label>
                    <textarea name="message" id="message" rows="4" required style="width:100%;border-radius:8px;border:1.5px solid #228be6;padding:12px;font-size:1.08rem;margin-top:6px;resize:vertical;background:#fafdff;"></textarea>
                </div>
                <button type="submit" name="send_message" style="background:linear-gradient(90deg,#228be6 60%,#ff3366 100%);color:#fff;padding:12px 0;border-radius:8px;font-size:1.1rem;font-weight:bold;letter-spacing:1px;border:none;box-shadow:0 2px 8px #228be633;transition:background .2s;cursor:pointer;">
                    Send Message
                </button>
            </form>
        </div>
        <?php endif; ?>
        <h2>Branch Contact List</h2>
        <table>
            <tr>
                <th>Branch Name</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Email</th>
            </tr>
            <tr><td>Dhaka Central</td><td>Motijheel, Dhaka</td><td>01711-100001</td><td>dhaka.central@novelnest.com</td></tr>
            <tr><td>Uttara</td><td>Sector 7, Uttara, Dhaka</td><td>01711-100002</td><td>uttara@novelnest.com</td></tr>
            <tr><td>Dhanmondi</td><td>Dhanmondi 27, Dhaka</td><td>01711-100003</td><td>dhanmondi@novelnest.com</td></tr>
            <tr><td>Mirpur</td><td>Mirpur 10, Dhaka</td><td>01711-100004</td><td>mirpur@novelnest.com</td></tr>
            <tr><td>Banani</td><td>Banani, Dhaka</td><td>01711-100005</td><td>banani@novelnest.com</td></tr>
            <tr><td>Gulshan</td><td>Gulshan 2, Dhaka</td><td>01711-100006</td><td>gulshan@novelnest.com</td></tr>
            <tr><td>Bashundhara</td><td>Bashundhara R/A, Dhaka</td><td>01711-100007</td><td>bashundhara@novelnest.com</td></tr>
            <tr><td>Mohakhali</td><td>Mohakhali, Dhaka</td><td>01711-100008</td><td>mohakhali@novelnest.com</td></tr>
            <tr><td>Chittagong Central</td><td>GEC Circle, Chattogram</td><td>01811-200001</td><td>ctg.central@novelnest.com</td></tr>
            <tr><td>Khulna Main</td><td>Shibbari, Khulna</td><td>01911-300001</td><td>khulna@novelnest.com</td></tr>
            <tr><td>Rajshahi</td><td>Shaheb Bazar, Rajshahi</td><td>01711-400001</td><td>rajshahi@novelnest.com</td></tr>
            <tr><td>Sylhet</td><td>Zindabazar, Sylhet</td><td>01711-500001</td><td>sylhet@novelnest.com</td></tr>
            <tr><td>Barisal</td><td>Band Road, Barisal</td><td>01711-600001</td><td>barisal@novelnest.com</td></tr>
            <tr><td>Rangpur</td><td>Jahaj Company, Rangpur</td><td>01711-700001</td><td>rangpur@novelnest.com</td></tr>
            <tr><td>Mymensingh</td><td>Town Hall, Mymensingh</td><td>01711-800001</td><td>mymensingh@novelnest.com</td></tr>
            <tr><td>Comilla</td><td>Kandirpar, Comilla</td><td>01711-900001</td><td>comilla@novelnest.com</td></tr>
            <tr><td>Jessore</td><td>Rail Road, Jessore</td><td>01712-100001</td><td>jessore@novelnest.com</td></tr>
            <tr><td>Bogura</td><td>Satmatha, Bogura</td><td>01712-200001</td><td>bogura@novelnest.com</td></tr>
            <tr><td>Faridpur</td><td>Goalchamot, Faridpur</td><td>01712-300001</td><td>faridpur@novelnest.com</td></tr>
            <tr><td>Gazipur</td><td>Chowrasta, Gazipur</td><td>01712-400001</td><td>gazipur@novelnest.com</td></tr>
            <tr><td>Narayanganj</td><td>Chashara, Narayanganj</td><td>01712-500001</td><td>narayanganj@novelnest.com</td></tr>
            <tr><td>Feni</td><td>Trunk Road, Feni</td><td>01712-600001</td><td>feni@novelnest.com</td></tr>
        </table>
        <div style="margin-top:32px;">
            <a href="index.php" style="background:#ff3366;color:#fff;font-weight:600;padding:8px 22px;border-radius:8px;text-decoration:none;box-shadow:0 2px 8px #0001;transition:background .2s;">&larr; Back to Home</a>
        </div>
    </div>
    <footer style="background:#222;margin-top:48px;padding:0;">
        <div style="max-width:900px;margin:0 auto;padding:18px 32px 10px 32px;display:flex;align-items:center;justify-content:space-between;">
            <div style="color:#fff;font-size:1.1rem;letter-spacing:1px;">&copy; <?= date('Y') ?> NOVEL NEST. All rights reserved.</div>
            <div style="color:#fff;">
                <a href="about.php" style="color:#fff;text-decoration:underline;margin-right:18px;">About Us</a>
            </div>
        </div>
    </footer>
</body>
</html>