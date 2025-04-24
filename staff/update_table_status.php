<?php
include('config/config.php');

if (isset($_POST['table_id']) && isset($_POST['status'])) {
    $table_id = $_POST['table_id'];
    $status = $_POST['status'];

    $query = "UPDATE rpos_tables SET status = ? WHERE table_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('si', $status, $table_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Status updated.";
    } else {
        echo "No change or error.";
    }
} else {
    echo "Invalid request.";
}
?>