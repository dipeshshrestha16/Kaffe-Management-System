<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Add Table
if (isset($_POST['add_table'])) {
    $table_number = $_POST['table_number'];
    $status = $_POST['status'];

    $query = "INSERT INTO rpos_tables (table_number, status) VALUES (?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ss', $table_number, $status);
    $stmt->execute();

    if ($stmt) {
        $success = "Table Added";
    } else {
        $err = "Error Adding Table";
    }
}

// Delete Table
if (isset($_GET['delete'])) {
    $table_id = $_GET['delete'];
    $query = "DELETE FROM rpos_tables WHERE table_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $table_id);
    $stmt->execute();
}
?>

<?php require_once('includes/header.php'); ?>

<body>
    <?php require_once('includes/sidebar.php'); ?>

    <div class="main-content">
        <?php require_once('includes/navbar.php'); ?>

        <div class="container-fluid mt-5">
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4>Add New Table</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label>Table Number</label>
                                    <input type="text" name="table_number" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="available">Available</option>
                                        <option value="occupied">Occupied</option>
                                        <option value="reserved">Reserved</option>
                                    </select>
                                </div>
                                <button type="submit" name="add_table" class="btn btn-success">Add Table</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white">
                            <h4>Current Tables</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Table Number</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT * FROM rpos_tables ORDER BY table_number ASC";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    while ($row = $res->fetch_object()) {
                                        ?>
                                        <tr>
                                            <td><?php echo $row->table_number; ?></td>
                                            <td>
                                                <select class="form-control status-dropdown"
                                                    data-id="<?php echo $row->table_id; ?>">
                                                    <option value="available" <?php if ($row->status == 'available')
                                                        echo 'selected'; ?>>Available</option>
                                                    <option value="occupied" <?php if ($row->status == 'occupied')
                                                        echo 'selected'; ?>>Occupied</option>
                                                    <option value="reserved" <?php if ($row->status == 'reserved')
                                                        echo 'selected'; ?>>Reserved</option>
                                                </select>
                                            </td>
                                            <td>
                                                <a href="manage_tables.php?delete=<?php echo $row->table_id; ?>"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once('includes/footer.php'); ?>
    </div>

    <?php require_once('includes/scripts.php'); ?>

    <script>
        // AJAX: Update table status when dropdown changes
        document.querySelectorAll('.status-dropdown').forEach(function (select) {
            select.addEventListener('change', function () {
                const tableId = this.getAttribute('data-id');
                const newStatus = this.value;

                fetch('update_table_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `table_id=${tableId}&status=${newStatus}`
                })
                    .then(res => res.text())
                    .then(response => {
                        console.log(response); // Optional: For debugging
                    });
            });
        });
    </script>
</body>

</html>