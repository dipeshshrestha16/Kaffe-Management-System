<?php
session_start();
include('config/config.php');
//login 
if (isset($_POST['login'])) {
    $admin_email = $_POST['admin_email'];
    $admin_password = sha1(md5($_POST['admin_password'])); //double encrypt to increase security
    $stmt = $mysqli->prepare("SELECT admin_email, admin_password, admin_id  FROM   rpos_admin WHERE (admin_email =? AND admin_password =?)"); //sql to log in user
    $stmt->bind_param('ss', $admin_email, $admin_password); //bind fetched parameters
    $stmt->execute();
    $stmt->bind_result($admin_email, $admin_password, $admin_id);
    $rs = $stmt->fetch();
    $_SESSION['admin_id'] = $admin_id;
    if ($rs) {

        header("location:admin/index.php");
    } else {
        $err = "Incorrect Authentication Credentials ";
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
                                        <input class="form-control" required name="admin_email" placeholder="Email"
                                            type="email">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="input-group input-group-alternative">
                                        <div class="input-group-prepend">
                                            <!-- <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span> -->
                                        </div>
                                        <input class="form-control" required name="admin_password"
                                            placeholder="Password" type="password">
                                    </div>
                                </div>
                                <div class="custom-control custom-control-alternative custom-checkbox">
                                    <input class="custom-control-input" id=" customCheckLogin" type="checkbox">
                                    <label class="custom-control-label" for=" customCheckLogin">
                                        <span class="text-muted">Remember Me</span>
                                    </label>
                                </div>
                                <div class="text-center">
                                    <button type="submit" name="login" class="btn btn my-2 login-btn"
                                        style="background-color:rgb(158, 148, 175);;">Log In</button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    require_once('includes/footer.php');
    ?>
    <?php
    require_once('includes/scripts.php');
    ?>
</body>

</html>