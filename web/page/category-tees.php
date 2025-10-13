<?php
require '../_base.php';

$_title = 'Home | EZ';
include '../_head.php';
?>

<link rel="stylesheet" href="../css/index.css">

<?php
include '../_nav.php';
?>

<!-- Database connection -->
<?php
include '../config.php';


try {
    // Fetch products from database
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Prepare the SQL query using PDO
    $sql = "SELECT P.product_id, P.name, P.price, P.color, P.img_front, P.img_back 
            FROM product P 
            WHERE P.cat_id = 'CAT7'";

    // Prepare the statement
    $stmt = $pdo->prepare($sql);

    // Execute the query
    $stmt->execute();

    // Fetch all results as an associative array
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle database errors
    die("Database error: " . $e->getMessage());
}
?>

<!-- Start coding here -->

<div class="main-content-container">
    <!-- Left navbar Section -->
    <div class="side-navbar">
        <p>SUMMER 2025</p>
        <ul>
            <li><a href="../index.php">NEW ARRIVALS</a></li>
            <li style="background: #9db4c0; font-weight: 600; color: #253237; border-radius: 5px;"><a href="../page/category-tees.php">TEES</a></li>
            <li><a href="../page/category-casual-shirts.php">CASUAL SHIRTS</a></li>
            <li><a href="../page/category-sweater&hoodie.php">SWEATER & HOODIE</a></li>
            <li><a href="../page/category-pants.php">PANTS</a></li>
            <li><a href="../page/category-shorts.php">SHORTS</a></li>
            <li><a href="../page/category-skirts.php">SKIRTS</a></li>

        </ul>
    </div>

    <div class="product-wrapper">
        <!-- Search Bar -->
        <div class="search-container">
            <form method="GET" action="">
                <input type="text" id="search" name="search" placeholder="Search for products..." value="<?= htmlspecialchars($search) ?>">
            </form>
        </div>
        <!-- Product Section -->
        <div class="product-container">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product">
                        <a href="../page/product_page.php?product_id=<?= $product['product_id'] ?>">
                            <div class="product-img-container">
                                <img src="../<?= htmlspecialchars($product['img_front']) ?>" class="front" alt="<?= htmlspecialchars($product['name']) ?> Front">
                                <img src="../<?= htmlspecialchars($product['img_back']) ?>" class="back" alt="<?= htmlspecialchars($product['name']) ?> Back">
                            </div>
                            <div class="product-info">
                                <h2><?= htmlspecialchars($product['name']) ?></h2>
                                <p><b><?= number_format($product['price'], 2) ?> MYR </b> | <?= htmlspecialchars($product['color']) ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products">
                    <img src="../images/no-product-found.svg" alt="No Products">
                    <p>No products found. Find other categories.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
include '../_foot.php';
