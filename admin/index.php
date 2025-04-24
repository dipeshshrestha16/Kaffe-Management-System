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
                        <div class="card card-stats">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Total Orders Today</h5>
                                        <span class="h2 font-weight-bold mb-0"><?php echo $totalOrders; ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                                            <i class="ni ni-cart"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Available Tables -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-stats">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Available Tables</h5>
                                        <span class="h2 font-weight-bold mb-0"><?php echo $availableTables; ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                                            <i class="ni ni-chart-bar-32"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bills Pending -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-stats">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Bills Pending</h5>
                                        <span class="h2 font-weight-bold mb-0"><?php echo $billsPending; ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                                            <i class="ni ni-collection"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Sales -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-stats">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Total Sales Today</h5>
                                        <span
                                            class="h2 font-weight-bold mb-0">Rs.<?php echo $totalSales ? number_format($totalSales, 2) : '0.00'; ?></span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                            <i class="ni ni-money-coins"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-12 mt-1">
                        <div class="card card-stats">
                            <div class="card-body">
                                <h5 class="card-title text-uppercase text-muted mb-0">Recent Orders</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Table</th>
                                            <th>Order</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $recentOrdersQuery = "SELECT table_id, order_id, SUM(prod_price * prod_qty) AS total FROM rpos_tableorders WHERE DATE(order_time) = ? GROUP BY table_id ORDER BY order_time DESC LIMIT 5";
                                        $stmt = $mysqli->prepare($recentOrdersQuery);
                                        $stmt->bind_param('s', $today);
                                        $stmt->execute();
                                        $stmt->bind_result($table_id, $order_id, $total);
                                        while ($stmt->fetch()) {
                                            echo "<tr><td>$table_id</td><td>$order_id</td><td>Rs. " . number_format($total, 2) . "</td></tr>";
                                        }
                                        $stmt->close();
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <canvas id="salesTrendChart" width="200" height="50"></canvas>

                <script>
                    var ctx = document.getElementById('salesTrendChart').getContext('2d');
                    var salesTrendChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                            datasets: [{
                                label: 'Sales in Rs.',
                                data: [1200, 1800, 1500, 1700, 2200, 2100, 2500],  // Replace with dynamic data
                                borderColor: 'rgb(75, 192, 192)',
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (tooltipItem) {
                                            return 'Rs. ' + tooltipItem.raw;
                                        }
                                    }
                                }
                            }
                        }
                    });
                </script>


            </div>
        </div>

        <?php include('includes/footer.php'); ?>
    </div>

    <?php include('includes/scripts.php'); ?>

</body>

</html>