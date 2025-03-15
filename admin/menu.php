<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Handle delete request
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $adn = "DELETE FROM rpos_products WHERE prod_id = ?";
    $stmt = $mysqli->prepare($adn);

    if ($stmt) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $success = "Deleted";
            header("refresh:1; url=menu.php");
        } else {
            $err = "Try Again Later";
        }
        $stmt->close();
    } else {
        $err = "Prepare statement failed: " . $mysqli->error;
    }
}

// Handle status toggle request
if (isset($_GET['toggle_status'])) {
    $id = intval($_GET['toggle_status']);
    $current_status = $_GET['current_status'] === 'enabled' ? 'disabled' : 'enabled';

    $stmt = $mysqli->prepare("UPDATE rpos_products SET status = ? WHERE prod_id = ?");
    if ($stmt) {
        $stmt->bind_param('si', $current_status, $id);
        if ($stmt->execute()) {
            $success = "Product status updated";
            header("refresh:1; url=menu.php");
        } else {
            $err = "Failed to update status";
        }
        $stmt->close();
    } else {
        $err = "Prepare statement failed: " . $mysqli->error;
    }
}

$selected_category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

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
                            <a href="edit-menu.php" class="btn btn-outline-success">
                                <i class="fas fa-utensils"></i> Add New Product
                            </a>
                            <a href="disabled_menu.php" class="btn btn-outline-warning">
                                <i class="fas fa-eye-slash"></i> View Disabled Products
                            </a>
                            <form method="GET" action="menu.php" class="d-flex">
                                <select name="category" class="form-control" onchange="this.form.submit()"
                                    style="margin-right: 5px;">
                                    <option value="">All Categories</option>
                                    <option value="hot coffee beverage" <?= ($selected_category == "hot coffee beverage") ? 'selected' : ''; ?>>Hot Coffee Beverage</option>
                                    <option value="cold coffee beverage" <?= ($selected_category == "cold coffee beverage") ? 'selected' : ''; ?>>Cold Coffee Beverage</option>
                                    <option value="hot coffee alternative" <?= ($selected_category == "hot coffee alternative") ? 'selected' : ''; ?>>Hot Coffee Alternative</option>
                                    <option value="cold coffee alternative" <?= ($selected_category == "cold coffee alternative") ? 'selected' : ''; ?>>Cold Coffee Alternative</option>
                                    <option value="bakery" <?= ($selected_category == "bakery") ? 'selected' : ''; ?>>
                                        Bakery</option>
                                    <option value="cookies" <?= ($selected_category == "cookies") ? 'selected' : ''; ?>>
                                        Cookies</option>
                                </select>
                                <input type="text" name="search" class="form-control" placeholder="Search Product"
                                    value="<?php echo $search; ?>" style="margin-right: 5px;">
                                <button type="submit" class="btn btn-primary">Search</button>
                            </form>
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
                                    $query = "SELECT * FROM rpos_products WHERE status = 'enabled'";
                                    if (!empty($selected_category)) {
                                        $query .= " AND categorie = ?";
                                    }
                                    if (!empty($search)) {
                                        $query .= " AND prod_name LIKE ?";
                                    }
                                    $stmt = $mysqli->prepare($query);
                                    if (!empty($selected_category) && !empty($search)) {
                                        $searchTerm = "%$search%";
                                        $stmt->bind_param('ss', $selected_category, $searchTerm);
                                    } elseif (!empty($selected_category)) {
                                        $stmt->bind_param('s', $selected_category);
                                    } elseif (!empty($search)) {
                                        $searchTerm = "%$search%";
                                        $stmt->bind_param('s', $searchTerm);
                                    }
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
                                                <a href="menu.php?toggle_status=<?php echo $prod->prod_id; ?>&current_status=<?php echo $prod->status; ?>"
                                                    class="btn btn-sm btn-warning">
                                                    <i class="fas fa-toggle-off"></i> Available
                                                </a><span class='badge badge-success'>Enabled</span>
                                            </td>
                                            <td>
                                                <a href="menu.php?delete=<?php echo $prod->prod_id; ?>"
                                                    class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</a>
                                                <a href="update_product.php?update=<?php echo $prod->prod_id; ?>"
                                                    class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Update</a>

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
        </div>
    </div>
    <?php require_once('includes/scripts.php'); ?>
</body>

</html>