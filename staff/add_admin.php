<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();
//Add Staff
if (isset($_POST['addStaff'])) {
    //Prevent Posting Blank Values
    if (empty($_POST["staff_number"]) || empty($_POST["staff_name"]) || empty($_POST['staff_email']) || empty($_POST['staff_password'])) {
        $err = "Blank Values Not Accepted";
    } else {
        $staff_number = $_POST['staff_number'];
        $staff_name = $_POST['staff_name'];
        $staff_email = $_POST['staff_email'];
        $staff_password = sha1(md5($_POST['staff_password']));

        //Insert Captured information to a database table
        $postQuery = "INSERT INTO rpos_staff (staff_number, staff_name, staff_email, staff_password) VALUES(?,?,?,?)";
        $postStmt = $mysqli->prepare($postQuery);
        //bind paramaters
        $rc = $postStmt->bind_param('ssss', $staff_number, $staff_name, $staff_email, $staff_password);
        $postStmt->execute();
        //declare a varible which will be passed to alert function
        if ($postStmt) {
            $success = "Staff Added" && header("refresh:1; url=hrm.php");
        } else {
            $err = "Please Try Again Or Try Later";
        }
    }
}
require_once('includes/header.php');
?>

<body>
    <!-- Sidenav -->
    <?php
    require_once('includes/sidebar.php');
    ?>
    <!-- Main content -->
    <div class="main-content">
        <!-- Top navbar -->
        <?php
        require_once('includes/navbar.php');
        ?>
        <!-- Header -->
        <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;"
            class="header  pb-8 pt-5 pt-md-8">
            <span class="mask bg-gradient-dark opacity-8"></span>
            <div class="container-fluid">
                <div class="header-body">
                </div>
            </div>
        </div>
        <!-- Page content -->
        <div class="container-fluid mt--8">
            <!-- Table -->
            <div class="row">
                <div class="col">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <h3>Please Fill All Fields</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>Admin Number</label>
                                        <input type="text" name="staff_number" class="form-control"
                                            value="<?php echo $alpha; ?>-<?php echo $beta; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Admin Name</label>
                                        <input type="text" name="staff_name" class="form-control" value="">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>Admin Email</label>
                                        <input type="email" name="staff_email" class="form-control" value="">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Admin Password</label>
                                        <input type="password" name="staff_password" class="form-control" value="">
                                    </div>
                                </div>
                                <br>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <input type="submit" name="addStaff" value="Add Admin" class="btn btn-success"
                                            value="">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            <?php
            require_once('includes/footer.php');
            ?>
        </div>
    </div>
    <!-- Argon Scripts -->
    <?php
    require_once('includes/scripts.php');
    ?>
</body>

</html>