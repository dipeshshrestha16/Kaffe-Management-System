<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

header('Content-Type: application/json');

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $mysqli->prepare("DELETE FROM rpos_staff WHERE staff_id = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'invalid']);
}
