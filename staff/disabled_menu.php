<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Handle status toggle request
if (isset($_GET['toggle_status'])) {
    $id = intval($_GET['toggle_status']);
    $current_status = 'enabled';

    $stmt = $mysqli->prepare("UPDATE rpos_products SET status = ? WHERE prod_id = ?");
    if ($stmt) {
        $stmt->bind_param('si', $current_status, $id);
        if ($stmt->execute()) {
            $success = "Product enabled";
            header("refresh:1; url=disabled_menu.php");
        } else {
            $err = "Failed to update status";
        }
        $stmt->close();
    } else {
        $err = "Prepare statement failed: " . $mysqli->error;
    }
}

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
                <div class="header-body">
                </div>
            </div>
        </div>
        <div class="container-fluid mt--8">
            <div class="row">
                <div class="col">
                    <div class="card shadow">
                        <div class="card-header border-0 d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">Disabled Products</h3>
                            <a href="menu.php" class="btn btn-outline-primary">Back to Menu</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Image</th>
                                        <th>Product Code</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $mysqli->prepare("SELECT * FROM rpos_products WHERE status = 'disabled'");
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($prod = $res->fetch_object()) {
                                        ?>
                                        <tr>
                                            <td><img src='assets/img/products/<?php echo $prod->prod_img ?: 'default.jpg'; ?>'
                                                    height='60' width='60' class='img-thumbnail'></td>
                                            <td><?php echo $prod->prod_code; ?></td>
                                            <td><?php echo $prod->prod_name; ?></td>
                                            <td>Rs.<?php echo $prod->prod_price; ?></td>
                                            <td>
                                                <a href="disabled_menu.php?toggle_status=<?php echo $prod->prod_id; ?>"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-toggle-on"></i> Enable
                                                </a>
                                            </td>
                                            <td><span class='badge badge-secondary'>Disabled</span></td>

                                        </tr>
                                    <?php } ?>
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