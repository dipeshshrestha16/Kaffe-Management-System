<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$user_id = $_SESSION['admin_id'];
include('includes/header.php');

// Fetch shift data
$stmt = $mysqli->prepare("SELECT id, balance_date, opening_balance, closing_balance, total_sales, pay_ins, pay_outs, expected_amount, actual_amount, variance, status, created_at, updated_at FROM rpos_balances WHERE user_id = ? ORDER BY balance_date DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
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
                    <h1 class="text-white">Shift Report</h1>
                </div>
            </div>
        </div>

        <div class="container-fluid mt--8">
            <div class="row">
                <div class="col">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <h3 class="mb-0">All Shift Balances</h3>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-items-center table-flush table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Opening</th>
                                        <th>Sales</th>
                                        <th>Pay Ins</th>
                                        <th>Pay Outs</th>
                                        <th>Expected</th>
                                        <th>Actual</th>
                                        <th>Variance</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['balance_date']; ?></td>
                                            <td>Rs. <?php echo number_format($row['opening_balance'], 2); ?></td>
                                            <td>Rs. <?php echo number_format($row['total_sales'], 2); ?></td>
                                            <td>Rs. <?php echo number_format($row['pay_ins'], 2); ?></td>
                                            <td>Rs. <?php echo number_format($row['pay_outs'], 2); ?></td>
                                            <td>Rs. <?php echo number_format($row['expected_amount'], 2); ?></td>
                                            <td>Rs. <?php echo number_format($row['actual_amount'], 2); ?></td>
                                            <td
                                                class="<?php echo ($row['variance'] < 0) ? 'text-danger font-weight-bold' : (($row['variance'] > 0) ? 'text-success font-weight-bold' : ''); ?>">
                                                Rs. <?php echo number_format($row['variance'], 2); ?>
                                            </td>

                                            <td>
                                                <span
                                                    class="<?php echo ($row['status'] == 'open') ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $row['created_at']; ?></td>
                                            <td><?php echo $row['updated_at']; ?></td>
                                            <td>
                                                <a target="_blank"
                                                    href="print_shift_report.php?id=<?php echo $row['id']; ?>"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fas fa-print"></i> Print
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
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