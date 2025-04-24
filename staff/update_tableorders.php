<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

// Check if table_id is valid and ensure orders are unpaid
if ($table_id > 0) {
    $orderQuery = "SELECT * FROM rpos_tableorders WHERE table_id = ? AND order_status != 'paid'";
    $orderStmt = $mysqli->prepare($orderQuery);
    $orderStmt->bind_param('i', $table_id);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();
} else {
    // Handle invalid table_id or error if needed
    header("Location: table_order.php");
    exit();
}

// Handle quantity reduction
if (isset($_POST['reduce_qty'])) {
    $order_id = $_POST['order_id'];
    $new_qty = $_POST['prod_qty'];

    if ($new_qty > 0) {
        // Update the quantity in the database
        $update_query = "UPDATE rpos_tableorders SET prod_qty = ? WHERE order_id = ?";
        $update_stmt = $mysqli->prepare($update_query);
        $update_stmt->bind_param('ii', $new_qty, $order_id);
        $update_stmt->execute();
    } else {
        // If the quantity is reduced to 0, remove the order
        $delete_query = "DELETE FROM rpos_tableorders WHERE order_id = ?";
        $delete_stmt = $mysqli->prepare($delete_query);
        $delete_stmt->bind_param('i', $order_id);
        $delete_stmt->execute();
    }

    // Redirect to refresh the page
    header("Location: update_tableorders.php?table_id=" . $table_id);
    exit();
}

require_once('includes/header.php');
?>

<body>
    <?php require_once('includes/sidebar.php'); ?>
    <div class="main-content">
        <?php require_once('includes/navbar.php'); ?>

        <div class="header pb-8 pt-5 pt-md-8"
            style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;">
            <span class="mask bg-gradient-dark opacity-8"></span>
            <div class="container-fluid">
                <div class="header-body">
                    <h1 class="text-white">Update Orders for Table <?php echo $table_id; ?></h1>
                </div>
            </div>
        </div>

        <div class="container-fluid mt--8">
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header border-0">Current Unpaid Orders</div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($orderResult->num_rows > 0) {
                                        while ($item = $orderResult->fetch_object()) {
                                            $subtotal = $item->prod_price * $item->prod_qty;
                                            echo "<tr>
                                                <td>{$item->prod_name}</td>
                                                <td>{$item->prod_qty}</td>
                                                <td>\${$item->prod_price}</td>
                                                <td>
                                                    <form method='POST' action='update_tableorders.php?table_id={$table_id}'>
                                                        <input type='hidden' name='order_id' value='{$item->order_id}'>
                                                        <div class='input-group' style='max-width: 150px;'>
                                                            <input type='number' name='prod_qty' value='{$item->prod_qty}' class='form-control' min='1' required>
                                                        </div>
                                                        <div class='d-flex mt-2'>
                                                            <button type='submit' name='reduce_qty' class='btn btn-warning btn-sm mr-2'>Update Quantity</button>
                                                            <a href='cancel_order.php?order_id={$item->order_id}' class='btn btn-danger btn-sm'>Cancel</a>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4'>No orders found for this table.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <a href="table_order.php?table_id=<?php echo $table_id; ?>" class="btn btn-primary">Back to Table
                        Orders</a>
                </div>
            </div>

            <?php require_once('includes/footer.php'); ?>
        </div>
    </div>

    <?php require_once('includes/scripts.php'); ?>
</body>

</html>