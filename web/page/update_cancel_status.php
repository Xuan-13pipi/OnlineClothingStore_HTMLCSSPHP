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

try {
    // Verify order belongs to user
    $stmt = $pdo->prepare("SELECT user_id FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order || $order['user_id'] != $user_id) {
        header('Location: profile_history.php');
        exit();
    }

    // Update order status
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt->execute([$status, $order_id]);
    
    // Redirect back to order details
    header("Location: history_detail.php?order_id=$order_id");
    exit();
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("System error, please try again later");
}