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

        <div class="header pb-8 pt-5 pt-md-8"
            style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;">
            <span class="mask bg-gradient-dark opacity-8"></span>
            <div class="container-fluid">
                <div class="header-body"></div>
            </div>
        </div>

        <div class="container-fluid mt--8">
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

            <?php require_once('includes/footer.php'); ?>
        </div>
    </div>

    <?php require_once('includes/scripts.php'); ?>
</body>

</html>