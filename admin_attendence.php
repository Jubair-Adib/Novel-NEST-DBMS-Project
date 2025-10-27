<?php
// filepath: /Users/adib/Project/NN/admin_attendence.php
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin.php');
    exit;
}
$msg = '';

// Fetch branches for dropdown
$branch_list = $db->query("SELECT id, name FROM branches ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Add attendance
if (isset($_POST['add_attendance'])) {
    $user_id = intval($_POST['user_id']);
    $branch_id = intval($_POST['branch_id']);
    $date = $_POST['date'] ?? date('Y-m-d');
    $entry_time = $_POST['entry_time'] ?? '';
    $leave_time = $_POST['leave_time'] ?? '';
    $stmt = $db->prepare("INSERT INTO branch_entries (user_id, branch_id, date, entry_time, leave_time) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $branch_id, $date, $entry_time, $leave_time]);
    $msg = 'Attendance added!';
}

// Delete selected
if (isset($_POST['delete_selected']) && isset($_POST['entry_ids'])) {
    $ids = $_POST['entry_ids'];
    $in = implode(',', array_fill(0, count($ids), '?'));
    $db->prepare("DELETE FROM branch_entries WHERE id IN ($in)")->execute($ids);
    $msg = 'Selected attendances deleted!';
}

// Delete all
if (isset($_POST['delete_all'])) {
    $db->exec("DELETE FROM branch_entries");
    $msg = 'All attendances deleted!';
}

// Delete single
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->prepare("DELETE FROM branch_entries WHERE id=?")->execute([$id]);
    $msg = 'Attendance deleted!';
}

// Edit attendance
if (isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $user_id = intval($_POST['edit_user_id']);
    $branch_id = intval($_POST['edit_branch_id']);
    $entry_time = $_POST['edit_entry_time'];
    $leave_time = $_POST['edit_leave_time'];
    $db->prepare("UPDATE branch_entries SET user_id=?, branch_id=?, entry_time=?, leave_time=? WHERE id=?")
        ->execute([$user_id, $branch_id, $entry_time, $leave_time, $id]);
    $msg = 'Attendance updated!';
}

// --- SEARCH ---
$search_id_name = trim($_GET['search_id_name'] ?? '');
$search_branch = intval($_GET['search_branch'] ?? 0);
$search_date = $_GET['search_date'] ?? '';
$where = [];
$params = [];
if ($search_id_name !== '') {
    $where[] = "(u.id = ? OR u.name LIKE ?)";
    $params[] = $search_id_name;
    $params[] = "%$search_id_name%";
}
if ($search_branch) {
    $where[] = "be.branch_id = ?";
    $params[] = $search_branch;
}
if ($search_date) {
    $where[] = "be.date = ?";
    $params[] = $search_date;
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$entries_stmt = $db->prepare("SELECT be.*, u.name as user_name, b.name as branch_name FROM branch_entries be
    LEFT JOIN users u ON be.user_id = u.id
    LEFT JOIN branches b ON be.branch_id = b.id
    $where_sql
    ORDER BY be.date DESC, be.entry_time ASC");
$entries_stmt->execute($params);
$entries = $entries_stmt->fetchAll(PDO::FETCH_ASSOC);

// For user name autofill
$user_js = [];
foreach ($db->query("SELECT id, name FROM users") as $u) {
    $user_js[$u['id']] = $u['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Attendance Management - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; margin:0; font-family:sans-serif; }
        .header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 40px; background: linear-gradient(90deg, #18181b 60%, #ff3366 100%);
            color: #fff; box-shadow: 0 2px 8px #ff336633;
        }
        .logo { display: flex; align-items: center; }
        .logo img { height: 48px; margin-right: 16px; }
        .login-btn {
            background: #ff3366; color: #fff; border: none; padding: 10px 24px;
            border-radius: 24px; font-size: 1rem; cursor: pointer;
            box-shadow: 0 2px 8px #ff336633; text-decoration: none; margin-left: 12px;
        }
        .admin-box { max-width:1000px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        h2 { text-align:center; color:#ff3366; }
        form { margin-bottom:32px; }
        input, select { width:100%; margin-bottom:10px; padding:8px; border-radius:8px; border:1px solid #ccc; }
        .msg { color:#080; text-align:center; font-weight:bold; margin-bottom:18px; }
        table { width:100%; border-collapse:collapse; margin-top:24px; }
        th, td { border:1px solid #ccc; padding:8px; text-align:center; }
        th { background:#eee; }
        .actions a, .actions button { margin:0 6px; }
        .footer {
            background: linear-gradient(90deg, #18181b 60%, #ff3366 100%);
            color: #fff; padding: 32px 0 16px 0; text-align: center;
        }
        .footer .socials a { margin: 0 12px; color: #fff; font-size: 1.5rem; text-decoration: none; transition: color .2s; }
        .footer .socials a:hover { color: #ff3366; }
        .footer .links { margin: 16px 0; }
        .footer .links a { margin: 0 10px; color: #ff3366; text-decoration: none; font-weight: bold; }
        .footer .links a:hover { text-decoration: underline; color: #fff; }
        .footer .about { margin-top: 16px; }
        .footer .about a { color: #fff; text-decoration: underline; }
        .footer .about a:hover { color: #ff3366; }
        .action-btn { background: #0099cc; color: #fff; border: none; padding: 6px 16px; border-radius: 8px; font-size: 0.95rem; cursor: pointer; margin: 0 2px; }
        .edit-btn { background: #22bb33; }
        .delete-btn { background: #ff3366; }
        .select-actions { display: flex; justify-content: space-between; margin-top: 16px; }
    </style>
    <script>
        // Autofill user name by ID
        function autofillName() {
            var userId = document.getElementById('user_id').value;
            var userName = <?= json_encode($user_js) ?>;
            document.getElementById('user_name').value = userName[userId] || '';
        }
    </script>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="assets/logo.png" alt="Logo" onerror="this.style.display='none'">
            <span style="font-size:2rem;font-weight:bold;letter-spacing:2px;">NOVEL NEST</span>
        </div>
        <button class="login-btn" onclick="window.location.href='admin.php'">Go Back</button>
    </div>
    <div class="admin-box">
        <h2>Attendance Management</h2>
        <?php if ($msg) echo '<div class="msg">'.$msg.'</div>'; ?>
        <form method="post">
            <label>User ID</label>
            <input type="number" name="user_id" id="user_id" oninput="autofillName()" required>
            <label>User Name</label>
            <input type="text" id="user_name" readonly>
            <label>Branch</label>
            <select name="branch_id" required>
                <option value="">Select Branch</option>
                <?php foreach ($branch_list as $br): ?>
                    <option value="<?= $br['id'] ?>"><?= htmlspecialchars($br['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label>Date</label>
            <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
            <label>Entry Time</label>
            <input type="time" name="entry_time" required>
            <label>Leave Time</label>
            <input type="time" name="leave_time">
            <button type="submit" name="add_attendance" class="login-btn" style="width:100%;margin-top:8px;">Add Attendance</button>
        </form>
        <form method="get" style="margin-bottom:16px;display:flex;gap:8px;align-items:center;">
            <input type="text" name="search_id_name" placeholder="Search by ID or Name" style="flex:1;">
            <select name="search_branch" style="flex:1;">
                <option value="">Search by Branch</option>
                <?php foreach ($branch_list as $br): ?>
                    <option value="<?= $br['id'] ?>" <?= $search_branch==$br['id']?'selected':'' ?>><?= htmlspecialchars($br['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="search_date" value="<?= htmlspecialchars($search_date) ?>" style="flex:1;">
            <button type="submit" class="login-btn" style="padding:8px 18px;">Search</button>
            <?php if ($search_id_name || $search_branch || $search_date): ?>
                <a href="admin_attendence.php" style="color:#888;">Clear</a>
            <?php endif; ?>
        </form>
        <form method="post">
        <table>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th>ID</th>
                <th>Name</th>
                <th>Branch</th>
                <th>Date</th>
                <th>Entry</th>
                <th>Leave</th>
                <th>Action</th>
            </tr>
            <?php foreach ($entries as $e): ?>
            <tr>
                <td><input type="checkbox" name="entry_ids[]" value="<?= $e['id'] ?>"></td>
                <td><?= htmlspecialchars($e['user_id']) ?></td>
                <td><?= htmlspecialchars($e['user_name']) ?></td>
                <td><?= htmlspecialchars($e['branch_name']) ?></td>
                <td><?= htmlspecialchars($e['date']) ?></td>
                <td><?= htmlspecialchars($e['entry_time']) ?></td>
                <td><?= htmlspecialchars($e['leave_time']) ?></td>
                <td class="actions">
                    <!-- Edit Form (inline) -->
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="edit_id" value="<?= $e['id'] ?>">
                        <input type="hidden" name="edit_user_id" value="<?= $e['user_id'] ?>">
                        <input type="hidden" name="edit_branch_id" value="<?= $e['branch_id'] ?>">
                        <input type="hidden" name="edit_entry_time" value="<?= $e['entry_time'] ?>">
                        <input type="hidden" name="edit_leave_time" value="<?= $e['leave_time'] ?>">
                        <button type="submit" class="action-btn edit-btn">Edit</button>
                    </form>
                    <a href="admin_attendence.php?delete=<?= $e['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Delete this attendance?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div class="select-actions">
            <button type="submit" name="delete_selected" class="action-btn delete-btn" onclick="return confirm('Delete selected attendances?')">Delete Selected</button>
            <button type="submit" name="delete_all" class="action-btn delete-btn" onclick="return confirm('Delete ALL attendances?')">Delete All</button>
        </div>
        </form>
        <div style="text-align:center;margin-top:24px;">
            <a href="admin.php" class="login-btn" style="background:#ff3366;">Back to Admin Dashboard</a>
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
    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('input[name="entry_ids[]"]');
            for (var cb of checkboxes) cb.checked = this.checked;
        });
    </script>
</body>
</html>