<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

if (isset($_POST['addProduct'])) {
    if (empty($_POST["prod_code"]) || empty($_POST["prod_name"]) || empty($_POST['prod_desc']) || empty($_POST['prod_price']) || empty($_POST['categorie'])) {
        $_SESSION['err'] = "Blank values are not accepted.";
    } else {
        $prod_id = $_POST['prod_id'];
        $prod_code = $_POST['prod_code'];
        $prod_name = $_POST['prod_name'];
        $prod_img = $_FILES['prod_img']['name'];

        // Move uploaded file to correct location
        if (!empty($prod_img)) {
            move_uploaded_file($_FILES["prod_img"]["tmp_name"], "assets/img/products/" . $prod_img);
        }

        $prod_desc = $_POST['prod_desc'];
        $prod_price = $_POST['prod_price'];
        $categorie = $_POST['categorie'];

        $postQuery = "INSERT INTO rpos_products (prod_id, prod_code, prod_name, prod_img, prod_desc, prod_price, categorie) VALUES(?,?,?,?,?,?,?)";
        $postStmt = $mysqli->prepare($postQuery);
        $postStmt->bind_param('sssssss', $prod_id, $prod_code, $prod_name, $prod_img, $prod_desc, $prod_price, $categorie);

        if ($postStmt->execute()) {
            $_SESSION['success'] = "Product added successfully.";
            // Let the page reload normally — redirect via JS later
        } else {
            $_SESSION['err'] = "Failed to add product. Please try again.";
        }
    }
}
require_once('includes/header.php');
?>

<body>
    <!-- Sidenav -->
    <?php require_once('includes/sidebar.php'); ?>

    <!-- Main content -->
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
                        <div class="card-header border-0">
                            <h3>Please Fill All Fields</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>Product Name</label>
                                        <input type="text" name="prod_name" class="form-control">
                                        <input type="hidden" name="prod_id" value="<?php echo $prod_id; ?>"
                                            class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Product Code</label>
                                        <input type="text" name="prod_code"
                                            value="<?php echo $alpha; ?>-<?php echo $beta; ?>" class="form-control">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>Product Image</label>
                                        <input type="file" name="prod_img" class="btn btn-outline-success form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Product Price</label>
                                        <input type="text" name="prod_price" class="form-control">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-row">
                                    <div class="col-md-12">
                                        <label>Product Description</label>
                                        <textarea rows="5" name="prod_desc" class="form-control"></textarea>
                                    </div>
                                </div>
                                <br>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>Product Category</label>
                                        <select name="categorie" class="form-control">
                                            <option value="">Select Category</option>
                                            <option value="hot coffee beverage">Hot Coffee Beverage</option>
                                            <option value="cold coffee beverage">Cold Coffee Beverage</option>
                                            <option value="hot coffee alternative">Hot Coffee Alternative</option>
                                            <option value="cold coffee alternative">Cold Coffee Alternative</option>
                                            <option value="bakery">Bakery</option>
                                            <option value="cookies">Cookies</option>
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <input type="submit" name="addProduct" value="Add Product"
                                            class="btn btn-success">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php require_once('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Argon Scripts -->
    <?php require_once('includes/scripts.php'); ?>

    <!-- SweetAlert2 Script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- SweetAlert2 Flash Message Handler -->
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?php echo $_SESSION["success"]; ?>',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'menu.php';
                }
            });
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['err'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo $_SESSION["err"]; ?>',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Try Again'
            });
        </script>
        <?php unset($_SESSION['err']); ?>
    <?php endif; ?>
</body>

</html>