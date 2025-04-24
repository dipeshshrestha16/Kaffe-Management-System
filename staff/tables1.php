<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Fetch tables from database
$query = "SELECT * FROM rpos_table";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$tables = $stmt->get_result();
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
                    <h3 class="mb-0">Manage Tables</h3>
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th>Table Name</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($table = $tables->fetch_object()) { ?>
                                <tr>
                                    <td><?php echo $table->table_name; ?></td>
                                    <td>
                                        <?php echo ($table->o_status == 'available')
                                            ? '<span class="badge badge-success">Available</span>'
                                            : '<span class="badge badge-danger">Occupied</span>'; ?>
                                    </td>
                                    <td>
                                        <a href="orders.php?table_id=<?php echo $table->table_id; ?>"
                                            class="btn btn-sm btn-primary">Orders</a>
                                        <a href="print_bill.php?table_id=<?php echo $table->table_id; ?>"
                                            class="btn btn-sm btn-warning">Print Bill</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php require_once('includes/footer.php'); ?>
    <?php require_once('includes/scripts.php'); ?>
</body>

</html>