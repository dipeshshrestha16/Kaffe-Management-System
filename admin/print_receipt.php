<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Get the table ID from the URL
$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

// Retrieve orders for this table
$ret = "SELECT * FROM rpos_tableorders WHERE table_id = ? AND order_status = 'paid'";
$stmt = $mysqli->prepare($ret);
$stmt->bind_param('i', $table_id);
$stmt->execute();
$res = $stmt->get_result();

// Initialize total
$total = 0;
$customer_name = ''; // Default empty

// Get the customer name from the first order for this table
if ($order = $res->fetch_object()) {
    $customer_name = $order->customer_name;
    // Rewind the result set to loop through all the orders again
    $stmt->execute();
    $res = $stmt->get_result();
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Restaurant POS System">
    <meta name="author" content="MartDevelopers Inc">
    <title>Kaffe POS</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" id="bootstrap-css">
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
            <div id="Receipt" class="well col-xs-10 col-sm-10 col-md-6 col-xs-offset-1 col-sm-offset-1 col-md-offset-3">
                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <address>
                            <strong>Freak's Kaffe</strong>
                            <br>
                            127-0-0-1
                            <br>
                            Kathmandu, Nepal
                            <br>
                            (+977)9847904448
                        </address>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6 text-right">
                        <p>
                            <em>Date: <?php echo date('d/M/Y g:i'); ?></em>
                        </p>
                        <p>
                            <em class="text-success">Customer:
                                <?php echo $customer_name ? $customer_name : 'N/A'; ?></em>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="text-center">
                        <h2>Receipt</h2>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Loop through all orders for the table
                            while ($order = $res->fetch_object()) {
                                $subtotal = $order->prod_price * $order->prod_qty;
                                $total += $subtotal;
                                ?>
                                <tr>
                                    <td class="col-md-9"><em><?php echo $order->prod_name; ?></em></td>
                                    <td class="col-md-1" style="text-align: center"><?php echo $order->prod_qty; ?></td>
                                    <td class="col-md-1 text-center">Rs.<?php echo $order->prod_price; ?></td>
                                    <td class="col-md-1 text-center">Rs.<?php echo number_format($subtotal, 2); ?></td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td></td>
                                <td></td>
                                <td class="text-right">
                                    <p><strong>Subtotal: </strong></p>
                                    <p><strong>Tax (14%): </strong></p>
                                </td>
                                <td class="text-center">
                                    <p><strong>Rs.<?php echo number_format($total, 2); ?></strong></p>
                                    <p><strong>Rs.<?php echo number_format($total * 0.14, 2); ?></strong></p>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td class="text-right">
                                    <h4><strong>Total: </strong></h4>
                                </td>
                                <td class="text-center text-danger">
                                    <h4><strong>Rs.<?php echo number_format($total * 1.14, 2); ?></strong></h4>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="well col-xs-10 col-sm-10 col-md-6 col-xs-offset-1 col-sm-offset-1 col-md-offset-3">
                <button id="print" onclick="printContent('Receipt');"
                    class="btn btn-success btn-lg text-justify btn-block">
                    Print <span class="fas fa-print"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function printContent(el) {
            var restorepage = $('body').html();
            var printcontent = $('#' + el).clone();
            $('body').empty().html(printcontent);
            window.print();
            $('body').html(restorepage);
        }
    </script>
</body>

</html>