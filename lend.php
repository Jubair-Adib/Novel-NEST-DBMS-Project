<?php
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Fetch lists for dropdowns
$user_list = $db->query("SELECT id, name FROM users ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$branch_list = $db->query("SELECT id, name FROM branches ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$book_list = $db->query("SELECT id, title FROM books ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_lend'])) {
    $id = intval($_POST['lend_id']);
    $user_id = intval($_POST['user_id']);
    $branch_id = intval($_POST['branch_id']);
    $book_id = intval($_POST['book_id']);
    $received_time = $_POST['received_time'];
    $return_time = $_POST['return_time'];
    $status = $_POST['status'];
    $stmt = $db->prepare("UPDATE lend SET user_id=?, branch_id=?, book_id=?, received_time=?, return_time=?, status=? WHERE id=?");
    $stmt->execute([$user_id, $branch_id, $book_id, $received_time, $return_time, $status, $id]);
    $msg = "Lending record updated!";
}

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_lend'])) {
    $user_id = intval($_POST['user_id']);
    $branch_id = intval($_POST['branch_id']);
    $book_id = intval($_POST['book_id']);
    $received_time = $_POST['received_time'];
    $return_time = $_POST['return_time'];
    $status = $_POST['status'];
    $stmt = $db->prepare("INSERT INTO lend (user_id, branch_id, book_id, received_time, return_time, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $branch_id, $book_id, $received_time, $return_time, $status]);
    $msg = "Lending record added!";
}

// Fetch lending records
$lends = $db->query("SELECT lend.*, users.name AS user_name, books.title AS book_title, branches.name AS branch_name
    FROM lend
    LEFT JOIN users ON lend.user_id = users.id
    LEFT JOIN books ON lend.book_id = books.id
    LEFT JOIN branches ON lend.branch_id = branches.id
    ORDER BY lend.received_time DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Lending - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; font-family:sans-serif; margin:0; }
        header, footer { background:#222; color:#fff; padding:24px 0; text-align:center; }
        main { max-width:900px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        h1, h2 { color:#ff3366; margin-bottom:16px; }
        form.update-lend-form { display:flex; flex-wrap:wrap; gap:16px; align-items:center; margin-bottom:32px; background:#f9f9fc; padding:16px; border-radius:12px; }
        form.update-lend-form > * { flex:1 1 180px; min-width:120px; }
        form.update-lend-form button { flex:0 0 auto; }
        table { width:100%; border-collapse:collapse; margin-top:24px; }
        th, td { border:1px solid #e0e0e0; padding:8px; text-align:center; }
        th { background:#f3f3f3; }
        .msg { color:green; text-align:center; margin-bottom:16px; }
        .back-btn { background:#ff3366; color:#fff; border:none; padding:10px 24px; border-radius:24px; font-size:1rem; cursor:pointer; text-decoration:none; }
        @media (max-width:700px) {
            main { padding:10px; }
            form.update-lend-form { flex-direction:column; gap:8px; }
        }
    </style>
</head>
<body>
    <header>
        <h1>NOVEL NEST Admin &mdash; Update Lending</h1>
    </header>
    <main>
        <?php if (!empty($msg)): ?>
            <div class="msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <h2>Lending Records</h2>
        <!-- Add Lend Form -->
        <form method="post" class="update-lend-form" style="margin-bottom:24px;">
            <select name="user_id" required>
                <option value="">Select User</option>
                <?php foreach ($user_list as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="branch_id" required>
                <option value="">Select Branch</option>
                <?php foreach ($branch_list as $br): ?>
                    <option value="<?= $br['id'] ?>"><?= htmlspecialchars($br['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="book_id" required>
                <option value="">Select Book</option>
                <?php foreach ($book_list as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['title']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="received_time" required>
            <input type="date" name="return_time">
            <select name="status" required>
                <option value="lent">Lent</option>
                <option value="returned">Returned</option>
                <option value="overdue">Overdue</option>
            </select>
            <button type="submit" name="add_lend" class="back-btn">Add Lend</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Branch</th>
                    <th>Book</th>
                    <th>Received Time</th>
                    <th>Return Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lends as $l): ?>
                <tr>
                    <td><?= htmlspecialchars($l['id']) ?></td>
                    <td><?= htmlspecialchars($l['user_name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($l['branch_name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($l['book_title'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($l['received_time']) ?></td>
                    <td><?= htmlspecialchars($l['return_time']) ?></td>
                    <td><?= htmlspecialchars($l['status']) ?></td>
                    <td>
                        <?php if (isset($_GET['edit']) && $_GET['edit'] == $l['id']): ?>
                        <form method="post" class="update-lend-form">
                            <input type="hidden" name="lend_id" value="<?= $l['id'] ?>">
                            <select name="user_id" required>
                                <?php foreach ($user_list as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= $l['user_id'] == $u['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <select name="branch_id" required>
                                <?php foreach ($branch_list as $br): ?>
                                    <option value="<?= $br['id'] ?>" <?= $l['branch_id'] == $br['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($br['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <select name="book_id" required>
                                <?php foreach ($book_list as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= $l['book_id'] == $b['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($b['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="date" name="received_time" value="<?= $l['received_time'] ?>" required>
                            <input type="date" name="return_time" value="<?= $l['return_time'] ?>">
                            <select name="status" required>
                                <option value="lent" <?= $l['status'] == 'lent' ? 'selected' : '' ?>>Lent</option>
                                <option value="returned" <?= $l['status'] == 'returned' ? 'selected' : '' ?>>Returned</option>
                                <option value="overdue" <?= $l['status'] == 'overdue' ? 'selected' : '' ?>>Overdue</option>
                            </select>
                            <button type="submit" name="update_lend" class="back-btn">Save</button>
                            <a href="lend.php" class="back-btn" style="background:#888;">Cancel</a>
                        </form>
                        <?php else: ?>
                        <a href="lend.php?edit=<?= $l['id'] ?>" class="back-btn">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div style="text-align:center;margin-top:24px;">
            <a href="admin.php" class="back-btn">Back to Admin Dashboard</a>
        </div>
    </main>
    <footer>
        &copy; <?= date('Y') ?> NOVEL NEST. All rights reserved.
    </footer>
</body>
</html>