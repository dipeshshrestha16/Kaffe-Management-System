<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Handle delete request
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
        $stmt->close(); // Move close here, after executing the statement
    } else {
        $err = "Prepare statement failed: " . $mysqli->error; // Debugging line
    }
}


// Get selected category filter
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';

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
                                <!-- Search Bar -->
                                <!-- Search Bar -->
                                <input type="text" name="search" class="form-control" placeholder="Search Product"
                                    value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>"
                                    style="margin-right: 5px;">
                                <!-- Search Button -->
                                <button type="submit" class="btn btn-primary">Search</button>

                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Image</th>
                                        <th scope="col">Product Code</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Price</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Get category and search filters
                                    $category = isset($_GET['category']) ? $_GET['category'] : '';
                                    $search = isset($_GET['search']) ? $_GET['search'] : '';

                                    // Base query
                                    $query = "SELECT * FROM rpos_products WHERE 1";

                                    // If category is selected
                                    if (!empty($category)) {
                                        $query .= " AND categorie = ?";
                                    }

                                    // If search is used
                                    if (!empty($search)) {
                                        $query .= " AND prod_name LIKE ?";
                                    }

                                    // Prepare and execute statement
                                    $stmt = $mysqli->prepare($query);

                                    // Bind parameters
                                    if (!empty($category) && !empty($search)) {
                                        $searchTerm = "%$search%";
                                        $stmt->bind_param('ss', $category, $searchTerm);
                                    } elseif (!empty($category)) {
                                        $stmt->bind_param('s', $category);
                                    } elseif (!empty($search)) {
                                        $searchTerm = "%$search%";
                                        $stmt->bind_param('s', $searchTerm);
                                    }

                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    while ($prod = $res->fetch_object()) {
                                        ?>
                                        <tr>
                                            <td><?php
                                            if ($prod->prod_img) {
                                                echo "<img src='assets/img/products/$prod->prod_img' height='60' width='60 class='img-thumbnail'>";
                                            } else {
                                                echo "<img src='assets/img/products/default.jpg' height='60' width='60 class='img-thumbnail'>";
                                            }

                                            ?></td>
                                            <td><?php echo $prod->prod_code; ?></td>
                                            <td><?php echo $prod->prod_name; ?></td>
                                            <td>Rs.<?php echo $prod->prod_price; ?></td>
                                            <td>
                                                <a href="menu.php?delete=<?php echo $prod->prod_id; ?>">
                                                    <button class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </a>
                                                <a href="update_product.php?update=<?php echo $prod->prod_id; ?>">
                                                    <button class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i> Update
                                                    </button>
                                                </a>
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