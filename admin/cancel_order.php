<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Get order_id from URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Check if the order_id is valid
if ($order_id > 0) {
    // Delete the order from the rpos_tableorders table
    $cancel_query = "DELETE FROM rpos_tableorders WHERE order_id = ?";
    $cancel_stmt = $mysqli->prepare($cancel_query);
    $cancel_stmt->bind_param('i', $order_id);
    $cancel_stmt->execute();

    // Redirect back to the table orders page
    header("Location: tables.php");
    exit();
} else {
    // If invalid order_id, redirect back to the table orders page
    header("Location: tables.php");
    exit();
}
?>