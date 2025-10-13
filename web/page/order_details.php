<?php
require '../_base.php';

$_title = 'Order Details | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/order_details.css">

<?php
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
// Get user permissions
try {
    $query = "SELECT is_admin FROM member WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If  not an administrator, redirect to the home page
    if (!$user || $user['is_admin'] != 1) {
        header('Location: ../index.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_orderdetail.php');
    exit;
}

$order_id = $_GET['id'];

// Process order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['new_status'];

    try {
        $update_sql = "UPDATE orders SET order_status = :new_status WHERE order_id = :order_id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->bindParam(':new_status', $new_status, PDO::PARAM_STR);
        $update_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $update_stmt->execute();

        // success message
        temp('success', "Order status updated");
    } catch (PDOException $e) {
        temp('error', "Error updating order status: ");
    }
}

// get order information
try {
    $order_sql = "SELECT o.*, m.username, m.email, m.address
                        FROM orders o
                        JOIN member m ON o.user_id = m.user_id
                        WHERE o.order_id = :order_id";
    $order_stmt = $pdo->prepare($order_sql);
    $order_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $order_stmt->execute();
    $order = $order_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header('Location: admin_orderdetail.php');
        exit;
    }

    // Get order items
    $items_sql = "SELECT od.*, p.name AS product_name, p.img_front
                  FROM order_details od
                  JOIN product p ON od.product_id = p.product_id
                  WHERE od.order_id = :order_id";
    $items_stmt = $pdo->prepare($items_sql);
    $items_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $items_stmt->execute();
    $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

</head>

<body>
    <header>
        <a href="/"><img src="../images/Logo.png" alt="Logo" class="logo"></a>
        <nav>
            <span><a class="admin-dashboard-btn" href="../page/admin_dashboard.php">Admin Dashboard</a></span>
            <a href="../page/admin_profile.php"><img src="../images/images_nav/user.svg" alt="User" height="28" width="28"></a>
        </nav>
    </header>

    <main>
        <!-- left sidenav -->
        <nav class="left-sidenav">
            <ul>
                <li><a href="../page/admin_dashboard.php">Dashboard</a></li>
                <li><a href="../page/admin_dashboard_products.php">Products</a></li>
                <li><a href="../page/admin_dashboard_user.php">Users</a></li>
                <li><a class="active" href="../page/admin_dashboard_orderdetail.php">Order Detail</a></li>
            </ul>
        </nav>

        <!-- maincontent -->
        <div class="main-content">
            <div class="back-link">
                <a href="../page/admin_dashboard_orderdetail.php">← Return to order list</a>
            </div>

            <h1>Order details #<?= htmlspecialchars($order['order_id']) ?></h1>

            <?php
            $flash_success = temp('success');
            $flash_error = temp('error');
            ?>
            <?php if (!empty($flash_success) || !empty($flash_error)): ?>
                <div id="info">
                    <?php if (!empty($flash_success)): ?>
                        <div class="success-message">
                            <?php echo $flash_success; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($flash_error)): ?>
                        <div class="error-message">
                            <?php echo $flash_error; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="order-details-container">
                <div class="order-meta">
                    <div class="meta-section">
                        <h2>Order Information</h2>
                        <div class="meta-item">
                            <strong>Order ID:</strong> <?= htmlspecialchars($order['order_id']) ?>
                        </div>
                        <div class="meta-item">
                            <strong>Order Date:</strong> <?= htmlspecialchars($order['created_at']) ?>
                        </div>
                        <div class="meta-item">
                            <strong>Order Status:</strong>
                            <span class="status-badge status-<?= strtolower($order['order_status']) ?>">
                                <?= htmlspecialchars($order['order_status']) ?>
                            </span>
                        </div>
                        <div class="meta-item">
                            <strong>Subtotal:</strong> <?= htmlspecialchars(number_format($order['total_amount'], 2)) ?>&nbsp;MYR
                        </div>
                        <div class="meta-item">
                        </div>
                    </div>

                    <div class="meta-section">
                        <h2>Customer information</h2>
                        <div class="meta-item">
                            <strong>Customer Name:</strong> <?= htmlspecialchars($order['username']) ?>
                        </div>
                        <div class="meta-item">
                            <strong>Email:</strong> <?= htmlspecialchars($order['email']) ?>
                        </div>
                        <div class="meta-item">
                        </div>
                    </div>

                    <div class="meta-section">
                        <h2>shipping address</h2>
                        <div class="meta-item">
                            <strong>Address:</strong> <?= htmlspecialchars($order['address'] ?? 'Not provided') ?>
                        </div>


                    </div>

                    <div class="meta-section">
                        <h2>Update order status</h2>
                        <form method="POST" action="">
                            <select name="new_status" class="status-select">
                                <option value="Pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Pending </option>
                                <option value="Paid" <?= $order['order_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="Shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="Delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="Cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Canceled</option>
                            </select>
                            <button type="submit" name="update_status" class="update-status-btn">Update Status</button>
                        </form>
                    </div>
                </div>

                <div class="order-items">
                    <h2>Order Product</h2>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product Picture</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($order_items) > 0): ?>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['product_id']) ?></td>

                                        <td>
                                            <?php if (!empty($item['img_front'])): ?>
                                                <img src="../<?= htmlspecialchars($item['img_front']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="product-thumbnail">
                                            <?php else: ?>
                                                <div class="no-image">No Image</div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                                        <td><?= htmlspecialchars(number_format($item['price'], 2)) ?>&nbsp;MYR</td>
                                        <td><?= htmlspecialchars(number_format($item['quantity'] * $item['price'], 2)) ?>&nbsp;MYR</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">no found the order</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right"><strong>Subtotal:</strong></td>
                                <td><?= htmlspecialchars(number_format($order['total_amount'], 2)) ?>&nbsp;MYR</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </main>

</body>

</html>