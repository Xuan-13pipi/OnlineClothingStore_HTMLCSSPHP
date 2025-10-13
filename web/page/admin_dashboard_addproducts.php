<?php
require_once '../_base.php';
$_title = 'Admin Dashboard | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/admin_dashboard_addproducts.css">
</head>

<!-- This code prevents users getting into admin page by changing the URL -->
<?php
// Database Connection
require '../config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect if not logged in
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch user details using PDO
$query = "SELECT is_admin FROM member WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['is_admin'] != 1) {
    header('Location: ../index.php'); // Redirect if not admin
    exit;
}
?>

<?php include '../lib/submit_product.php'; ?>

<body>
    <header>
        <a href="/"><img src="../images/Logo.png" alt="Logo" class="logo"></a>
        <nav>
        <span> <a class="admin-dashboard-btn" href="../page/admin_dashboard.php">Admin Dashboard</a></span>
            <a href="../page/profile_page.php"><img src="../images/images_nav/user.svg" alt="User" height="28" width="28"></a>
        </nav>
    </header>

    <main>
        <!-- Sidenav Bar here -->
        <nav class="left-sidenav">
            <ul>
                <li><a href="../page/admin_dashboard.php">Dashboard</a></li>
                <li><a class="active" href="../page/admin_dashboard_products.php">Products</a></li>
                <li><a href="../page/admin_dashboard_user.php">Users</a></li>
                <li><a href="../page/admin_dashboard_orderdetail.php">Order Detail</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Add Product</h1>
            <form id="product-form" action="../lib/submit_product.php" method="POST" enctype="multipart/form-data">
                <!-- Product Name -->
                <div class="form-group">
                    <label for="product-name">Product Name</label>
                    <input type="text" id="product-name" name="product-name" placeholder="Insert name here..." required>
                </div>

                <!-- Product Price -->
                <div class="form-group">
                    <label for="product-price">Product Price</label>
                    <input type="number" id="product-price" name="product-price" step="0.1" placeholder="Eg. 150.00" required>
                </div>

                <!-- Product Color -->
                <div class="form-group">
                    <label for="product-color">Product Color</label>
                    <input type="text" id="product-color" name="product-color" placeholder="Eg. Green" required>
                </div>

                <!-- Product Category -->
                <div class="form-group">
                    <label for="product-category">Product Category</label>
                    <select id="product-category" name="product-category" required>
                        <option value="">Select Category</option>
                        <option value="CAT1">New Arrivals</option>
                        <option value="CAT2">Casual Shirts</option>
                        <option value="CAT3">Pants</option>
                        <option value="CAT4">Shorts</option>
                        <option value="CAT5">Skirts</option>
                        <option value="CAT6">Sweater & Hoodies</option>
                        <option value="CAT7">Tees</option>
                    </select>
                </div>

                <!-- Image Upload -->
                <div class="img-upload-container">
                    <div class="img-upload">
                        <h3>Front Image</h3>
                        <input type="file" id="front-image" name="front-image" accept="image/*" required>
                        <img id="front-preview" src="#" alt="Front Image Preview" style="display:none;">
                    </div>

                    <div class="img-upload">
                        <h3>Back Image</h3>
                        <input type="file" id="back-image" name="back-image" accept="image/*" required>
                        <img id="back-preview" src="#" alt="Back Image Preview" style="display:none;">
                    </div>
                </div>

                <!-- Submit Button -->
                <button class="add-product" type="submit">Add Product</button>
            </form>
        </div>
    </main>
    <script src="../js/addproducts.js"></script>
</body>
</html>