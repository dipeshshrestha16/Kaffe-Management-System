<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

// Handle add to order
if (isset($_POST['add_to_order'])) {
    $table_id = $_POST['table_id'];
    $prod_id = $_POST['prod_id'];
    $prod_name = $_POST['prod_name'];
    $prod_price = $_POST['prod_price'];
    $prod_qty = $_POST['prod_qty'];

    // If no customer name is entered, ask for it (first time only)
    if (!isset($_SESSION['customer_name_' . $table_id])) {
        $customer_name = $_POST['customer_name'];
        $_SESSION['customer_name_' . $table_id] = $customer_name;
    } else {
        $customer_name = $_SESSION['customer_name_' . $table_id];
    }

    // Check if product already exists in KOT
    // Check if product already exists in KOT
    $check = $mysqli->prepare("SELECT * FROM rpos_tableorders WHERE table_id = ? AND prod_id = ? AND order_status != 'paid'");
    $check->bind_param('ii', $table_id, $prod_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $existing = $res->fetch_object();
        $new_qty = $existing->prod_qty + $prod_qty;
        $update = $mysqli->prepare("UPDATE rpos_tableorders SET prod_qty = ? WHERE table_id = ? AND prod_id = ? AND order_status != 'paid'");
        $update->bind_param('iii', $new_qty, $table_id, $prod_id);
        $update->execute();
    } else {
        $stmt = $mysqli->prepare("INSERT INTO rpos_tableorders (table_id, prod_id, prod_name, prod_price, prod_qty, customer_name) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iisdis', $table_id, $prod_id, $prod_name, $prod_price, $prod_qty, $customer_name);
        $stmt->execute();
    }

    // Check table status and update it to 'occupied' if it's reserved or available
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

// Handle pay all orders
if (isset($_POST['pay_all_orders'])) {
    $payQuery = "UPDATE rpos_tableorders SET order_status = 'paid' WHERE table_id = ?";
    $payStmt = $mysqli->prepare($payQuery);
    $payStmt->bind_param('i', $table_id);
    $payStmt->execute();

    // Reset table status to 'available'
    $freeTable = $mysqli->prepare("UPDATE rpos_tables SET status = 'available' WHERE table_id = ?");
    $freeTable->bind_param('i', $table_id);
    $freeTable->execute();

    // Clear the customer name session after payment
    unset($_SESSION['customer_name_' . $table_id]);

    // Redirect to reload the page and reflect the changes
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
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header border-0">Add Product</div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="table_id" value="<?php echo $table_id; ?>">

                                <!-- Only show the customer name field if not set in session -->
                                <?php if (!isset($_SESSION['customer_name_' . $table_id])): ?>
                                    <div class="form-group">
                                        <label>Customer Name</label>
                                        <input type="text" class="form-control" name="customer_name" required>
                                    </div>
                                <?php endif; ?>

                                <div class="form-group">
                                    <label>Select Product</label>
                                    <select class="form-control" name="prod_id"
                                        onchange="fillProductDetails(this.value)">
                                        <option value="">Select</option>
                                        <?php
                                        $ret = "SELECT * FROM rpos_products";
                                        $stmt = $mysqli->prepare($ret);
                                        $stmt->execute();
                                        $res = $stmt->get_result();
                                        while ($prod = $res->fetch_object()) {
                                            echo "<option value='$prod->prod_id' data-name='$prod->prod_name' data-price='$prod->prod_price'>$prod->prod_name - Rs. $prod->prod_price</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <input type="hidden" name="prod_name" id="prod_name">
                                <input type="hidden" name="prod_price" id="prod_price">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" class="form-control" name="prod_qty" required>
                                </div>
                                <button type="submit" name="add_to_order" class="btn btn-success">Add to Order</button>
                            </form>
                        </div>
                    </div>
                </div>

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

            <!-- Buttons -->
            <div class="row">
                <div class="col-md-6" style="text-align: left;">
                    <a href="update_tableorders.php?table_id=<?php echo $table_id; ?>" class="btn btn-info">Update
                        Orders</a>
                </div>
                <div class="col-md-6" style="text-align: right;">
                    <form method="POST">
                        <button type="submit" name="pay_all_orders" class="btn btn-success">Pay Orders</button>
                    </form>
                    <a target="_blank" href="print_receipt.php?table_id=<?php echo $table_id; ?>">
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-print"></i>
                            Print Receipt
                        </button>
                    </a>
                </div>
            </div>

            <?php require_once('includes/footer.php'); ?>
        </div>
    </div>
    <?php require_once('includes/scripts.php'); ?>
    <script>
        function fillProductDetails(prod_id) {
            const select = document.querySelector("select[name='prod_id']");
            const selectedOption = select.options[select.selectedIndex];
            const name = selectedOption.getAttribute('data-name');
            const price = selectedOption.getAttribute('data-price');
            document.getElementById('prod_name').value = name;
            document.getElementById('prod_price').value = price;
        }
    </script>
</body>

</html>