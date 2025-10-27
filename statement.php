<?php
// statement.php - NOVEL NEST Full User Statement
include_once 'includes/db_connect.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo '<h2>Login required.</h2>';
    exit;
}
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Full Statement - NOVEL NEST</title>
    <style>
        body {
            background: #f8f8fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
        }
        .statement {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 6px 32px #ff336644;
            padding: 38px 38px 28px 38px;
            position: relative;
        }
        .brand-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            margin-bottom: 18px;
        }
        .brand-header img {
            height: 60px;
            border-radius: 12px;
            box-shadow: 0 2px 8px #ff336633;
        }
        .brand-header .brand-title {
            font-size: 2.2rem;
            font-weight: bold;
            color: #ff3366;
            letter-spacing: 2px;
            text-shadow: 0 2px 8px #ff336688;
        }
        h2 {
            text-align: center;
            color: #18181b;
            margin-bottom: 8px;
            font-size: 2rem;
            letter-spacing: 1px;
        }
        .user-info {
            text-align: center;
            margin-bottom: 24px;
            font-size: 1.08rem;
            color: #444;
        }
        .section-title {
            color: #ff3366;
            font-size: 1.3rem;
            margin: 32px 0 12px 0;
            text-align: left;
            border-left: 6px solid #ff3366;
            padding-left: 12px;
            letter-spacing: 1px;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 18px;
            background: #f8f8fa;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px #ff336611;
        }
        th, td {
            padding: 10px 12px;
            text-align: left;
        }
        th {
            background: #ff3366;
            color: #fff;
            font-weight: bold;
            font-size: 1.05rem;
            border-bottom: 2px solid #fffbe6;
        }
        tr:nth-child(even) td {
            background: #fffbe6;
        }
        tr:nth-child(odd) td {
            background: #fff;
        }
        td {
            color: #222;
            font-size: 1.04rem;
        }
        .no-data {
            color: #888;
            text-align: center;
            font-style: italic;
        }
        .print-btn {
            display: block;
            margin: 36px auto 0 auto;
            background: linear-gradient(90deg, #ff3366 0%, #ffb347 100%);
            color: #fff;
            border: none;
            padding: 14px 38px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px #ff336633;
            transition: background 0.2s;
        }
        .print-btn:hover {
            background: linear-gradient(90deg, #ffb347 0%, #ff3366 100%);
        }
        @media print {
            body { background: #fff; }
            .statement { box-shadow: none; border: none; }
            .print-btn { display: none; }
            .brand-header img { filter: grayscale(1); }
        }
    </style>
</head>
<body>
    <div class="statement">
        <div class="brand-header">
            <img src="assets/logo.png" alt="NOVEL NEST" onerror="this.style.display='none'">
            <span class="brand-title">NOVEL NEST</span>
        </div>
        <h2>Full User Statement</h2>
        <div class="user-info">
            <b>Name:</b> <?= htmlspecialchars($user['name']) ?> &nbsp; | &nbsp;
            <b>Email:</b> <?= htmlspecialchars($user['email']) ?> &nbsp; | &nbsp;
            <b>User ID:</b> <?= htmlspecialchars($user['unique_id']) ?>
        </div>

        <!-- Buy History -->
        <div class="section-title">🛒 Buy History</div>
        <table>
            <tr>
                <th>Book</th>
                <th>Branch</th>
                <th>Date</th>
                <th>Method</th>
                <th>Payment</th>
            </tr>
            <?php
            $stmt = $db->prepare("SELECT buy.*, books.title FROM buy LEFT JOIN books ON buy.books = books.id WHERE buy.user_id = ? ORDER BY buy.time DESC");
            $stmt->execute([$user_id]);
            $has_data = false;
            foreach ($stmt as $row) {
                $has_data = true;
                echo '<tr><td>' . htmlspecialchars($row['title']) . '</td><td>' . htmlspecialchars($row['branch']) . '</td><td>' . htmlspecialchars($row['time']) . '</td><td>' . htmlspecialchars($row['method']) . '</td><td>$' . number_format($row['payment_total'],2) . '</td></tr>';
            }
            if (!$has_data) echo '<tr><td colspan="5" class="no-data">No buy history found.</td></tr>';
            ?>
        </table>

        <!-- Lend History -->
        <div class="section-title">📚 Lend History</div>
        <table>
            <tr>
                <th>Book</th>
                <th>Branch</th>
                <th>Received</th>
                <th>Return by</th>
                <th>Returned</th>
                <th>Method</th>
            </tr>
            <?php
            $stmt = $db->prepare("SELECT lend.*, books.title FROM lend LEFT JOIN books ON lend.books = books.id WHERE lend.user_id = ? ORDER BY lend.received_time DESC");
            $stmt->execute([$user_id]);
            $has_data = false;
            foreach ($stmt as $row) {
                $has_data = true;
                echo '<tr><td>' . htmlspecialchars($row['title']) . '</td><td>' . htmlspecialchars($row['branch']) . '</td><td>' . htmlspecialchars($row['received_time']) . '</td><td>' . htmlspecialchars($row['return_time']) . '</td><td>' . htmlspecialchars($row['returned_time']) . '</td><td>' . htmlspecialchars($row['method']) . '</td></tr>';
            }
            if (!$has_data) echo '<tr><td colspan="6" class="no-data">No lend history found.</td></tr>';
            ?>
        </table>

        <!-- Attendance -->
        <div class="section-title">🕒 Attendance</div>
        <table>
            <tr>
                <th>Branch</th>
                <th>Date</th>
                <th>Entry</th>
                <th>Exit</th>
            </tr>
            <?php
            $stmt = $db->prepare("SELECT branch_entries.*, branches.name AS branch_name FROM branch_entries LEFT JOIN branches ON branch_entries.branch_id = branches.id WHERE branch_entries.user_id = ? ORDER BY branch_entries.date DESC, branch_entries.entry_time DESC");
            $stmt->execute([$user_id]);
            $has_data = false;
            foreach ($stmt as $row) {
                $has_data = true;
                echo '<tr><td>' . htmlspecialchars($row['branch_name']) . '</td><td>' . htmlspecialchars($row['date']) . '</td><td>' . htmlspecialchars($row['entry_time']) . '</td><td>' . htmlspecialchars($row['leave_time']) . '</td></tr>';
            }
            if (!$has_data) echo '<tr><td colspan="4" class="no-data">No attendance found.</td></tr>';
            ?>
        </table>

        <!-- Amounts -->
        <div class="section-title">💰 Amounts</div>
        <table>
            <tr>
                <th>Change</th>
                <th>Reason</th>
                <th>Date</th>
                <th>Final Amount</th>
            </tr>
            <?php
            $stmt = $db->prepare("SELECT * FROM amounts WHERE user_id = ? ORDER BY date DESC");
            $stmt->execute([$user_id]);
            $has_data = false;
            foreach ($stmt as $row) {
                $has_data = true;
                $sign = $row['change'] >= 0 ? '+' : '-';
                echo '<tr><td>' . $sign . '$' . number_format(abs($row['change']),2) . '</td><td>' . htmlspecialchars($row['reason']) . '</td><td>' . htmlspecialchars($row['date']) . '</td><td>$' . number_format($row['final_amount'],2) . '</td></tr>';
            }
            if (!$has_data) echo '<tr><td colspan="4" class="no-data">No amount history found.</td></tr>';
            ?>
        </table>

        <button class="print-btn" onclick="window.print()">Print Statement</button>
    </div>
</body>
</html>
