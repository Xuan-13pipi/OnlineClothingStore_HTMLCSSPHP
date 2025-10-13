<?php
require '../_base.php';

$_title = 'Order Details | EZ';
include '../_head.php';
include '../_nav.php';

?>
<link rel="stylesheet" href="../css/order_details.css">

<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require '../config.php';

if (!isset($_GET['order_id'])) {
    header('Location: profile_history.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'];

try {
    // Get order header info and all products
    $order_sql = "SELECT 
                    o.order_id,
                    o.created_at,
                    o.order_status,
                    o.total_amount,
                    o.payment_method,
                    m.hp,
                    m.email, 
                    m.address,
                    p.product_id,
                    p.name AS product_name,
                    p.img_front,
                    od.quantity,
                    od.price AS unit_price,
                    (od.quantity * od.price) AS subtotal
                FROM orders o
                JOIN member m ON o.user_id = m.user_id
                JOIN order_details od ON o.order_id = od.order_id
                JOIN product p ON od.product_id = p.product_id
                WHERE o.order_id = :order_id 
                AND o.user_id = :user_id
                ORDER BY p.name";

    $stmt = $pdo->prepare($order_sql);
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all products for this order
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($order_items)) {
        header('Location: profile_history.php');
        exit();
    }

    // Get order detail info from first item
    $order_detail = $order_items[0];
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("System error, Please try again");
}
$subtotal = $order_detail['total_amount'];

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

?>
</head>

<body>
    <main>
        <!-- left-sidenav -->
        <nav class="left-sidenav">
            <ul>
                <li><a href="../page/profile_page.php">User</a></li>
                <li><a class="active" href="../page/profile_history.php">History</a></li>
            </ul>
        </nav>

        <div class="main-content">
            <div class="back-link">
                <a href="profile_history.php">← Return to order list</a>
            </div>

            <h1>Order details #<?= htmlspecialchars($order_detail['order_id']) ?></h1>

            <div class="order-details-container">
                <div class="order-meta">
                    <div class="meta-section">
                        <h2>Order Information</h2>
                        <div class="meta-item">
                            <strong>Order ID:</strong> <?= htmlspecialchars($order_detail['order_id']) ?>
                        </div>
                        <div class="meta-item">
                            <strong>Order Date:</strong> <?= htmlspecialchars($order_detail['created_at']) ?>
                        </div>
                        <div class="meta-item">
                            <strong>Hp Number (+60):</strong> <?= htmlspecialchars($order_detail['hp']) ?>
                        </div>
                        <div class="meta-item">
                            <strong>Address:</strong> <?= htmlspecialchars($order_detail['address']) ?>
                        </div>
                        <div class="meta-item">
                            <strong>Email:</strong> <?= htmlspecialchars($order_detail['email']) ?>
                        </div>
                        <div class="meta-item">
                            <strong>Order Status:</strong>
                            <span class="status-badge status-<?= strtolower($order_detail['order_status']) ?>">
                                <?= htmlspecialchars($order_detail['order_status']) ?>
                            </span>
                        </div>
                        <div class="meta-item">
                            <strong>Payment Method:</strong> <?= htmlspecialchars($order_detail['payment_method']) ?>
                        </div>
                        
                        <?php if ($order_detail['order_status'] === 'Pending'): ?>
                        <div class="action-buttons mt-3">
                            <form method="POST" action="update_cancel_status.php" style="display: inline-block;">
                                <input type="hidden" name="order_id" value="<?= $order_detail['order_id'] ?>">
                                <input type="hidden" name="status" value="Cancelled">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Cancel Order
                                </button>
                            </form>
    
                            <form method="POST" action="update_paid_status.php" style="display: inline-block; margin-left: 10px;">
                                <input type="hidden" name="order_id" value="<?= $order_detail['order_id'] ?>">
                                <input type="hidden" name="status" value="Paid">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Pay Now
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="order-items">
                        <h2>Product Details</h2>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>PRODUCT PICTURES</th>
                                    <th>PRODUCT NAME</th>
                                    <th>QUANTITY</th>
                                    <th>UNIT PRICE</th>
                                    <th>SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td class="product-sell">
                                            <?php if (!empty($item['img_front'])): ?>
                                                <img src="../<?= htmlspecialchars($item['img_front']) ?>"
                                                    alt="<?= htmlspecialchars($item['product_name']) ?>"
                                                    class="product-thumbnail">
                                            <?php else: ?>
                                                <div class="no-image">no image</div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                                        <td><?= number_format($item['unit_price'], 2) ?> MYR</td>
                                        <td><?= number_format($item['subtotal'], 2) ?> MYR</td>
                                    </tr>
                                <?php endforeach; ?>

                                <tr class="total-row">
                                    <td colspan="4" class="text-right">Shipping Fees:</td>
                                    <td><?= number_format($shipping_fee, 2) ?>MYR</td>
                                </tr>

                                <tr class="total-row">
                                    <td colspan="4" class="text-right"><strong>Order Total:</strong></td>
                                    <td><b><?= number_format($order_detail['total_amount'], 2) ?> MYR</b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

</body>

</html>