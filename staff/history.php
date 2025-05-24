<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

require_once('includes/header.php');

// Pagination settings
$limit = 15; // Records per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total grouped paid orders
$countQuery = "SELECT COUNT(*) AS total FROM (
    SELECT table_id, customer_name
    FROM rpos_tableorders
    WHERE order_status = 'paid'
    GROUP BY table_id, customer_name
) AS grouped_orders";
$countStmt = $mysqli->prepare($countQuery);
$countStmt->execute();
$countResult = $countStmt->get_result()->fetch_assoc();
$totalRecords = $countResult['total'];
$totalPages = ceil($totalRecords / $limit);

// Main query with LIMIT & OFFSET
$query = "SELECT table_id, customer_name, group_id, SUM(prod_price * prod_qty) AS total_price, MAX(order_time) AS last_order_date
          FROM rpos_tableorders
          WHERE order_status = 'paid'
          GROUP BY table_id, customer_name, group_id
          ORDER BY last_order_date DESC
          LIMIT ? OFFSET ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$res = $stmt->get_result();
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
                    <h1 class="text-white">Paid Orders History</h1>
                </div>
            </div>
        </div>

        <div class="container-fluid mt--8">
            <div class="row">
                <div class="col">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <h3>Paid Orders History</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-success" scope="col">Table Number</th>
                                        <th scope="col">Customer</th>
                                        <th scope="col">Total Price</th>
                                        <th class="text-success" scope="col">Last Order Date</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($order = $res->fetch_object()) {
                                        $total_price = $order->total_price;
                                        $last_order_date = date('d/M/Y g:i A', strtotime($order->last_order_date));
                                        ?>
                                        <tr>
                                            <th class="text-success" scope="row">Table <?php echo $order->table_id; ?></th>
                                            <td><?php echo $order->customer_name; ?></td>
                                            <td>Rs. <?php echo number_format($total_price, 2); ?></td>
                                            <td><?php echo $last_order_date; ?></td>
                                            <td>
                                                <a target="_blank"
                                                    href="print_receipt.php?table_id=<?php echo $order->table_id; ?>&group_id=<?php echo $order->group_id; ?>">

                                                    <button class="btn btn-sm btn-primary">
                                                        <i class="fas fa-print"></i> Print Receipt
                                                    </button>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Links -->
                        <nav aria-label="Page navigation" class="mt-4 mb-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>

                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php require_once('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Argon Scripts -->
    <?php require_once('includes/scripts.php'); ?>
</body>

</html>