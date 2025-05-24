<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

if ($order_id > 0 && $table_id > 0) {
    // Fetch order details
    $fetch_order = $mysqli->prepare("SELECT prod_id, prod_qty FROM rpos_tableorders WHERE order_id = ?");
    $fetch_order->bind_param('i', $order_id);
    $fetch_order->execute();
    $result = $fetch_order->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_object();
        $prod_id = $order->prod_id;
        $prod_qty = $order->prod_qty;

        // Restore product stock
        $restore_stock = $mysqli->prepare("UPDATE rpos_inventory SET stock_qty = stock_qty + ? WHERE prod_id = ?");
        $restore_stock->bind_param('ii', $prod_qty, $prod_id);
        $restore_stock->execute();

        // Delete order
        $delete_order = $mysqli->prepare("DELETE FROM rpos_tableorders WHERE order_id = ?");
        $delete_order->bind_param('i', $order_id);
        $delete_order->execute();

        // Set session message
        $_SESSION['cancel_success'] = true;
    }
}

header("Location: update_tableorders.php?table_id=$table_id");
exit();
