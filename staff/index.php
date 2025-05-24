<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Get today's date in YYYY-MM-DD format
$today = date('Y-m-d');

// 1. Total Orders Today (total number of orders made today)
$totalOrders = 0;
$stmt = $mysqli->prepare("SELECT SUM(prod_qty) FROM rpos_tableorders WHERE DATE(order_time) = ?");
$stmt->bind_param('s', $today);
$stmt->execute();
$stmt->bind_result($totalOrders);
$stmt->fetch();
$stmt->close();

// 2. Available Tables (number of tables with status 'available')
$availableTables = 0;
$query = "SELECT COUNT(*) FROM rpos_tables WHERE status = 'available'";
$stmt = $mysqli->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error . "<br>Query: " . $query);
}
$stmt->execute();
$stmt->bind_result($availableTables);
$stmt->fetch();
$stmt->close();

// 3. Bills Pending (total number of unpaid bills, 'occupied' tables)
$billsPending = 0;
$stmt = $mysqli->prepare("SELECT COUNT(DISTINCT table_id) FROM rpos_tableorders WHERE order_status = 'unpaid'");
$stmt->execute();
$stmt->bind_result($billsPending);
$stmt->fetch();
$stmt->close();

// 4. Total Sales Today (sum of paid orders)
$totalSales = 0;
$stmt = $mysqli->prepare("SELECT SUM(prod_price * prod_qty) FROM rpos_tableorders WHERE order_status = 'paid' AND DATE(order_time) = ?");
$stmt->bind_param('s', $today);
$stmt->execute();
$stmt->bind_result($totalSales);
$stmt->fetch();
$stmt->close();

// Low Stock Items Query
$lowStockItems = [];
$lowStockQuery = "SELECT p.prod_name, i.stock_qty, i.min_qty FROM rpos_inventory i JOIN rpos_products p ON i.prod_id = p.prod_id WHERE i.stock_qty <= i.min_qty ORDER BY p.prod_name ASC";
$stmt = $mysqli->prepare($lowStockQuery);
$stmt->execute();
$stmt->bind_result($prod_name, $stock_qty, $min_qty);
while ($stmt->fetch()) {
    $lowStockItems[] = ['prod_name' => $prod_name, 'stock_qty' => $stock_qty, 'min_qty' => $min_qty];
}
$stmt->close();

// Pagination for Recent Orders
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;
$ordersPerPage = 3;
$offset = ($page - 1) * $ordersPerPage;

// Get total count of distinct recent orders
$countQuery = "
    SELECT COUNT(*) FROM (
        SELECT table_id, prod_name
        FROM rpos_tableorders
        WHERE DATE(order_time) = ?
        GROUP BY table_id, prod_name
    ) AS grouped_orders
";
$stmt = $mysqli->prepare($countQuery);
$stmt->bind_param('s', $today);
$stmt->execute();
$stmt->bind_result($totalOrdersCount);
$stmt->fetch();
$stmt->close();

// Fetch paginated recent orders
$recentOrdersQuery = "
    SELECT table_id, prod_name, SUM(prod_qty) AS quantity
    FROM rpos_tableorders
    WHERE DATE(order_time) = ?
    GROUP BY table_id, prod_name
    ORDER BY MAX(order_time) DESC
    LIMIT ? OFFSET ?
";
$stmt = $mysqli->prepare($recentOrdersQuery);
$stmt->bind_param('sii', $today, $ordersPerPage, $offset);
$stmt->execute();
$stmt->bind_result($table_id, $prod_name, $quantity);

$recentOrders = [];
while ($stmt->fetch()) {
    $recentOrders[] = ['table_id' => $table_id, 'prod_name' => $prod_name, 'quantity' => $quantity];
}
$stmt->close();

$totalPages = ceil($totalOrdersCount / $ordersPerPage);
?>

<!DOCTYPE html>
<html>

<?php include('includes/header.php'); ?>

<body>
    <?php include('includes/navbar.php'); ?>
    <div class="main-content" id="panel">
        <?php include('includes/sidebar.php'); ?>

        <div class="container-fluid mt-4">
            <div class="header-body">
                <div class="row">
                    <!-- Total Orders Today -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-stats shadow-lg hover-shadow-lg"
                            style="background: linear-gradient(135deg, rgba(75, 192, 192, 0.7), rgba(75, 192, 192, 1)); border-radius: 15px; transition: transform 0.3s;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Total Orders Today</h5>
                                        <span class="h2 font-weight-bold mb-0"><?php echo $totalOrders; ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-white text-primary rounded-circle shadow-lg">
                                            <i class="ni ni-cart" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Available Tables -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-stats shadow-lg hover-shadow-lg"
                            style="background: linear-gradient(135deg, rgba(0, 123, 255, 0.7), rgba(0, 123, 255, 1)); border-radius: 15px; transition: transform 0.3s;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Available Tables</h5>
                                        <span class="h2 font-weight-bold mb-0"><?php echo $availableTables; ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-white text-info rounded-circle shadow-lg">
                                            <i class="ni ni-chart-bar-32" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bills Pending -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-stats shadow-lg hover-shadow-lg"
                            style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.7), rgba(255, 193, 7, 1)); border-radius: 15px; transition: transform 0.3s;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Bills Pending</h5>
                                        <span class="h2 font-weight-bold mb-0"><?php echo $billsPending; ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-white text-warning rounded-circle shadow-lg">
                                            <i class="ni ni-collection" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Sales -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-stats shadow-lg hover-shadow-lg"
                            style="background: linear-gradient(135deg, rgba(131, 226, 53, 0.7), rgba(131, 226, 53, 0.7)); border-radius: 15px; transition: transform 0.3s;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Total Sales Today</h5>
                                        <span
                                            class="h2 font-weight-bold mb-0">Rs.<?php echo $totalSales ? number_format($totalSales, 2) : '0.00'; ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-white text-danger rounded-circle shadow-lg">
                                            <i class="ni ni-money-coins" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid mt--8 pt-1">
                        <div class="row">
                            <div class="col">
                                <div class="card shadow">
                                    <div class="card-header border-0">
                                        <h3>Select A Table To Place An Order</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <?php
                                            $ret = "SELECT * FROM rpos_tables ORDER BY table_number ASC";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute();
                                            $res = $stmt->get_result();
                                            while ($table = $res->fetch_object()) {
                                                // Determine button color class based on status
                                                $btn_class = 'btn-success'; // default for available
                                                if ($table->status === 'reserved') {
                                                    $btn_class = 'btn-danger';
                                                } elseif ($table->status === 'occupied') {
                                                    $btn_class = 'btn-warning text-dark';
                                                }
                                                ?>
                                                <div class="col-md-3 mb-3">
                                                    <a href="table_order.php?table_id=<?php echo $table->table_id; ?>"
                                                        class="btn <?php echo $btn_class; ?> btn-block p-4 shadow-sm text-center">
                                                        <h4>Table <?php echo $table->table_number; ?></h4>
                                                        <small>Status: <?php echo ucfirst($table->status); ?></small>
                                                    </a>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Recent Orders -->
                            <div class="col-xl-6 col-md-12 mt-1">
                                <div class="card card-stats shadow-lg hover-shadow-lg" style="border-radius: 15px;">
                                    <div class="card-body">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Recent Orders</h5>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Table</th>
                                                    <th>Order</th>
                                                    <th>Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (empty($recentOrders)) {
                                                    echo "<tr><td colspan='3'>No recent orders today.</td></tr>";
                                                } else {
                                                    foreach ($recentOrders as $order) {
                                                        echo "<tr>
                                                    <td>{$order['table_id']}</td>
                                                    <td>{$order['prod_name']}</td>
                                                    <td>{$order['quantity']}</td>
                                                </tr>";
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-center">
                                                        <!-- Pagination buttons -->
                                                        <form method="get" style="display:inline;">
                                                            <input type="hidden" name="page"
                                                                value="<?= max(1, $page - 1) ?>">
                                                            <button type="submit" class="btn btn-sm btn-primary"
                                                                <?= $page <= 1 ? 'disabled' : '' ?>>&laquo;
                                                                Previous</button>
                                                        </form>

                                                        <span style="margin: 0 10px;">Page <?= $page ?> of
                                                            <?= $totalPages ?></span>

                                                        <form method="get" style="display:inline;">
                                                            <input type="hidden" name="page"
                                                                value="<?= min($totalPages, $page + 1) ?>">
                                                            <button type="submit" class="btn btn-sm btn-primary"
                                                                <?= $page >= $totalPages ? 'disabled' : '' ?>>Next
                                                                &raquo;</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Low Stock Items -->
                            <div class="col-xl-6 col-md-12 mt-1">
                                <div class="card card-stats shadow-lg hover-shadow-lg"
                                    style="background: linear-gradient(135deg, rgb(133, 26, 36), rgb(133, 26, 36)); border-radius: 15px;">
                                    <div class="card-body">
                                        <h5 class="card-title text-uppercase text-white mb-3">Low Stock Items</h5>
                                        <table class="table table-bordered" style="background-color: white;">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Stock</th>
                                                    <th>Min Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (empty($lowStockItems)) {
                                                    echo "<tr><td colspan='3'>No low stock items.</td></tr>";
                                                } else {
                                                    foreach ($lowStockItems as $item) {
                                                        echo "<tr>
                        <td>{$item['prod_name']}</td>
                        <td>{$item['stock_qty']}</td>
                        <td>{$item['min_qty']}</td>
                      </tr>";
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <?php include('includes/footer.php'); ?>
                        <?php include('includes/scripts.php'); ?>
</body>

</html>