<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

if (!isset($_SESSION['group_id_' . $table_id])) {
    $_SESSION['group_id_' . $table_id] = time();
}
$group_id = $_SESSION['group_id_' . $table_id];

// Error messages using session for SweetAlert2
$error_message = null;

// Handle add to order
if (isset($_POST['add_to_order'])) {
    $table_id = intval($_POST['table_id']);
    $prod_id = intval($_POST['prod_id']);
    $prod_name = $_POST['prod_name'];
    $prod_price = floatval($_POST['prod_price']);
    $prod_qty = max(1, intval($_POST['prod_qty']));

    $stockCheck = $mysqli->prepare("SELECT stock_qty FROM rpos_inventory WHERE prod_id = ?");
    $stockCheck->bind_param('i', $prod_id);
    $stockCheck->execute();
    $stockRes = $stockCheck->get_result();
    $stockRow = $stockRes->fetch_object();

    // Only block order if stock info exists and quantity is insufficient
    if ($stockRow && $stockRow->stock_qty < $prod_qty) {
        $_SESSION['stock_error'] = $stockRow->stock_qty;
        header("Location: table_order.php?table_id=" . $table_id);
        exit();
    }

    if (!isset($_SESSION['customer_name_' . $table_id])) {
        $customer_name = $_POST['customer_name'];
        $_SESSION['customer_name_' . $table_id] = $customer_name;
    } else {
        $customer_name = $_SESSION['customer_name_' . $table_id];
    }

    $check = $mysqli->prepare("SELECT * FROM rpos_tableorders WHERE table_id = ? AND prod_id = ? AND order_status != 'paid' AND group_id = ?");
    $check->bind_param('iii', $table_id, $prod_id, $group_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $existing = $res->fetch_object();
        $new_qty = $existing->prod_qty + $prod_qty;
        $update = $mysqli->prepare("UPDATE rpos_tableorders SET prod_qty = ? WHERE table_id = ? AND prod_id = ? AND order_status != 'paid' AND group_id = ?");
        $update->bind_param('iiii', $new_qty, $table_id, $prod_id, $group_id);
        $update->execute();
    } else {
        $stmt = $mysqli->prepare("INSERT INTO rpos_tableorders (table_id, prod_id, prod_name, prod_price, prod_qty, customer_name, group_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iisdisi', $table_id, $prod_id, $prod_name, $prod_price, $prod_qty, $customer_name, $group_id);
        $stmt->execute();
    }

    // Only update inventory if product exists in inventory
    if ($stockRow) {
        $updateStock = $mysqli->prepare("UPDATE rpos_inventory SET stock_qty = stock_qty - ? WHERE prod_id = ? AND stock_qty >= ?");
        $updateStock->bind_param('iii', $prod_qty, $prod_id, $prod_qty);
        $updateStock->execute();
    }

    // Update table status if needed
    $checkTableStatus = $mysqli->prepare("SELECT status FROM rpos_tables WHERE table_id = ?");
    $checkTableStatus->bind_param('i', $table_id);
    $checkTableStatus->execute();
    $statusResult = $checkTableStatus->get_result();
    if ($statusResult->num_rows > 0) {
        $row = $statusResult->fetch_object();
        if (strtolower($row->status) == 'available' || strtolower($row->status) == 'reserved') {
            $updateStatus = $mysqli->prepare("UPDATE rpos_tables SET status = 'occupied' WHERE table_id = ?");
            $updateStatus->bind_param('i', $table_id);
            $updateStatus->execute();
        }
    }
}

// Handle payment
if (isset($_POST['pay_all_orders'])) {
    $payStmt = $mysqli->prepare("UPDATE rpos_tableorders SET order_status = 'paid' WHERE table_id = ? AND group_id = ?");
    $payStmt->bind_param('ii', $table_id, $group_id);
    $payStmt->execute();

    $freeTable = $mysqli->prepare("UPDATE rpos_tables SET status = 'available' WHERE table_id = ?");
    $freeTable->bind_param('i', $table_id);
    $freeTable->execute();

    unset($_SESSION['customer_name_' . $table_id]);
    unset($_SESSION['group_id_' . $table_id]);

    $_SESSION['payment_success'] = true;
    header("Location: table_order.php?table_id=" . $table_id);
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
                    <h1 class="text-white">Table <?php echo $table_id; ?> - Order</h1>
                </div>
            </div>
        </div>

        <div class="container-fluid mt--8">
            <div class="row">
                <!-- Add Product Form -->
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header border-0">Add Product</div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="table_id" value="<?php echo $table_id; ?>">
                                <?php if (!isset($_SESSION['customer_name_' . $table_id])): ?>
                                    <div class="form-group">
                                        <label>Customer Name</label>
                                        <input type="text" class="form-control" name="customer_name" required>
                                    </div>
                                <?php endif; ?>

                                <div class="form-group">
                                    <label for="prod_id">Select Product</label>
                                    <select class="form-control" name="prod_id" onchange="fillProductDetails(this)">
                                        <option value="">Select</option>
                                        <?php
                                        $ret = "SELECT p.prod_id, p.prod_name, p.prod_price, i.stock_qty 
                                            FROM rpos_products p 
                                            LEFT JOIN rpos_inventory i ON p.prod_id = i.prod_id";
                                        $stmt = $mysqli->prepare($ret);
                                        $stmt->execute();
                                        $res = $stmt->get_result();
                                        while ($prod = $res->fetch_object()) {
                                            $label = isset($prod->stock_qty)
                                                ? ($prod->stock_qty <= 0 ? "(Out of Stock)" : "- Rs. $prod->prod_price")
                                                : "- Rs. $prod->prod_price";
                                            echo "<option value='$prod->prod_id' data-name='$prod->prod_name' data-price='$prod->prod_price'>$prod->prod_name $label</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <input type="hidden" name="prod_name" id="prod_name">
                                <input type="hidden" name="prod_price" id="prod_price">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" class="form-control" name="prod_qty" min="1" value="1"
                                        required>
                                </div>
                                <button type="submit" name="add_to_order" class="btn btn-success">Add to Order</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Current Orders -->
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header border-0">Current Orders</div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $kotQuery = "SELECT * FROM rpos_tableorders WHERE table_id = ? AND order_status != 'paid'";
                                    $kotStmt = $mysqli->prepare($kotQuery);
                                    $kotStmt->bind_param('i', $table_id);
                                    $kotStmt->execute();
                                    $kotResult = $kotStmt->get_result();

                                    $total = 0;
                                    while ($item = $kotResult->fetch_object()) {
                                        $subtotal = $item->prod_price * $item->prod_qty;
                                        $total += $subtotal;
                                        echo "<tr>
                                    <td>{$item->prod_name}</td>
                                    <td>{$item->prod_qty}</td>
                                    <td>Rs.{$item->prod_price}</td>
                                    <td><strong>Rs.{$subtotal}</strong></td>
                                  </tr>";
                                    }
                                    echo "<tr><td colspan='3'><strong>Total</strong></td><td><strong>Rs.{$total}</strong></td></tr>";
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="row mt-3">
                <div class="col-md-6 text-left">
                    <a href="update_tableorders.php?table_id=<?php echo $table_id; ?>" class="btn btn-info">Update
                        Orders</a>
                </div>
                <div class="col-md-6 text-right">
                    <form id="payForm" method="POST" class="d-inline">
                        <button type="button" id="payBtn" class="btn btn-success">Pay Orders</button>
                        <input type="hidden" name="pay_all_orders" value="1">
                    </form>
                    <a target="_blank"
                        href="print_receipt.php?table_id=<?php echo $table_id; ?>&group_id=<?php echo $group_id; ?>"
                        class="btn btn-sm btn-primary">
                        <i class="fas fa-print"></i> Print Receipt
                    </a>
                </div>
            </div>
            <?php require_once('includes/footer.php'); ?>
        </div>
    </div>

    <?php require_once('includes/scripts.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function fillProductDetails(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            document.getElementById('prod_name').value = selectedOption.getAttribute('data-name');
            document.getElementById('prod_price').value = selectedOption.getAttribute('data-price');
        }

        document.getElementById('payBtn').addEventListener('click', function () {
            Swal.fire({
                title: 'Confirm Payment',
                text: "Are you sure you want to complete this order?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Pay Now'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('payForm').submit();
                }
            });
        });
    </script>

    <?php if (isset($_SESSION['payment_success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Payment Successful',
                text: 'The order has been paid successfully!',
                confirmButtonColor: '#28a745',
                confirmButtonText: 'OK'
            });
        </script>
        <?php unset($_SESSION['payment_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['stock_error'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Stock Alert',
                text: 'Cannot add product. Only <?php echo $_SESSION['stock_error']; ?> units left in stock or item is out of stock.',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'OK'
            });
        </script>
        <?php unset($_SESSION['stock_error']); ?>
    <?php endif; ?>
</body>

</html>