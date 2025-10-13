<?php
require '../_base.php';
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productId = $_POST['product-id'];

    // Fetch product images using PDO
    $sql = "SELECT img_front, img_back FROM product WHERE product_id = :product_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':product_id', $productId, PDO::PARAM_STR);
    $stmt->execute();

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $front_image_path = $product['img_front']; // Front image path
        $back_image_path = $product['img_back'];   // Back image path

        // Delete the product from the database
        $deleteSQL = "DELETE FROM product WHERE product_id = :product_id";
        $deleteStmt = $pdo->prepare($deleteSQL);
        $deleteStmt->bindParam(':product_id', $productId, PDO::PARAM_STR);

        if ($deleteStmt->execute()) {
            // Delete image files from the server
            if (file_exists($front_image_path)) {
                unlink($front_image_path); // Delete the front image
            }

            if (file_exists($back_image_path)) {
                unlink($back_image_path); // Delete the back image
            }
            temp('success', "Product deleted successfully!");
        header("Location: ../page/admin_dashboard_products.php");
        exit();
        } else {
            temp('error', "Error deleting product.");
        header("Location: ../page/admin_dashboard_products.php");
        exit();
        }
    } else {
        temp('error', "Product not found.");
        header("Location: ../page/admin_dashboard_products.php");
        exit();
    }
    
}
?>
