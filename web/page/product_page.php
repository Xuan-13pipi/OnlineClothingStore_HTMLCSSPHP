<!DOCTYPE html>
<?php
require '../_base.php';
// Database connection
include '../config.php';

// Fetch product details based on product_id
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    try {
        // Prepare the SQL statement
        $sql = "SELECT product_id, name, price, color, img_front, img_back FROM product WHERE product_id = ?";
        $stmt = $pdo->prepare($sql);
        // Execute the statement
        $stmt->execute([$product_id]);
        // Fetch the result
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Product found, you can now use $product array
        } else {
            die("Product not found.");
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    die("Product ID not specified.");
}
$_title = $product['name'] . ' | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/product_page.css">
<?php
include '../_nav.php';
?>
<!-- Start structure here -->
<?php
// Flash messages
$flash_success = temp('success');
$flash_error = temp('error');
?>
<?php if (!empty($flash_success) || !empty($flash_error)): ?>
    <div id="flash-messages">
        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($flash_success); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($flash_error); ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<div class="product-wrapper">
    <div class="side-navbar">
        <p>SUMMER 2025</p>
        <ul>
            <li><a href="../index.php">NEW ARRIVALS</a></li>
            <li><a href="../page/category-tees.php">TEES</a></li>
            <li><a href="../page/category-casual-shirts.php">CASUAL SHIRTS</a></li>
            <li><a href="../page/category-sweater&hoodie.php">SWEATER & HOODIE</a></li>
            <li><a href="../page/category-pants.php">PANTS</a></li>
            <li><a href="../page/category-shorts.php">SHORTS</a></li>
        </ul>
    </div>

    <div class="product-img">
        <img src="<?php echo $product['img_front']; ?>" alt="Shirt">
        <img src="<?php echo $product['img_back']; ?>" alt="Shirt">
    </div>

    <aside class="product-info">
        <div class="name"><?php echo $product['name']; ?></div>
        <div class="price"><?php echo $product['price']; ?> MYR</div>
        <div class="line"></div>
        <div class="color-btn-container">
            <?php echo '<button type="button" class="color-btn" style="background-color: ' . $product['color'] . ';" data-color="' . $product['color'] . '"></button>'; ?>
        </div>
        <div class="sword">Color: <span id="selected-color-text"><?php echo $product['color']; ?></span></div>
        <div class="size-btn-container">
            <button class="size-btn" data-size="S">S</button>
            <button class="size-btn" data-size="M">M</button>
            <button class="size-btn" data-size="L">L</button>
            <button class="size-btn" data-size="XL">XL</button>
        </div>

        <!-- Size Guide Link -->
        <div class="size-guide-link">
            <a href="#" id="size-guide-link">Size Guide</a>
        </div>

        <!-- Size Guide Modal -->
        <div id="size-guide-modal" class="modal">
            <div class="modal-content">
                <!-- Close Button with Image -->
                <span class="close">
                    <img src="../images/close.svg" alt="Close">
                </span>
                <img src="../images/saizGuide.png" alt="Size Guide">
            </div>
        </div>


        <!-- Form data submit here -->
        <form action="../lib/add_to_cart.php" method="POST">
            <div class="quantity-input">
                <button type="button" class="quantity-btn minus">-</button>
                <input type="number" class="quantity" name="quantity" value="1" min="1"  max="10">
                <button type="button" class="quantity-btn plus">+</button>
            </div>
            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
            <input type="hidden" name="size" id="selected-size" value="">
            <input type="hidden" name="color" id="selected-color" value="<?php echo $product['color']; ?>">
            <div class="addtocart-container">
                <button type="submit" class="addtocart">Add to Cart</button>
            </div>
        </form>


        <div class="dropdown">
            <button class="dropdown-toggle">Details</button>
            <div class="dropdown-content">
                <ul>
                    <li>100% Cotton</li>
                    <li>Regular</li>
                    <li>Loose Fit</li>
                    <li>Slightly Stretchy</li>
                </ul>
            </div>
        </div>
        <div class="dropdown">
            <button class="dropdown-toggle">Shopping</button>
            <div class="dropdown-content">
                <ol>
                    <li>All products are photographed as they are, and we strive to ensure that the colors in our photos match the actual items as closely as possible. However, there may be slight variations in color and size due to photography or computer screen settings. Please refer to the actual product for accurate color and size.</li><br>
                    <li>Before shipping, each item is carefully packed by a dedicated department to ensure there are no quality issues. However, we cannot guarantee perfection. If you find any inherent defects in the product, please contact us within 48 hours of receipt, and we will resolve the issue promptly fo you.</li>
                </ol>
            </div>
        </div>
        <div class="dropdown">
            <button class="dropdown-toggle">Return</button>
            <div class="dropdown-content">
                <ol>
                    <li>From the date of package receipt, you can enjoy a seven-day no-reason return or exchange service at our store. This service is applicable only if the product does not affect its resale value, remains clean with no obvious signs of wear, and is in its original packaging. The shipping costs for returns or exchanges shall be borne by the buyer.</li><br>
                    <li>During the return or exchange mailing process, our company does not accept cash on delivery (COD) or standard postal packages. If a return is made autonomously without prior communication, we will refuse to sign for it.</li><br>
                    <li>After-sales service is one of our fundamental services, and we are committed to providing excellent after-sales support to our customers. However, good after-sales service also requires customer cooperation. Please carefully read the above instructions before proceeding with a return or exchange and follow our company's policies to ensure the quality of our after-sales service is not compromised.</li>
                </ol>
            </div>
        </div>
    </aside>
</div>
<script src="../js/productpage.js"></script>

<?php
include '../_foot.php';
