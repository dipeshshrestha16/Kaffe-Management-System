<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$success = $err = "";

// Update Profile
if (isset($_POST['ChangeProfile'])) {
    $staff_id = $_SESSION['staff_id'];
    $staff_name = $_POST['staff_name'];
    $staff_email = $_POST['staff_email'];

    $Qry = "UPDATE rpos_staff SET staff_name = ?, staff_email = ? WHERE staff_id = ?";
    $postStmt = $mysqli->prepare($Qry);
    $postStmt->bind_param('ssi', $staff_name, $staff_email, $staff_id);

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
        $staff_id = $_SESSION['staff_id'];
        $sql = "SELECT * FROM rpos_staff WHERE staff_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $staff_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();

        if (!$row) {
            $err = "Staff record not found";
        } elseif ($old_password != $row['staff_password']) {
            $err = "Incorrect Old Password";
        } elseif ($new_password != $confirm_password) {
            $err = "New Password and Confirmation Do Not Match";
        } else {
            $updateQuery = "UPDATE rpos_staff SET staff_password = ? WHERE staff_id = ?";
            $updateStmt = $mysqli->prepare($updateQuery);
            $updateStmt->bind_param('si', $new_password, $staff_id);
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

<!-- Add SweetAlert2 CSS and JS -->
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<body>
    <?php require_once('includes/sidebar.php'); ?>
    <div class="main-content">
        <?php require_once('includes/navbar.php'); ?>

        <?php
        $staff_id = $_SESSION['staff_id'];
        $ret = "SELECT * FROM rpos_staff WHERE staff_id = ?";
        $stmt = $mysqli->prepare($ret);
        $stmt->bind_param('i', $staff_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($staff = $res->fetch_object()):
            ?>

            <!-- Header -->
            <div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center"
                style="min-height: 600px; background-image: url(../admin/assets/img/theme/restro00.jpg); background-size: cover; background-position: center top;">
                <span class="mask bg-gradient-default opacity-8"></span>
                <div class="container-fluid d-flex align-items-center">
                    <div class="row">
                        <div class="col-lg-7 col-md-10">
                            <h1 class="display-2 text-white">Hello <?php echo $staff->staff_name; ?></h1>
                            <p class="text-white mt-0 mb-5">This is your profile page. You can customize your profile and
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
                            <div class="card-profile-image mt-5">
                                <img src="../admin/assets/img/theme/user-a-min.png" class="rounded-circle mx-auto d-block">
                            </div>
                            <div class="card-body pt-0 pt-md-4 mt-5 text-center">
                                <h3><?php echo $staff->staff_name; ?></h3>
                                <p class="h5 font-weight-300"><?php echo $staff->staff_email; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8 order-xl-1">
                        <div class="card bg-secondary shadow">
                            <div class="card-header bg-white border-0">
                                <h3 class="mb-0">My Account</h3>
                            </div>
                            <div class="card-body">
                                <!-- Profile Update Form -->
                                <form method="post" novalidate>
                                    <h6 class="heading-small text-muted mb-4">User Information</h6>
                                    <div class="pl-lg-4">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="input-username">User Name</label>
                                                    <input type="text" name="staff_name" id="input-username"
                                                        class="form-control form-control-alternative"
                                                        value="<?php echo htmlspecialchars($staff->staff_name); ?>"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="input-email">Email Address</label>
                                                    <input type="email" name="staff_email" id="input-email"
                                                        class="form-control form-control-alternative"
                                                        value="<?php echo htmlspecialchars($staff->staff_email); ?>"
                                                        required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <input type="submit" name="ChangeProfile"
                                                    class="btn btn-success form-control-alternative" value="Update Profile">
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <hr>

                                <!-- Password Change Form -->
                                <form method="post" novalidate>
                                    <h6 class="heading-small text-muted mb-4">Change Password</h6>
                                    <div class="pl-lg-4">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label for="old-password">Old Password</label>
                                                    <input type="password" name="old_password" id="old-password"
                                                        class="form-control form-control-alternative" required>
                                                </div>
                                            </div>

                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label for="new-password">New Password</label>
                                                    <input type="password" name="new_password" id="new-password"
                                                        class="form-control form-control-alternative" required>
                                                </div>
                                            </div>

                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label for="confirm-password">Confirm New Password</label>
                                                    <input type="password" name="confirm_password" id="confirm-password"
                                                        class="form-control form-control-alternative" required>
                                                </div>
                                            </div>

                                            <div class="col-lg-12">
                                                <input type="submit" name="changePassword"
                                                    class="btn btn-success form-control-alternative"
                                                    value="Change Password">
                                            </div>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>

                <?php
        endwhile;
        ?>

        </div>
    </div>

    <?php require_once('includes/footer.php'); ?>

    <script>
        // SweetAlert2 popup for success or error
        <?php if (!empty($success)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo addslashes($success); ?>',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        <?php endif; ?>

        <?php if (!empty($err)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo addslashes($err); ?>',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        <?php endif; ?>
    </script>
</body>

</html>