<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

$success = $err = "";

// Auto-generate staff number if not already generated
if (!isset($alpha)) {
    $alpha = strtoupper(substr(md5(time()), 0, 4));
}
if (!isset($beta)) {
    $beta = rand(1000, 9999);
}

// Add Staff
if (isset($_POST['addStaff'])) {
    if (
        empty($_POST["staff_number"]) ||
        empty($_POST["staff_name"]) ||
        empty($_POST['staff_email']) ||
        empty($_POST['staff_password'])
    ) {
        $err = "Error: Blank Values Not Accepted.";
    } else {
        $staff_number = $_POST['staff_number'];
        $staff_name = $_POST['staff_name'];
        $staff_email = $_POST['staff_email'];
        $staff_password = sha1(md5($_POST['staff_password']));

        $postQuery = "INSERT INTO rpos_staff (staff_number, staff_name, staff_email, staff_password) VALUES (?, ?, ?, ?)";
        $postStmt = $mysqli->prepare($postQuery);

        if ($postStmt === false) {
            $err = "Database prepare failed: " . htmlspecialchars($mysqli->error);
        } else {
            $postStmt->bind_param('ssss', $staff_number, $staff_name, $staff_email, $staff_password);
            if ($postStmt->execute()) {
                $success = "✅ Staff Added Successfully!";
                header("refresh:2; url=view_staff.php");
            } else {
                $err = "❌ Failed to add staff: " . htmlspecialchars($postStmt->error);
            }
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
        <!-- Top navbar -->
        <?php require_once('includes/navbar.php'); ?>

        <!-- Header -->
        <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;"
            class="header pb-8 pt-5 pt-md-8">
            <span class="mask bg-gradient-dark opacity-8"></span>
            <div class="container-fluid">
                <div class="header-body"></div>
            </div>
        </div>

        <!-- Page content -->
        <div class="container-fluid mt--8">
            <div class="row">
                <div class="col">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <h3>Please Fill All Fields</h3>
                        </div>
                        <div class="card-body">

                            <!-- Alert messages -->
                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>
                            <?php if (!empty($err)): ?>
                                <div class="alert alert-danger"><?php echo $err; ?></div>
                            <?php endif; ?>

                            <!-- Form -->
                            <form method="POST">
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>Staff Number</label>
                                        <input type="text" name="staff_number" class="form-control"
                                            value="<?php echo $alpha . '-' . $beta; ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Staff Name</label>
                                        <input type="text" name="staff_name" class="form-control" value="">
                                    </div>
                                </div>

                                <hr>

                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>Staff Email</label>
                                        <input type="email" name="staff_email" class="form-control" value="">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Staff Password</label>
                                        <input type="password" name="staff_password" class="form-control" value="">
                                    </div>
                                </div>

                                <br>

                                <div class="form-row">
                                    <div class="col-md-6">
                                        <input type="submit" name="addStaff" value="Add Staff" class="btn btn-success">
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

    <!-- Scripts -->
    <?php require_once('includes/scripts.php'); ?>
</body>

</html>