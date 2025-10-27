<?php
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$user_list = $db->query("SELECT id, name FROM users ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$branch_list = $db->query("SELECT id, name FROM branches ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$book_list = $db->query("SELECT id, title, price FROM books ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sale'])) {
    $user_id = intval($_POST['user_id']);
    $branch_id = intval($_POST['branch_id']);
    $book_ids = $_POST['book_ids'] ?? [];
    $payment_total = floatval($_POST['payment_total']);
    $method = $_POST['method'];
    $payment_id = $_POST['payment_id'];
    $date = $_POST['date'] . ' ' . ($_POST['time'] ?? '00:00:00');
    foreach ($book_ids as $book_id) {
        $stmt = $db->prepare("INSERT INTO buy (user_id, branch_id, books, payment_total, method, payment_id, time) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $branch_id, $book_id, $payment_total, $method, $payment_id, $date]);
    }
    $msg = "Sale(s) added!";
}

$sales = $db->query("SELECT buy.*, users.name AS user_name, books.title AS book_title, branches.name AS branch_name
    FROM buy
    LEFT JOIN users ON buy.user_id = users.id
    LEFT JOIN books ON buy.books = books.id
    LEFT JOIN branches ON buy.branch_id = branches.id
    ORDER BY buy.time DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Sold History - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background:#f8f8fa; font-family:sans-serif; margin:0; }
        header, footer { background:#222; color:#fff; padding:24px 0; text-align:center; }
        main { max-width:900px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        h1, h2 { color:#ff3366; margin-bottom:16px; }
        form.add-sale-form { display:flex; flex-wrap:wrap; gap:16px; align-items:center; margin-bottom:32px; background:#f9f9fc; padding:16px; border-radius:12px; }
        form.add-sale-form > * { flex:1 1 180px; min-width:120px; }
        form.add-sale-form button { flex:0 0 auto; }
        table { width:100%; border-collapse:collapse; margin-top:24px; }
        th, td { border:1px solid #e0e0e0; padding:8px; text-align:center; }
        th { background:#f3f3f3; }
        .msg { color:green; text-align:center; margin-bottom:16px; }
        .back-btn { background:#ff3366; color:#fff; border:none; padding:10px 24px; border-radius:24px; font-size:1rem; cursor:pointer; text-decoration:none; }
        @media (max-width:700px) {
            main { padding:10px; }
            form.add-sale-form { flex-direction:column; gap:8px; }
        }
    </style>
</head>
<body>
    <header>
        <h1>NOVEL NEST Admin &mdash; Book Sold History</h1>
    </header>
    <main>
        <?php if (!empty($msg)): ?>
            <div class="msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <form method="post" class="add-sale-form" autocomplete="off">
            <select name="user_id" required>
                <option value="">User</option>
                <?php foreach ($user_list as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="branch_id" required>
                <option value="">Branch</option>
                <?php foreach ($branch_list as $br): ?>
                    <option value="<?= $br['id'] ?>"><?= htmlspecialchars($br['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="book_ids[]" multiple required id="book-select">
                <?php foreach ($book_list as $b): ?>
                    <option value="<?= $b['id'] ?>" data-price="<?= $b['price'] ?>">
                        <?= htmlspecialchars($b['title']) ?> (<?= $b['price'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" step="0.01" name="payment_total" id="payment-total" placeholder="Total Payment" required readonly>
            <input type="text" name="method" placeholder="Method" required>
            <input type="text" name="payment_id" placeholder="Payment ID" required>
            <input type="date" name="date" required>
            <input type="time" name="time" required>
            <button type="submit" name="add_sale" class="back-btn">Add Sale</button>
        </form>
        <h2>Book Sold History</h2>
        <table>
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>User</th>
                    <th>Branch</th>
                    <th>Book</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Payment ID</th>
                    <th>Date/Time</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($sales as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['id']) ?></td>
                    <td><?= htmlspecialchars($s['user_name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($s['branch_name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($s['book_title'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($s['payment_total']) ?></td>
                    <td><?= htmlspecialchars($s['method']) ?></td>
                    <td><?= htmlspecialchars($s['payment_id']) ?></td>
                    <td><?= htmlspecialchars($s['time']) ?></td>
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
    <script>
    // Auto-calculate payment total based on selected books
    document.addEventListener('DOMContentLoaded', function() {
        const bookSelect = document.getElementById('book-select');
        const paymentInput = document.getElementById('payment-total');
        if (bookSelect && paymentInput) {
            bookSelect.addEventListener('change', function() {
                let total = 0;
                Array.from(bookSelect.selectedOptions).forEach(opt => {
                    total += parseFloat(opt.getAttribute('data-price')) || 0;
                });
                paymentInput.value = total.toFixed(2);
            });
        }
    });
    </script>
</body>
</html>
