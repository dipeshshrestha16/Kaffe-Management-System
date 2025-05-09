<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$shift_id = intval($_GET['id']);
$stmt = $mysqli->prepare("SELECT * FROM rpos_balances WHERE id = ?");
$stmt->bind_param("i", $shift_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    die("Shift not found.");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Print Shift Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 70%;
            margin: 0 auto;
            border-collapse: collapse;
        }

        td,
        th {
            padding: 10px 15px;
            border: 1px solid #000;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <h2>Shift Report - <?php echo $data['balance_date']; ?></h2>

    <table>
        <tr>
            <th>Opening Balance</th>
            <td>Rs. <?php echo number_format($data['opening_balance'], 2); ?></td>
        </tr>
        <tr>
            <th>Total Sales</th>
            <td>Rs. <?php echo number_format($data['total_sales'], 2); ?></td>
        </tr>
        <tr>
            <th>Pay Ins</th>
            <td>Rs. <?php echo number_format($data['pay_ins'], 2); ?></td>
        </tr>
        <tr>
            <th>Pay Outs</th>
            <td>Rs. <?php echo number_format($data['pay_outs'], 2); ?></td>
        </tr>
        <tr>
            <th>Expected Amount</th>
            <td>Rs. <?php echo number_format($data['expected_amount'], 2); ?></td>
        </tr>
        <tr>
            <th>Actual Amount</th>
            <td>Rs. <?php echo number_format($data['actual_amount'], 2); ?></td>
        </tr>

        <tr>
            <th>Status</th>
            <td><?php echo ucfirst($data['status']); ?></td>
        </tr>
        <tr>
            <th>Created At</th>
            <td><?php echo $data['created_at']; ?></td>
        </tr>
        <tr>
            <th>Updated At</th>
            <td><?php echo $data['updated_at']; ?></td>
        </tr>
        <tr>
            <th>Variance</th>
            <td>Rs. <?php echo number_format($data['variance'], 2); ?></td>
        </tr>
    </table>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Print This Report</button>
    </div>

</body>

</html>