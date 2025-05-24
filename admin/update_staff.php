<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

$success = $err = "";

// Update Staff
if (isset($_POST['UpdateStaff'])) {
    if (
        empty($_POST["staff_number"]) ||
        empty($_POST["staff_name"]) ||
        empty($_POST['staff_email']) ||
        empty($_POST['staff_password'])
    ) {
        $err = "Blank Values Not Accepted";
    } else {
        $staff_number = $_POST['staff_number'];
        $staff_name = $_POST['staff_name'];
        $staff_email = $_POST['staff_email'];
        $staff_password = sha1(md5($_POST['staff_password']));
        $update = $_GET['update'];

        $postQuery = "UPDATE rpos_staff SET staff_number = ?, staff_name = ?, staff_email = ?, staff_password = ? WHERE staff_id = ?";
        $postStmt = $mysqli->prepare($postQuery);
        if ($postStmt) {
            $postStmt->bind_param('ssssi', $staff_number, $staff_name, $staff_email, $staff_password, $update);
            if ($postStmt->execute()) {
                $success = "Staff Updated Successfully!";
                header("refresh:2; url=view_staff.php");
            } else {
                $err = "Failed to update staff. Please try again.";
            }
        } else {
            $err = "Database error: " . $mysqli->error;
        }
    }
}
require_once('includes/header.php');
?>

<body>
    <!-- Sidenav -->
    <?php require_once('includes/sidebar.php'); ?>
    <div class="main-content">
        <!-- Top navbar -->
        <?php require_once('includes/navbar.php'); ?>

        <?php
        $update = $_GET['update'];
        $ret = "SELECT * FROM rpos_staff WHERE staff_id = ?";
        $stmt = $mysqli->prepare($ret);
        $stmt->bind_param('i', $update);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($staff = $res->fetch_object()) {
            ?>
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
                                <h3>Edit Staff Details</h3>
                                <!-- Success/Error Messages -->
                                <?php if ($success): ?>
                                    <div class="alert alert-success"><?php echo $success; ?></div>
                                <?php elseif ($err): ?>
                                    <div class="alert alert-danger"><?php echo $err; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <label>Staff Number</label>
                                            <input type="text" name="staff_number" class="form-control"
                                                value="<?php echo $staff->staff_number; ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Staff Name</label>
                                            <input type="text" name="staff_name" class="form-control"
                                                value="<?php echo $staff->staff_name; ?>">
                                        </div>
                                    </div>

                                    <div class="form-row mt-3">
                                        <div class="col-md-6">
                                            <label>Staff Email</label>
                                            <input type="email" name="staff_email" class="form-control"
                                                value="<?php echo $staff->staff_email; ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Staff Password</label>
                                            <input type="password" name="staff_password" class="form-control"
                                                placeholder="Enter new password">
                                        </div>
                                    </div>

                                    <br>
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <input type="submit" name="UpdateStaff" value="Update Staff"
                                                class="btn btn-success">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="container mt-4">
                    <div class="alert alert-danger">Staff record not found.</div>
                </div>
            <?php } ?>

            <?php require_once('includes/footer.php'); ?>
        </div>
        <?php require_once('includes/scripts.php'); ?>
</body>

</html>