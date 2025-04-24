<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;
$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

if ($table_id == 0 || $group_id == 0) {
    echo "<script>alert('Missing table or session info.'); window.close();</script>";
    exit();
}

$ret = "SELECT * FROM rpos_tableorders WHERE table_id = ? AND group_id = ?";
$stmt = $mysqli->prepare($ret);
$stmt->bind_param('ii', $table_id, $group_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<script>alert('No orders found for this session.'); window.close();</script>";
    exit();
}

$total = 0;
$customer_name = '';

if ($first_order = $res->fetch_object()) {
    $customer_name = $first_order->customer_name;
    $stmt->execute(); // Reset result
    $res = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Freak's Kaffe Receipt</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <style>
        body {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div id="Receipt" class="well col-md-6 col-md-offset-3">
                <div class="row">
                    <div class="col-md-6">
                        <address>
                            <strong>Freak's Kaffe</strong><br>
                            127-0-0-1<br>
                            Kathmandu, Nepal<br>
                            (+977)9847904448
                        </address>
                    </div>
                    <div class="col-md-6 text-right">
                        <p><em>Date: <?php echo date('d/M/Y g:i A'); ?></em></p>
                        <p><em class="text-success">Customer: <?php echo $customer_name ?: 'N/A'; ?></em></p>
                    </div>
                </div>

                <div class="text-center">
                    <h2>Bill Preview</h2>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th class="text-center">Unit Price</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $res->fetch_object()):
                            $subtotal = $order->prod_price * $order->prod_qty;
                            $total += $subtotal;
                            ?>
                            <tr>
                                <td><em><?php echo $order->prod_name; ?></em></td>
                                <td class="text-center"><?php echo $order->prod_qty; ?></td>
                                <td class="text-center">Rs.<?php echo $order->prod_price; ?></td>
                                <td class="text-center">Rs.<?php echo number_format($subtotal, 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <tr>
                            <td colspan="2"></td>
                            <td class="text-right"><strong>Subtotal:</strong><br><strong>Tax (14%):</strong></td>
                            <td class="text-center">
                                <strong>Rs.<?php echo number_format($total, 2); ?></strong><br>
                                <strong>Rs.<?php echo number_format($total * 0.14, 2); ?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td class="text-right">
                                <h4><strong>Total:</strong></h4>
                            </td>
                            <td class="text-center text-danger">
                                <h4><strong>Rs.<?php echo number_format($total * 1.14, 2); ?></strong></h4>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="well col-md-6 col-md-offset-3">
                <button id="print" onclick="printContent('Receipt');" class="btn btn-success btn-lg btn-block">
                    Print <span class="fas fa-print"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function printContent(el) {
            var restore = document.body.innerHTML;
            var printContent = document.getElementById(el).innerHTML;
            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = restore;
        }
    </script>
</body>

</html>