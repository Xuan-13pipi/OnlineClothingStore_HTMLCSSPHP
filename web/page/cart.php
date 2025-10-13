<?php
require_once '../_base.php';
require '../config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get cart items with optional search
$select_fields = "c.cart_id, c.quantity, c.size, c.color, 
                 c.user_id, c.product_id,
                 p.name, p.price, 
                 p.img_front, p.img_back"; // Using the actual columns from your tables

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT $select_fields
        FROM cart c
        JOIN product p ON c.product_id = p.product_id
        WHERE c.user_id = :user_id";

if (!empty($search)) {
    $sql .= " AND p.name LIKE :search";
}

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
}

$stmt->execute();
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle item removal
if (isset($_GET['remove_id'])) {
    $cart_id = $_GET['remove_id'];

    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);

    $_SESSION['success'] = 'Item removed from cart';
    header("Location: cart.php");
    exit();
}

// Handle quantity updates
if (isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $new_quantity = (int)$_POST['quantity'];

    // Validate quantity range
    if ($new_quantity < 1 || $new_quantity > 10) {
        $_SESSION['error'] = 'Quantity must be between 1 and 10';
        header("Location: cart.php");
        exit();
    }

    if ($new_quantity > 0) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$new_quantity, $cart_id, $user_id]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);
    }

    $_SESSION['success'] = 'Cart updated';
    header("Location: cart.php");
    exit();
}

// Handle order placement (moved from check-out.php)
if (isset($_POST['place_order'])) {
    if (empty($cart_items)) {
        $_SESSION['error'] = 'Your cart is empty';
        header("Location: cart.php");
        exit();
    }

    // Process the order
    try {
        $pdo->beginTransaction();

        // 1. Create order record
        $stmt = $pdo->prepare("
            INSERT INTO orders 
            (user_id, total_amount, order_status, payment_method, created_at)
            VALUES (?, ?, 'Pending', 'Not Specified', NOW())
        ");

        // Calculate subtotal from cart items
        $stmt_cart = $pdo->prepare("
            SELECT SUM(p.price * c.quantity) as subtotal
            FROM cart c
            JOIN product p ON c.product_id = p.product_id
            WHERE c.user_id = ?
        ");
        $stmt_cart->execute([$user_id]);
        $subtotal = $stmt_cart->fetchColumn();

        // Calculate shipping fees (your existing logic)
        if ($subtotal < 50) {
            $shipping_fee = 6;
        } elseif ($subtotal < 100) {
            $shipping_fee = 10;
        } elseif ($subtotal < 150) {
            $shipping_fee = 14;
        } else {
            $shipping_fee = 0;
        }

        $grand_total = $subtotal + $shipping_fee;

        // Insert into orders (with grand total)
        $stmt = $pdo->prepare("
            INSERT INTO orders 
            (user_id, total_amount, order_status, payment_method, created_at)
            VALUES (?, ?, 'Pending', 'Not Specified', NOW())
        ");
        $stmt->execute([$user_id, $grand_total]);  // Inserting the grand total here

        $order_id = $pdo->lastInsertId();

        // 2. Move cart items to order_details
        $stmt_items = $pdo->prepare("
            INSERT INTO order_details 
            (order_id, product_id, quantity, size, price)
            SELECT ?, c.product_id, c.quantity, c.size, p.price
            FROM cart c
            JOIN product p ON c.product_id = p.product_id
            WHERE c.user_id = ?
        ");
        $stmt_items->execute([$order_id, $user_id]);

        // 3. Clear the cart
        $stmt_clear = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt_clear->execute([$user_id]);

        $pdo->commit();

        // Redirect to payment page
        header("Location: payment.php?order_id=$order_id");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Order processing failed: " . $e->getMessage();
        header("Location: cart.php");
        exit();
    }
}

// First check what columns exist in the product table
$product_columns = [];
try {
    $stmt = $pdo->query("DESCRIBE products");
    $product_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    try {
        $stmt = $pdo->query("DESCRIBE product");
        $product_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        die("Could not find products table");
    }
}

// Build the select fields dynamically based on available columns
$select_fields = "c.cart_id, c.quantity, c.size, c.color, p.product_id, p.name, p.price";
if (in_array('image', $product_columns)) {
    $select_fields .= ", p.image";
}

// Calculate total and total quantity
$total = 0;
$total_quantity = 0;
foreach ($cart_items as $item) {
    if (isset($item['price']) && isset($item['quantity'])) {
        $total += $item['price'] * $item['quantity'];
        $total_quantity += $item['quantity'];
    }
}

// Calculate shipping fees based on total
if ($total < 50) {
    $shipping_fee = 6;
} elseif ($total < 100) {
    $shipping_fee = 10;
} elseif ($total < 150) {
    $shipping_fee = 14;
} else {
    $shipping_fee = 0;
}

$grand_total = $total + $shipping_fee;

$_title = 'Your Cart | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="/css/cart.css">
<?php
include '../_nav.php';
?>

<div class="container">
    <h1>Your Shopping Cart</h1>

    <div class="search-container">
        <form method="GET" action="">
            <input type="text" id="search" name="search" placeholder="Search for products..." value="<?= htmlspecialchars($search) ?>">
        </form>
    </div>

    <?php
    // Flash messages
    $flash_success = temp('success');
    $flash_error = temp('error');
    ?>
    <?php if (!empty($flash_success) || !empty($flash_error)): ?>
        <div id="flash-messages">
            <?php if (!empty($flash_success)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($flash_success) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($flash_error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($flash_error) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty</p>
        <a href="../index.php" class="btn btn-primary">Continue Shopping</a>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Size</th>
                    <th>Color</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <?php if (!empty($item['image_front'])): ?>
                                <img src="../images/<?= htmlspecialchars($item['image']) ?>" width="50" height="50">
                            <?php endif; ?>
                            <?= htmlspecialchars($item['name'] ?? 'Product') ?>
                        </td>
                        <td><?= htmlspecialchars($item['size'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($item['color'] ?? 'N/A') ?></td>
                        <td><?= number_format($item['price'] ?? 0, 2) ?>MYR</td>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="10" class="form-control" style="width: 70px;">
                                <button type="submit" name="update_quantity" class="btn btn-sm btn-info mt-1">Update</button>
                            </form>
                        </td>
                        <td><?= number_format(($item['price'] ?? 0) * $item['quantity'], 2) ?>MYR</td>
                        <td>
                            <a href="cart.php?remove_id=<?= $item['cart_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this item?')">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
    <tr>
        <td colspan="7" style="padding: 0;">
            <div class="order-summary">
                <div class="summary-row">
                    <div class="summary-label">Total Quantity:</div>
                    <div class="summary-value"><?= $total_quantity ?></div>
                </div>
                <div class="summary-row subtotal">
                    <div class="summary-label">Total:</div>
                    <div class="summary-value"><?= number_format($total, 2) ?>MYR</div>
                </div>
                <div class="summary-row shipping">
                    <div class="summary-label">Shipping Fee:</div>
                    <div class="summary-value"><?= number_format($shipping_fee, 2) ?>MYR</div>
                </div>
                <div class="summary-row grand-total">
                    <div class="summary-label">Grand Total:</div>
                    <div class="summary-value"><?= number_format($grand_total, 2) ?>MYR</div>
                </div>
            </div>
        </td>
    </tr>
</tfoot>  
        </table>

        <div class="text-right">
            <a href="../index.php" class="btn btn-primary">Continue Shopping</a>
            <form method="post" style="display: inline-block;">
                <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include '../_foot.php'; ?>