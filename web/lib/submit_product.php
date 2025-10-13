<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../_base.php';
include '../config.php';
// Assosiative array of category_id to folder names
$categoryMapping = [
    'CAT1' => 'new-arrivals',
    'CAT2' => 'casual-shirts',
    'CAT3' => 'pants',
    'CAT4' => 'shorts',
    'CAT5' => 'skirts',
    'CAT6' => 'sweaterNhoodie',
    'CAT7' => 'tees',
];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productName = $_POST['product-name'];
    $productPrice = $_POST['product-price'];
    $productColor = $_POST['product-color'];
    $categoryId = $_POST['product-category'];

    // Generates a unique product id
    do {
        $productId = 'P' . str_pad(mt_rand(1, 999), 4, '0', STR_PAD_LEFT);
    } while (!isIdUnique($productId, $pdo, 'product', 'product_id'));

    // Get the folder name from the array
    $categoryPath = $categoryMapping[$catisegoryId];

    // Handle file uploads
    $uploadDir = "../images/clothes/" . $categoryPath . "/"; // Use mapped folder name
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create folder if it doesn't exist
    }

    // Generate unique file names for the images
    $frontImageName = $productName . " " . $productColor . "_front.png";
    $backImageName = $productName . " " . $productColor . "_back.png";

    $frontImagePath = $uploadDir . $frontImageName;
    $backImagePath = $uploadDir . $backImageName;

    // Move uploaded files
    if (move_uploaded_file($_FILES['front-image']['tmp_name'], $frontImagePath) &&
        move_uploaded_file($_FILES['back-image']['tmp_name'], $backImagePath)) {
        
        // Insert into `product` table using PDO
        $insertProduct = "INSERT INTO product (product_id, name, color, price, img_front, img_back, cat_id) 
                          VALUES (:product_id, :name, :color, :price, :img_front, :img_back, :cat_id)";
        
        $stmt = $pdo->prepare($insertProduct);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':name', $productName);
        $stmt->bindParam(':color', $productColor);
        $stmt->bindParam(':price', $productPrice, PDO::PARAM_INT);
        $stmt->bindParam(':img_front', $frontImagePath);
        $stmt->bindParam(':img_back', $backImagePath);
        $stmt->bindParam(':cat_id', $categoryId);

        if ($stmt->execute()) {
            temp('success', "Product added successfully!");
        header("Location: ../page/admin_dashboard_products.php");
        exit();
        } else {
            die("Error inserting product: " . implode(" ", $stmt->errorInfo()));
        }
    } else {
        echo "Error uploading files.";
    }
}
?>
