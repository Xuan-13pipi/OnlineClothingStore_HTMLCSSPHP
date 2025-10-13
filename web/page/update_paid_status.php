<?php
require_once '../_base.php';
require '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if required parameters are present
if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    header('Location: profile_history.php');
    exit();
}

$order_id = $_POST['order_id'];
$status = $_POST['status'];
$user_id = $_SESSION['user_id'];
    
// Redirect back to order details
header("Location: /page/payment.php?order_id=$order_id");
exit();