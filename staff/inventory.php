<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

require_once('includes/header.php');
?>

<body>
    <?php require_once('includes/sidebar.php'); ?>
    <div class="main-content">
        <?php require_once('includes/navbar.php'); ?>
        <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;"
            class="header pb-8 pt-5 pt-md-8">
            <span class="mask bg-gradient-dark opacity-8"></span>
            <div class="container-fluid">
                <div class="header-body"></div>
            </div>
        </div>

        <div class="container-fluid mt--8">
            <div class="row">
                <div class="col">
                    <div class="card shadow">
                        <!-- <div class="card-header border-0 d-flex justify-content-between align-items-center">
                            <a href="add-inventory.php" class="btn btn-outline-success">
                                <i class="fas fa-box"></i> Add Inventory Item
                            </a>
                        </div> -->

                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Stock Quantity</th>
                                        <th>Minimum Quantity</th>
                                        <th>Status</th>
                                        <!-- <th>Actions</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $mysqli->prepare("SELECT i.inv_id, i.stock_qty, i.min_qty, p.prod_name FROM rpos_inventory i JOIN rpos_products p ON i.prod_id = p.prod_id ORDER BY p.prod_name ASC");
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($row = $res->fetch_object()) {
                                        if ($row->stock_qty == 0) {
                                            $status = '<span class="text-danger font-weight-bold">Out of Stock</span>';
                                            $rowClass = 'table-danger';
                                        } elseif ($row->stock_qty <= $row->min_qty) {
                                            $status = '<span class="text-warning font-weight-bold">Low Stock</span>';
                                            $rowClass = 'table-warning';
                                        } else {
                                            $status = '<span class="text-success">OK</span>';
                                            $rowClass = '';
                                        }

                                        echo "<tr class='{$rowClass}'>
                                                <td>{$row->prod_name}</td>
                                                <td>{$row->stock_qty}</td>
                                                <td>{$row->min_qty}</td>
                                                <td>{$status}</td>
                                              </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
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