<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Handle new order submission
if (isset($_POST['add_order'])) {
    $table_id = intval($_POST['table_id']);
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Insert new order
    $stmt = $mysqli->prepare("INSERT INTO rpos_table_orders (table_id, product_id, quantity) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('iii', $table_id, $product_id, $quantity);
        if ($stmt->execute()) {
            // Update table status to occupied
            $update_stmt = $mysqli->prepare("UPDATE rpos_table SET status = 'occupied' WHERE table_id = ?");
            $update_stmt->bind_param('i', $table_id);
            $update_stmt->execute();
            $update_stmt->close();

            $success = "Order added successfully!";
        } else {
            $err = "Failed to add order.";
        }
        $stmt->close();
    } else {
        $err = "Prepare statement failed: " . $mysqli->error;
    }
}

// Fetch tables
$tables = $mysqli->query("SELECT * FROM rpos_table");
// Fetch products
$products = $mysqli->query("SELECT * FROM rpos_products WHERE status = 'enabled'");

require_once('includes/header.php');
?>

<body>
    <?php require_once('includes/sidebar.php'); ?>
    <div class="main-content">
        <?php require_once('includes/navbar.php'); ?>
        <div class="container-fluid mt--8">
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header">Add Order</div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label>Select Table</label>
                                    <select name="table_id" class="form-control" required>
                                        <?php while ($table = $tables->fetch_object()) { ?>
                                            <option value="<?php echo $table->table_id; ?>">
                                                <?php echo $table->table_name; ?> (<?php echo $table->status; ?>)
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Select Product</label>
                                    <select name="product_id" class="form-control" required>
                                        <?php while ($product = $products->fetch_object()) { ?>
                                            <option value="<?php echo $product->prod_id; ?>">
                                                <?php echo $product->prod_name; ?> - Rs.<?php echo $product->prod_price; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" name="quantity" class="form-control" required min="1">
                                </div>
                                <button type="submit" name="add_order" class="btn btn-success">Add Order</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php require_once('includes/footer.php'); ?>
        </div>
    </div>
    <?php require_once('includes/scripts.php'); ?>
</body>

</html>