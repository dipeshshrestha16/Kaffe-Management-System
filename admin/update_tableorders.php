<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

if ($table_id > 0) {
    $orderQuery = "SELECT * FROM rpos_tableorders WHERE table_id = ? AND order_status != 'paid'";
    $orderStmt = $mysqli->prepare($orderQuery);
    $orderStmt->bind_param('i', $table_id);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();
} else {
    header("Location: table_order.php");
    exit();
}

if (isset($_POST['reduce_qty'])) {
    $order_id = intval($_POST['order_id']);
    $new_qty = intval($_POST['prod_qty']);

    // Fetch current order details
    $fetch_order = $mysqli->prepare("SELECT prod_id, prod_qty FROM rpos_tableorders WHERE order_id = ?");
    $fetch_order->bind_param('i', $order_id);
    $fetch_order->execute();
    $order_res = $fetch_order->get_result();

    if ($order_res->num_rows > 0) {
        $order = $order_res->fetch_object();
        $prod_id = $order->prod_id;
        $old_qty = $order->prod_qty;

        if ($new_qty > 0) {
            $diff_qty = $new_qty - $old_qty;

            if ($diff_qty > 0) {
                // Check stock availability before increasing
                $check_stock = $mysqli->prepare("SELECT stock_qty FROM rpos_inventory WHERE prod_id = ?");
                $check_stock->bind_param('i', $prod_id);
                $check_stock->execute();
                $stock_res = $check_stock->get_result();
                $stock_row = $stock_res->fetch_object();

                if ($stock_row->stock_qty < $diff_qty) {
                    echo "<script>alert('Not enough stock to increase quantity.'); window.location.href='update_tableorders.php?table_id=$table_id';</script>";
                    exit();
                }

                // Deduct extra quantity from stock
                $update_stock = $mysqli->prepare("UPDATE rpos_inventory SET stock_qty = stock_qty - ? WHERE prod_id = ?");
                $update_stock->bind_param('ii', $diff_qty, $prod_id);
                $update_stock->execute();
            } elseif ($diff_qty < 0) {
                // Restore removed quantity to inventory
                $restore_qty = abs($diff_qty);
                $restore_stock = $mysqli->prepare("UPDATE rpos_inventory SET stock_qty = stock_qty + ? WHERE prod_id = ?");
                $restore_stock->bind_param('ii', $restore_qty, $prod_id);
                $restore_stock->execute();
            }

            // Update order quantity
            $update_order = $mysqli->prepare("UPDATE rpos_tableorders SET prod_qty = ? WHERE order_id = ?");
            $update_order->bind_param('ii', $new_qty, $order_id);
            $update_order->execute();
        } else {
            // Delete order and restore quantity
            $restore_stock = $mysqli->prepare("UPDATE rpos_inventory SET stock_qty = stock_qty + ? WHERE prod_id = ?");
            $restore_stock->bind_param('ii', $old_qty, $prod_id);
            $restore_stock->execute();

            $delete_order = $mysqli->prepare("DELETE FROM rpos_tableorders WHERE order_id = ?");
            $delete_order->bind_param('i', $order_id);
            $delete_order->execute();
        }
    }

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
                                            echo "<tr>
                                                <td>{$item->prod_name}</td>
                                                <td>{$item->prod_qty}</td>
                                                <td>\${$item->prod_price}</td>
                                                <td>
                                                    <form method='POST' action='update_tableorders.php?table_id={$table_id}' class='update-form'>
                                                        <input type='hidden' name='order_id' value='{$item->order_id}'>
                                                        <div class='input-group' style='max-width: 150px;'>
                                                            <input type='number' name='prod_qty' value='{$item->prod_qty}' class='form-control' min='0' required>
                                                        </div>
                                                        <div class='d-flex mt-2'>
                                                            <button type='submit' name='reduce_qty' class='btn btn-warning btn-sm mr-2'>Update Quantity</button>
                                                            <button type='button' class='btn btn-danger btn-sm cancel-btn' data-order-id='{$item->order_id}'>Cancel</button>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.cancel-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const orderId = this.getAttribute('data-order-id');
                    Swal.fire({
                        title: 'Cancel this order?',
                        text: "Are you sure you want to remove this order?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, cancel it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `cancel_order.php?order_id=${orderId}&table_id=<?php echo $table_id; ?>`;
                        }
                    });
                });
            });

            document.querySelectorAll("form.update-form").forEach(form => {
                form.addEventListener("submit", function (e) {
                    const submitBtn = e.submitter;
                    if (submitBtn && submitBtn.name === "reduce_qty") {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Update Quantity?',
                            text: "Do you want to update this quantity?",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, update it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'reduce_qty';
                                hiddenInput.value = '1';
                                form.appendChild(hiddenInput);
                                form.submit();
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>