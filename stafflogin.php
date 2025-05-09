<?php
session_start();
include('config/config.php');

if (isset($_POST['login'])) {
    $staff_email = $_POST['staff_email'];
    $staff_password = sha1(md5($_POST['staff_password'])); // Double hash for added security

    $stmt = $mysqli->prepare("SELECT staff_id FROM rpos_staff WHERE staff_email = ? AND staff_password = ?");
    $stmt->bind_param('ss', $staff_email, $staff_password);
    $stmt->execute();
    $stmt->bind_result($staff_id);

    if ($stmt->fetch()) {
        $_SESSION['staff_id'] = $staff_id;
        $stmt->close();

        // Check if staff has an open shift
        $shift_date = date('Y-m-d');
        $check_stmt = $mysqli->prepare("SELECT id FROM rpos_balances WHERE balance_date = ? AND status = 'open' LIMIT 1");
        $check_stmt->bind_param('s', $shift_date);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            header("Location: staff/index.php");
        } else {
            header("Location: staff/opening_balance.php");
        }
        $check_stmt->close();
        exit;
    } else {
        $login_error = "Invalid credentials. Please try again.";
    }
}
require_once('includes/header.php');
?>

<style>
    .login-btn:hover {
        background-color: rgb(0, 135, 156);
    }

    .login-btn:active {
        background-color: rgb(234, 234, 234);
        color: rgb(0, 0, 0);
    }
</style>

<body class="bg-dark">
    <div class="main-content">
        <div class="header bg-gradient-primar py-7">
            <div class="container mt--12 pt-4 pb-4">
                <div class="header-body text-center mb-7">
                    <div class="row justify-content-center">
                        <div class="col-lg-5 col-md-6">
                            <h1 class="text-white">Kaffe Point Of Sale</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mt--8 pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <div class="card bg-secondary shadow border-0">
                        <div class="card-body px-lg-5 py-lg-5">
                            <form method="post" role="form">
                                <div class="form-group mb-3">
                                    <div class="input-group input-group-alternative">
                                        <div class="input-group-prepend">
                                            <!-- <span class="input-group-text"><i class="ni ni-email-83"></i></span> -->
                                        </div>
                                        <input class="form-control" required name="staff_email" placeholder="Email"
                                            type="email">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="input-group input-group-alternative">
                                        <div class="input-group-prepend">
                                            <!-- <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span> -->
                                        </div>
                                        <input class="form-control" required name="staff_password"
                                            placeholder="Password" type="password">
                                    </div>
                                </div>
                                <div class="custom-control custom-control-alternative custom-checkbox">
                                    <input class="custom-control-input" id=" customCheckLogin" type="checkbox">
                                    <label class="custom-control-label" for=" customCheckLogin">
                                        <span class="text-muted">Remember me</span>
                                    </label>
                                </div>
                                <div class="text-center">
                                    <button type="submit" name="login" class="btn btn my-2 login-btn"
                                        style="background-color:rgb(158, 148, 175);;">Log In</button>
                                </div>
                            </form>

                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <!-- <a href="../admin/forgot_pwd.php" target="_blank" class="text-light"><small>Forgot password?</small></a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    require_once('includes/footer.php');
    ?>
    <!-- Argon Scripts -->
    <?php
    require_once('includes/scripts.php');
    ?>
</body>

</html>