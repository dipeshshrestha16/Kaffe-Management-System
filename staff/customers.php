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
                <div class="header-body">
                    <!-- Header content if any -->
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
                            <a href="add_customer.php" class="btn btn-outline-success">
                                <i class="fas fa-user-plus"></i>
                                Add New Customer
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Full Name</th>
                                        <th scope="col">Contact Number</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT * FROM rpos_customers ORDER BY created_at DESC";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($cust = $res->fetch_object()) {
                                        ?>
                                        <tr id="customer-row-<?php echo $cust->customer_id; ?>">
                                            <td><?php echo htmlspecialchars($cust->customer_name); ?></td>
                                            <td><?php echo htmlspecialchars($cust->customer_phoneno); ?></td>
                                            <td><?php echo htmlspecialchars($cust->customer_email); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-danger delete-btn"
                                                    data-id="<?php echo $cust->customer_id; ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php }
                                    $stmt->close();
                                    ?>
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
    <!-- Argon Scripts -->
    <?php require_once('includes/scripts.php'); ?>

    <!-- SweetAlert2 & AJAX Delete Script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const custId = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('delete_customer.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'customer_id=' + encodeURIComponent(custId)
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire(
                                            'Deleted!',
                                            'Customer has been deleted.',
                                            'success'
                                        ).then(() => {
                                            // Option 1: Remove row without reload
                                            const row = document.getElementById('customer-row-' + custId);
                                            if (row) row.remove();

                                            // Option 2: Reload the page (uncomment if you prefer this)
                                            // location.reload();
                                        });
                                    } else {
                                        Swal.fire(
                                            'Error!',
                                            data.message || 'Failed to delete customer.',
                                            'error'
                                        );
                                    }
                                })
                                .catch(() => {
                                    Swal.fire('Error!', 'Request failed.', 'error');
                                });
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>