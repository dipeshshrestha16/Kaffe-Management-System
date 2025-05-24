<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
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
                            <a href="add_staff.php" class="btn btn-outline-success">
                                <i class="fas fa-user-plus"></i> Add New Staff
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Staff Number</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT * FROM rpos_staff";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($staff = $res->fetch_object()) {
                                        ?>
                                        <tr>
                                            <td><?php echo $staff->staff_number; ?></td>
                                            <td><?php echo $staff->staff_name; ?></td>
                                            <td><?php echo $staff->staff_email; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-danger deleteStaff"
                                                    data-id="<?php echo $staff->staff_id; ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>

                                                <a href="update_staff.php?update=<?php echo $staff->staff_id; ?>">
                                                    <button class="btn btn-sm btn-primary">
                                                        <i class="fas fa-user-edit"></i> Update
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

            <!-- Footer -->
            <?php require_once('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Scripts -->
    <?php require_once('includes/scripts.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const deleteButtons = document.querySelectorAll(".deleteStaff");

            deleteButtons.forEach(button => {
                button.addEventListener("click", function () {
                    const staffId = this.getAttribute("data-id");

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will permanently delete the staff account!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e3342f',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Send AJAX request
                            fetch('delete_staff.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: 'id=' + staffId
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        Swal.fire('Deleted!', 'The staff member has been deleted.', 'success')
                                            .then(() => location.reload());
                                    } else {
                                        Swal.fire('Error!', 'Failed to delete. Try again.', 'error');
                                    }
                                });
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>