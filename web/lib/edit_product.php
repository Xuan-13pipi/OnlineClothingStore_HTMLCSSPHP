    <?php
    error_reporting(E_ALL); // Report all PHP errors
    ini_set('display_errors', 1); // Display errors to the browser

    require '../_base.php';

    $_title = 'Admin Dashboard | EZ';
    include '../_head.php';
    ?>
    <link rel="stylesheet" href="../css/admin_dashboard_addproducts.css">
    </head>

    <!-- Database connection -->
    <?php include '../config.php';

    // Get the product_id from the URL
    if (!isset($_GET['product_id'])) {
        die("Product ID is missing.");
    }

    $productId = $_GET['product_id'];
    // Fetch product details from the database using PDO
    try {
        $stmt = $pdo->prepare("SELECT * FROM product WHERE product_id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            die("Product not found in database");
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }

    // Handle photo uploads
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_FILES['product_photo_front']) || isset($_FILES['product_photo_back']))) {
        $uploadDir = '../images/products/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Function to handle single photo upload
        function handlePhotoUpload($file, $fieldName, $currentPath, $productId, $pdo)
        {
            global $uploadDir, $allowedTypes, $maxFileSize;

            if ($file['error'] !== UPLOAD_ERR_OK) {
                die("File upload error: " . $file['error']);
            }

            if ($file['size'] > $maxFileSize) {
                die("File size exceeds 5MB limit");
            }

            if (!in_array($file['type'], $allowedTypes)) {
                die("Only JPG, PNG, and GIF files are allowed");
            }

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . $fieldName . '.' . $extension;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                try {
                    $column = ($fieldName === 'front') ? 'img_front' : 'img_back';
                    $stmt = $pdo->prepare("UPDATE product SET $column = ? WHERE product_id = ?");
                    $stmt->execute([$destination, $productId]);

                    // Delete old photo if it exists
                    if (!empty($currentPath) && file_exists($currentPath)) {
                        unlink($currentPath);
                    }

                    return $destination;
                } catch (PDOException $e) {
                    die("Database error: " . $e->getMessage());
                }
            } else {
                die("Failed to move uploaded file");
            }
        }

        // Process front photo if uploaded
        if (isset($_FILES['product_photo_front']) && $_FILES['product_photo_front']['error'] !== UPLOAD_ERR_NO_FILE) {
            $newFrontPath = handlePhotoUpload($_FILES['product_photo_front'], 'front', $product['img_front'], $productId, $pdo);
            $product['img_front'] = $newFrontPath;
            echo "<script>alert('Front product photo updated successfully!');</script>";
        }

        // Process back photo if uploaded
        if (isset($_FILES['product_photo_back']) && $_FILES['product_photo_back']['error'] !== UPLOAD_ERR_NO_FILE) {
            $newBackPath = handlePhotoUpload($_FILES['product_photo_back'], 'back', $product['img_back'], $productId, $pdo);
            $product['img_back'] = $newBackPath;
            echo "<script>alert('Back product photo updated successfully!');</script>";
        }
    }
    ?>

    <body>
        <header>
            <a href="/"><img src="../images/Logo.png" alt="Logo" class="logo"></a>
            <nav>
                <span>Admin Dashboard</span>
                <a href="../page/demo1.php"><img src="../images/images_nav/user.svg" alt="User" height="28" width="28"></a>
            </nav>
        </header>

        <main>
            <div class="main-content">
                <h1>Edit Product</h1>

                <!-- Current Product Photos -->
                <div class="form-group">
                    <label>Current Product Photos</label>
                    <div class="product-photos-container" style="display: flex; gap: 20px; margin-top: 10px;">
                        <div class="product-photo-preview" style="flex: 1;">
                            <div style="font-weight: bold; margin-bottom: 5px;">Front Photo</div>
                            <img src="<?= htmlspecialchars($product['img_front']) ?>" alt="Front Product Photo" style="max-width: 100%; max-height: 200px; border: 1px solid #ddd; padding: 5px;">
                        </div>
                        <div class="product-photo-preview" style="flex: 1;">
                            <div style="font-weight: bold; margin-bottom: 5px;">Back Photo</div>
                            <img src="<?= htmlspecialchars($product['img_back']) ?>" alt="Back Product Photo" style="max-width: 100%; max-height: 200px; border: 1px solid #ddd; padding: 5px;">
                        </div>
                    </div>
                </div>

                <!-- Photo Update Form -->
                <form id="updatePhotoForm" method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
                    <div style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="product-photo-front">Update Front Photo</label>
                            <input type="file" id="product-photo-front" name="product_photo_front" accept="image/jpeg, image/png, image/gif" style="width: 100%;">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="product-photo-back">Update Back Photo</label>
                            <input type="file" id="product-photo-back" name="product_photo_back" accept="image/jpeg, image/png, image/gif" style="width: 100%;">
                        </div>
                    </div>
                    <button type="submit" class="save" style="margin-top: 10px;">Update Photos</button>
                </form>

                <!-- Product Details Form -->
                <form id="editProductForm" action="../lib/update_product.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                    <div class="form-group">
                        <label for="product-name">Product Name</label>
                        <input type="text" id="product-name" name="product_name" value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="product-price">Product Price</label>
                        <input type="number" id="product-price" name="product_price" value="<?= htmlspecialchars($product['price']) ?>" min="1" step="0.01" required>
                        <small>Price must be at least 1.00</small>
                    </div>

                    <div class="form-group">
                        <label for="product-color">Product Color</label>
                        <input type="text" id="product-color" name="product_color" value="<?= htmlspecialchars($product['color']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="product-category">Product Category</label>
                        <select id="product-category" name="product_category" required>
                            <option value="CAT1" <?= $product['cat_id'] === 'CAT1' ? 'selected' : '' ?>>New Arrivals</option>
                            <option value="CAT2" <?= $product['cat_id'] === 'CAT2' ? 'selected' : '' ?>>Casual Shirts</option>
                            <option value="CAT3" <?= $product['cat_id'] === 'CAT3' ? 'selected' : '' ?>>Pants</option>
                            <option value="CAT4" <?= $product['cat_id'] === 'CAT4' ? 'selected' : '' ?>>Shorts</option>
                            <option value="CAT5" <?= $product['cat_id'] === 'CAT5' ? 'selected' : '' ?>>Skirts</option>
                            <option value="CAT6" <?= $product['cat_id'] === 'CAT6' ? 'selected' : '' ?>>Sweater & Hoodies</option>
                            <option value="CAT7" <?= $product['cat_id'] === 'CAT7' ? 'selected' : '' ?>>Tees</option>
                        </select>
                    </div>

                    <button type="submit" class="save">Save Changes</button>
                </form>

                <form id="removeProductForm" action="../lib/remove_product.php" method="POST" onsubmit="return confirm('Are you sure you want to remove this product?');">
                    <input type="hidden" name="product-id" value="<?= $product['product_id'] ?>">
                    <button type="submit" class="danger">Remove Product</button>
                </form>
            </div>
        </main>

        <script>
            function validatePrice() {
                const priceInput = document.getElementById('product-price');
                const price = parseFloat(priceInput.value);

                if (price < 1) {
                    alert('Price must be at least 1.00');
                    priceInput.focus();
                    return false;
                }

                return true;
            }
        </script>

    </body>

    </html>