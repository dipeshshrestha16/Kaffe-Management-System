<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$success = $err = "";

// Update Profile
if (isset($_POST['ChangeProfile'])) {
    $admin_id = $_SESSION['admin_id'];
    $admin_name = $_POST['admin_name'];
    $admin_email = $_POST['admin_email'];

    $Qry = "UPDATE rpos_admin SET admin_name = ?, admin_email = ? WHERE admin_id = ?";
    $postStmt = $mysqli->prepare($Qry);
    $postStmt->bind_param('sss', $admin_name, $admin_email, $admin_id);

    if ($postStmt->execute()) {
        $success = "Account Updated Successfully!";
    } else {
        $err = "Please Try Again Later";
    }
}

// Change Password
if (isset($_POST['changePassword'])) {
    $error = 0;
    $old_password = !empty($_POST['old_password']) ? sha1(md5($_POST['old_password'])) : $error = 1;
    $new_password = !empty($_POST['new_password']) ? sha1(md5($_POST['new_password'])) : $error = 1;
    $confirm_password = !empty($_POST['confirm_password']) ? sha1(md5($_POST['confirm_password'])) : $error = 1;

    if ($error) {
        $err = "All Password Fields Are Required";
    } else {
        $admin_id = $_SESSION['admin_id'];
        $sql = "SELECT * FROM rpos_admin WHERE admin_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();

        if ($row && $old_password != $row['admin_password']) {
            $err = "Incorrect Old Password";
        } elseif ($new_password != $confirm_password) {
            $err = "New Password and Confirmation Do Not Match";
        } else {
            $updateQuery = "UPDATE rpos_admin SET admin_password = ? WHERE admin_id = ?";
            $updateStmt = $mysqli->prepare($updateQuery);
            $updateStmt->bind_param('ss', $new_password, $admin_id);
            if ($updateStmt->execute()) {
                $success = "Password Changed Successfully!";
            } else {
                $err = "Password Change Failed";
            }
        }
    }
}

require_once('includes/header.php');
?>

<body>
    <?php require_once('includes/sidebar.php'); ?>
    <div class="main-content">
        <?php require_once('includes/navbar.php'); ?>

        <?php
        $admin_id = $_SESSION['admin_id'];
        $ret = "SELECT * FROM rpos_admin WHERE admin_id = ?";
        $stmt = $mysqli->prepare($ret);
        $stmt->bind_param('s', $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($admin = $res->fetch_object()):
            ?>

            <!-- Header -->
            <div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center"
                style="min-height: 600px; background-image: url(assets/img/theme/restro00.jpg); background-size: cover; background-position: center top;">
                <span class="mask bg-gradient-default opacity-8"></span>
                <div class="container-fluid d-flex align-items-center">
                    <div class="row">
                        <div class="col-lg-7 col-md-10">
                            <h1 class="display-2 text-white">Hello <?php echo $admin->admin_name; ?></h1>
                            <p class="text-white mt-0 mb-5">This is your profile page. You can update your profile and
                                change your password here.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <div class="container-fluid mt--8">
                <div class="row">
                    <div class="col-xl-4 order-xl-2 mb-5 mb-xl-0">
                        <div class="card card-profile shadow">
                            <div class="card-profile-image">
                                <img src="assets/img/theme/user-a-min.png" class="rounded-circle">
                            </div>
                            <div class="card-body pt-0 pt-md-4 mt-5 text-center">
                                <h3><?php echo $admin->admin_name; ?></h3>
                                <p class="h5 font-weight-300"><?php echo $admin->admin_email; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8 order-xl-1">
                        <div class="card bg-secondary shadow">
                            <div class="card-header bg-white border-0">
                                <h3 class="mb-0">My account</h3>
                            </div>
                            <div class="card-body">
                                <!-- Profile Update Form -->
                                <form method="post">
                                    <h6 class="heading-small text-muted mb-4">User information</h6>
                                    <div class="pl-lg-4">
                                        <div class="form-group">
                                            <label>User Name</label>
                                            <input type="text" name="admin_name" value="<?php echo $admin->admin_name; ?>"
                                                class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Email address</label>
                                            <input type="email" name="admin_email"
                                                value="<?php echo $admin->admin_email; ?>" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <input type="submit" name="ChangeProfile" class="btn btn-success"
                                                value="Update Profile">
                                        </div>
                                    </div>
                                </form>

                                <hr class="my-4">

                                <!-- Change Password Form -->
                                <form method="post">
                                    <h6 class="heading-small text-muted mb-4">Change Password</h6>
                                    <div class="pl-lg-4">
                                        <div class="form-group">
                                            <label>Old Password</label>
                                            <input type="password" name="old_password" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>New Password</label>
                                            <input type="password" name="new_password" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Confirm New Password</label>
                                            <input type="password" name="confirm_password" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <input type="submit" name="changePassword" class="btn btn-success"
                                                value="Change Password">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            <?php require_once('includes/footer.php'); ?>
        </div>
    </div>

    <?php require_once('includes/scripts.php'); ?>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Trigger SweetAlert on Success or Error -->
    <script>
        <?php if (!empty($success)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?php echo $success; ?>',
                confirmButtonColor: '#28a745'
            });
        <?php elseif (!empty($err)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo $err; ?>',
                confirmButtonColor: '#dc3545'
            });
        <?php endif; ?>
    </script>
</body>

</html>