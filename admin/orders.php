<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Get table ID from URL
$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

// Fetch table details
$query = "SELECT * FROM rpos_table WHERE table_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $table_id);
$stmt->execute();
$table = $stmt->get_result()->fetch_object();
$stmt->close();

// Check if the table exists
if (!$table) {
    die("Table not found.");
}

// Fetch current orders for this table
$query = "SELECT * FROM rpos_ordrs WHERE table_id = ? AND o_status = 'pending'";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $table_id);
$stmt->execute();
$current_order = $stmt->get_result()->fetch_object();
$stmt->close();

// If there's no current order, create a new one
if (!$current_order) {
    // Insert new order for the table
    $query = "INSERT INTO rpos_ordrs (table_id, o_status, order_date) VALUES (?, 'pending', NOW())";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $table_id);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Fetch the newly created order
    $current_order = (object) ['order_id' => $order_id, 'table_id' => $table_id, 'o_status' => 'pending'];
}

// Handle adding items to the order
if (isset($_POST['add_item'])) {
    $prod_id = intval($_POST['prod_id']);
    $quantity = intval($_POST['quantity']);

    // Fetch product details
    $query = "SELECT * FROM rpos_products WHERE prod_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $prod_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_object();
    $stmt->close();

    if ($product) {
        // Calculate price and subtotal
        $price = $product->prod_price;
        $subtotal = $price * $quantity;

        // Insert order item into rpos_ordr_items table
        $query = "INSERT INTO rpos_ordr_items (order_id, prod_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('iiidd', $current_order->order_id, $prod_id, $quantity, $price, $subtotal);

        if ($stmt->execute()) {
            echo "Item added successfully!<br>";

            // Redirect to the same page to prevent form resubmission and fetch updated order items
            header("Location: orders.php?table_id=" . $table_id);
            exit;
        } else {
            echo "Error adding item: " . $stmt->error;  // Error debugging
        }
    } else {
        echo "Product not found.";
    }
}

// Fetch order items for the current order
$query = "SELECT oi.*, p.prod_name FROM rpos_ordr_items oi JOIN rpos_products p ON oi.prod_id = p.prod_id WHERE oi.order_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $current_order->order_id);
$stmt->execute();
$order_items = $stmt->get_result();
$stmt->close();

require_once('includes/header.php');
?>

<body>
    <?php require_once('includes/sidebar.php'); ?>
    <div class="main-content">
        <?php require_once('includes/navbar.php'); ?>

        <div class="container-fluid mt-4">
            <div class="card shadow">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Manage Orders for Table: <?php echo $table->table_name; ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="orders.php?table_id=<?php echo $table_id; ?>">
                        <div class="form-group">
                            <label for="prod_id">Select Product</label>
                            <select name="prod_id" id="prod_id" class="form-control" required>
                                <option value="">Select Product</option>
                                <?php
                                $query = "SELECT * FROM rpos_products WHERE status = 'enabled'";
                                $stmt = $mysqli->prepare($query);
                                $stmt->execute();
                                $products = $stmt->get_result();
                                $stmt->close();
                                while ($prod = $products->fetch_object()) {
                                    echo "<option value=\"$prod->prod_id\">$prod->prod_name - Rs.$prod->prod_price</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                        </div>
                        <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
                    </form>
                </div>

                <!-- Display current order items -->
                <div class="card-body">
                    <h4>Current Order Items</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($order_items->num_rows > 0): ?>
                                <?php while ($item = $order_items->fetch_object()): ?>
                                    <tr>
                                        <td><?php echo $item->prod_name; ?></td>
                                        <td><?php echo $item->quantity; ?></td>
                                        <td>Rs.<?php echo $item->price; ?></td>
                                        <td>Rs.<?php echo $item->subtotal; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No items in the current order.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Order actions -->
                <div class="card-footer d-flex justify-content-between">
                    <a href="print_bill.php?order_id=<?php echo $current_order->order_id; ?>"
                        class="btn btn-warning">Print Bill</a>
                    <a href="close_order.php?order_id=<?php echo $current_order->order_id; ?>"
                        class="btn btn-success">Mark as Paid</a>
                </div>
            </div>
        </div>
    </div>

    <?php require_once('includes/footer.php'); ?>
    <?php require_once('includes/scripts.php'); ?>
</body>

</html>