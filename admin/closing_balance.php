<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$user_id = $_SESSION['admin_id'];
$shift_date = date('Y-m-d');

$success = $err = '';

// Fetch open balance record
$stmt = $mysqli->prepare("SELECT id, opening_balance FROM rpos_balances WHERE balance_date = ? AND status = 'open' LIMIT 1");

if (!$stmt) {
    die("Prepare failed: " . $mysqli->error);
}
$stmt->bind_param('s', $shift_date);
$stmt->execute();
$stmt->bind_result($balance_id, $opening_balance);
$stmt->fetch();
$stmt->close();

if (!$balance_id) {
    die("No open shift found for today. Please set opening balance first.");
}

// Fetch total cash sales (paid orders)
$stmt = $mysqli->prepare(query: "SELECT SUM(prod_price * prod_qty) FROM rpos_tableorders WHERE order_status = 'paid' AND DATE(order_time) = ?");
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error);
}
$stmt->bind_param('s', $shift_date);
$stmt->execute();
$stmt->bind_result($total_sales);
$stmt->fetch();
$stmt->close();

if (!$total_sales) {
    $total_sales = 0;
}

if (isset($_POST['close_shift'])) {
    $pay_in = floatval($_POST['pay_in']);
    $pay_out = floatval($_POST['pay_out']);
    $actual_cash = floatval($_POST['actual_cash']); // Actual cash counted physically

    $expected_amount = $opening_balance + $total_sales + $pay_in - $pay_out;
    $variance = $actual_cash - $expected_amount;
    $closing_balance = $actual_cash; // Closing balance is the actual cash

    // Update the record
    $stmt = $mysqli->prepare("UPDATE rpos_balances SET total_sales = ?, pay_ins = ?, pay_outs = ?, expected_amount = ?, actual_amount = ?, variance = ?, closing_balance = ?, status = 'closed', updated_at = NOW() WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    $stmt->bind_param('dddddddi', $total_sales, $pay_in, $pay_out, $expected_amount, $actual_cash, $variance, $closing_balance, $balance_id);

    if ($stmt->execute()) {
        $success = "Shift closed successfully!";
    } else {
        $err = "Failed to close shift.";
    }
    $stmt->close();
}
?>

<?php include('includes/header.php'); ?>

<body class="bg-dark">
    <div class="main-content">
        <div class="header bg-gradient-primary py-5">
            <div class="container text-center">
                <h1 class="text-white">Close Shift</h1>
            </div>
        </div>
        <div class="container mt--6 pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="card bg-secondary shadow border-0">
                        <div class="card-body px-lg-5 py-lg-5">
                            <?php if ($err)
                                echo "<div class='alert alert-danger'>$err</div>"; ?>
                            <?php if ($success)
                                echo "<div class='alert alert-success'>$success</div>"; ?>

                            <?php if (!$success): ?>
                                <form method="post">
                                    <p><strong>Opening Balance:</strong> Rs.
                                        <?php echo number_format($opening_balance, 2); ?>
                                    </p>
                                    <p><strong>Total Sales:</strong> Rs. <?php echo number_format($total_sales, 2); ?></p>

                                    <div class="form-group">
                                        <label>Pay Ins (other income) (Rs.)</label>
                                        <input type="number" name="pay_in" step="0.01" value="0" required
                                            class="form-control">
                                    </div>

                                    <div class="form-group">
                                        <label>Pay Outs (expenses) (Rs.)</label>
                                        <input type="number" name="pay_out" step="0.01" value="0" required
                                            class="form-control">
                                    </div>

                                    <div class="form-group">
                                        <label>Actual Cash Counted (Rs.)</label>
                                        <input type="number" name="actual_cash" step="0.01" required class="form-control">
                                    </div>

                                    <button type="submit" name="close_shift" class="btn btn-primary btn-block">Close
                                        Shift</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
    <?php include('includes/scripts.php'); ?>
</body>