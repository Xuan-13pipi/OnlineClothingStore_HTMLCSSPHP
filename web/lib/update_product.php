<?php
require '../_base.php';
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Standardize to use underscores consistently
    $productId = $_POST['product_id'];  // Changed from 'product-id'
    $productName = $_POST['product_name'];  // Consider changing your form fields too
    $productPrice = $_POST['product_price'];
    $productColor = $_POST['product_color'];
    $categoryId = $_POST['product_category'];

    // Use PDO prepared statements
    $sql = "UPDATE product 
            SET name = :product_name, 
                price = :product_price, 
                color = :product_color, 
                cat_id = :category_id 
            WHERE product_id = :product_id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':product_name', $productName, PDO::PARAM_STR);
    $stmt->bindParam(':product_price', $productPrice, PDO::PARAM_STR);
    $stmt->bindParam(':product_color', $productColor, PDO::PARAM_STR);
    $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_STR);
    $stmt->bindParam(':product_id', $productId, PDO::PARAM_STR);

    if ($productPrice < 1) {
        die("Price must be at least 1.00");
    }

    if ($stmt->execute()) {
        temp('success', "Product updated successfully!");
        header("Location: ../page/admin_dashboard_products.php");
        exit();
    } else {
        temp('error', "Error updating product: " . implode(" ", $stmt->errorInfo()));
        header("Location: ../page/admin_dashboard_products.php");
        exit();
    }
}
?>