<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

if (isset($_POST['add_inventory'])) {
    $prod_id = $_POST['prod_id'];
    $stock_qty = $_POST['stock_qty'];
    $min_qty = $_POST['min_qty'];

    $stmt = $mysqli->prepare("INSERT INTO rpos_inventory (prod_id, stock_qty, min_qty) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE stock_qty = ?, min_qty = ?");
    $stmt->bind_param('siiii', $prod_id, $stock_qty, $min_qty, $stock_qty, $min_qty);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Inventory updated successfully";
        header("Location: inventory.php"); // Don't use refresh unless needed
        exit(); // Important to stop script after header
    } else {
        $_SESSION['err'] = "Failed to add inventory";
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
                <div class="col-xl-8 order-xl-1">
                    <div class="card bg-secondary shadow">
                        <div class="card-header bg-white border-0">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h3 class="mb-0">Add Inventory Item</h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="pl-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label" for="prod_id">Select Product</label>
                                        <select name="prod_id" class="form-control" required>
                                            <option value="">-- Choose Product --</option>
                                            <?php
                                            $result = $mysqli->query("SELECT prod_id, prod_name FROM rpos_products WHERE status='enabled'");
                                            while ($row = $result->fetch_object()) {
                                                echo "<option value='{$row->prod_id}'>{$row->prod_name}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-control-label" for="stock_qty">Stock Quantity</label>
                                        <input type="number" name="stock_qty" class="form-control" min="0" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-control-label" for="min_qty">Minimum Quantity (for
                                            alert)</label>
                                        <input type="number" name="min_qty" class="form-control" min="0" required>
                                    </div>

                                    <button type="submit" name="add_inventory" class="btn btn-success">Save
                                        Inventory</button>
                                </div>
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