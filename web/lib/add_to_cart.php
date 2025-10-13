<?php
require_once '../_base.php';
$_title = 'Login | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/product_page.css">
<?php
include '../_nav.php';
require '../config.php'; // connect to database

// 1. Debug: Dump all submitted form data / display data
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Start session and check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first");
    exit();
}

// Validate product ID
if (!isset($_POST['product_id'])) {
    header("Location: productpage.php?error=Invalid product");
    exit();
}

// Validate other inputs    
$product_id = $_POST['product_id'];
$user_id = $_SESSION['user_id'];
$size = $_POST['size'];
$color = $_POST['color'];
$quantity = $_POST['quantity'];

// Check if the same product with same size already exists in cart
$checkStmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND size = ?");
$checkStmt->execute([$user_id, $product_id, $size]);
$existingItem = $checkStmt->fetch();

if ($existingItem) {
    // If item exists, update the quantity
    $newQuantity = $existingItem['quantity'] + $quantity;
    $updateStmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
    $updateStmt->execute([$newQuantity, $existingItem['cart_id']]);
} else {
    // If item doesn't exist, insert new record
    $stmt = $pdo->prepare("INSERT INTO cart 
                                 (quantity, size, color, user_id, product_id) 
                                 VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$quantity, $size, $color, $user_id, $product_id]);
}

if (!isset($_POST['quantity'])) {
    die("Error: Quantity not received!");
}

$quantity = (int)$_POST['quantity'];
echo "Quantity received: " . $quantity . "<br>";

if ($quantity < 1) {
    die("Error: Quantity must be at least 1.");
}

temp('success', "Product succesfully added to cart.");
header("Location: /page/product_page.php?product_id=$product_id");
exit();
?>
<?php
include '../_foot.php';
?>