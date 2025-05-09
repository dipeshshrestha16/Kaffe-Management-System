<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Handle Update
if (isset($_POST['update'])) {
    $inv_id = $_POST['inv_id'];
    $stock_qty = $_POST['stock_qty'];
    $min_qty = $_POST['min_qty'];

    $stmt = $mysqli->prepare("UPDATE rpos_inventory SET stock_qty = ?, min_qty = ? WHERE inv_id = ?");
    $stmt->bind_param('iii', $stock_qty, $min_qty, $inv_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = "Inventory updated successfully.";
    } else {
        $err = "No changes made.";
    }
    $stmt->close();
}

// Handle Delete
if (isset($_POST['delete'])) {
    $inv_id = $_POST['inv_id'];

    $stmt = $mysqli->prepare("DELETE FROM rpos_inventory WHERE inv_id = ?");
    $stmt->bind_param('i', $inv_id);
    $stmt->execute();
    $stmt->close();

    header("Location: inventory.php");
    exit;
}

// Fetch inventory item
if (isset($_GET['inv_id'])) {
    $inv_id = $_GET['inv_id'];
    $stmt = $mysqli->prepare("SELECT i.inv_id, i.stock_qty, i.min_qty, p.prod_name FROM rpos_inventory i JOIN rpos_products p ON i.prod_id = p.prod_id WHERE i.inv_id = ?");
    $stmt->bind_param('i', $inv_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $item = $res->fetch_assoc();
    $stmt->close();
} else {
    die("Inventory item not found.");
}
?>

<!DOCTYPE html>
<html>
<?php include('includes/header.php'); ?>

<body>
    <?php include('includes/sidebar.php'); ?>
    <div class="main-content">
        <?php include('includes/navbar.php'); ?>

        <div class="container mt-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4>Edit Inventory Item - <?php echo htmlspecialchars($item['prod_name']); ?></h4>
                </div>
                <div class="card-body">
                    <?php if (isset($success))
                        echo "<div class='alert alert-success'>$success</div>"; ?>
                    <?php if (isset($err))
                        echo "<div class='alert alert-danger'>$err</div>"; ?>

                    <form method="post">
                        <input type="hidden" name="inv_id" value="<?php echo $item['inv_id']; ?>">
                        <div class="form-group">
                            <label>Stock Quantity</label>
                            <input type="number" name="stock_qty" class="form-control"
                                value="<?php echo $item['stock_qty']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Minimum Quantity</label>
                            <input type="number" name="min_qty" class="form-control"
                                value="<?php echo $item['min_qty']; ?>" required>
                        </div>
                        <div class="form-group mt-3">
                            <button type="submit" name="update" class="btn btn-success">Update</button>
                            <button type="submit" name="delete" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to delete this inventory item?');">Delete</button>
                            <a href="inventory.php" class="btn btn-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include('includes/footer.php'); ?>
    </div>
    <?php include('includes/scripts.php'); ?>
</body>

</html>