<?php
// receipt.php - NOVEL NEST Payment Receipt
include_once 'includes/db_connect.php';

$payment_id = $_GET['payment_id'] ?? '';
if (!$payment_id) {
    echo '<h2>Invalid payment ID.</h2>';
    exit;
}
$stmt = $db->prepare("SELECT buy.*, users.name AS user_name, users.email, users.unique_id, books.title FROM buy LEFT JOIN users ON buy.user_id = users.id LEFT JOIN books ON buy.books = books.id WHERE buy.payment_id = ?");
$stmt->execute([$payment_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    echo '<h2>Receipt not found.</h2>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - NOVEL NEST</title>
    <style>
        body { background:#f8f8fa; }
        .receipt { max-width:500px; margin:40px auto; background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:32px; }
        .receipt h2 { text-align:center; }
        .receipt-details { font-size:1.1rem; margin:24px 0; }
        .receipt-details b { display:inline-block; width:140px; }
        .print-btn { display:block; margin:24px auto 0 auto; background:#ff3366; color:#fff; border:none; padding:12px 32px; border-radius:8px; font-size:1rem; cursor:pointer; }
    </style>
</head>
<body>
    <div class="receipt">
        <h2>NOVEL NEST<br>Payment Receipt</h2>
        <div class="receipt-details">
            <b>Name:</b> <?= htmlspecialchars($row['user_name']) ?><br>
            <b>Email:</b> <?= htmlspecialchars($row['email']) ?><br>
            <b>User ID:</b> <?= htmlspecialchars($row['unique_id']) ?><br>
            <b>Book:</b> <?= htmlspecialchars($row['title']) ?><br>
            <b>Branch:</b> <?= htmlspecialchars($row['branch']) ?><br>
            <b>Date:</b> <?= htmlspecialchars($row['time']) ?><br>
            <b>Payment ID:</b> <?= htmlspecialchars($row['payment_id']) ?><br>
            <b>Amount:</b> $<?= number_format($row['payment_total'],2) ?><br>
            <b>Method:</b> <?= htmlspecialchars($row['method']) ?><br>
        </div>
        <button class="print-btn" onclick="window.print()">Print Receipt</button>
    </div>
</body>
</html>
