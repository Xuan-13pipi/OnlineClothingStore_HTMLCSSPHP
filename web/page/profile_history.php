<?php
require '../_base.php';

// Database Connection
require '../config.php';

$_title = 'Profile | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/profile_history.css">

<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get the current user ID
$user_id = $_SESSION['user_id'];

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$order_status = isset($_GET['order_status']) ? trim($_GET['order_status']) : '';


// Base query structure similar to the second file's approach
$sql = "SELECT 
    o.order_id, 
    o.total_amount,
    od.product_id,
    od.size, 
    od.quantity, 
    od.price,
    p.name AS product_name,
    p.img_front,
    o.order_status,
    o.created_at
FROM order_details od
JOIN orders o ON od.order_id = o.order_id
JOIN member m ON o.user_id = m.user_id
JOIN product p ON od.product_id = p.product_id
WHERE m.user_id = :user_id";

// Handle search input with improved structure
if (!empty($search)) {
    if (is_numeric($search)) {
        $sql .= " AND o.order_id = :search";
    } else {
        $sql .= " AND p.name LIKE :search";
    }
}

// Add order status filter if selected
if (!empty($order_status)) {
    $sql .= " AND o.order_status = :order_status";
}

$sql .= " ORDER BY o.order_id DESC";

$stmt = $pdo->prepare($sql);

// Bind parameters in a cleaner way
$params = [':user_id' => $user_id];

// Add search parameter if needed
if (!empty($search)) {
    if (is_numeric($search)) {
        $params[':search'] = $search;
    } else {
        $params[':search'] = "%$search%";
    }
}

// Add order status parameter if needed
if (!empty($order_status)) {
    $params[':order_status'] = $order_status;
}

// Execute with all parameters at once
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group orders by order_id
$grouped_orders = [];
foreach ($orders as $order) {
    $order_id = $order['order_id'];
    if (!isset($grouped_orders[$order_id])) {
        $grouped_orders[$order_id] = [
            'order_id' => $order_id,
            'order_status' => $order['order_status'],
            'created_at' => $order['created_at'],
            'products' => []
        ];
    }
    $grouped_orders[$order_id]['products'][] = $order;
}

include '../_nav.php';
?>

<body>
    <!-- Sidenav Bar here -->
    <nav class="left-sidenav">
        <ul>
            <li><a href="../page/profile_page.php">User</a></li>
            <li><a class="active" href="../page/profile_history.php">History</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="search-container">
            <form method="GET" action="">
                <input type="text" id="search" name="search" placeholder="Search orders..." value="<?= htmlspecialchars($search) ?>">
            </form>

            <p>
    <a href="?" <?= empty($order_status) ? 'class="active"' : '' ?>> &nbsp; &nbsp; All</a> |
    <a href="?order_status=pending" <?= $order_status === 'pending' ? 'class="active"' : '' ?>>Pending</a> |
    <a href="?order_status=paid" <?= $order_status === 'paid' ? 'class="active"' : '' ?>>Paid</a> |
    <a href="?order_status=shipped" <?= $order_status === 'shipped' ? 'class="active"' : '' ?>>Shipped</a> |
    <a href="?order_status=delivered" <?= $order_status === 'delivered' ? 'class="active"' : '' ?>>Delivered</a> |
    <a href="?order_status=cancelled" <?= $order_status === 'cancelled' ? 'class="active"' : '' ?>>Cancelled</a>
</p>

        </div>

        <?php if (count($grouped_orders) > 0): ?>
            <?php foreach ($grouped_orders as $group): ?>
                <a href='history_detail.php?order_id=<?= $group["order_id"] ?>' class="order-link">
                <div class="order-container">
                    <!-- Order Header -->
                    <div class="order-header">
                        <div class="order-info">
                            <span class="order-id-label">Order ID: <?= htmlspecialchars($group['order_id']) ?></span>
                            <span class="order-date"><?= date('Y-m-d', strtotime($group['created_at'])) ?></span>
                        </div>
                        <div class="order-status">
                            <span class="status status-<?= strtolower($group['order_status']) ?>">
                                <?= htmlspecialchars($group['order_status']) ?>
                            </span>
                    
                        </div>
                    </div>
                    
                    <!-- Product List -->
                    <div class="product-list">
                        <?php foreach ($group['products'] as $product): ?>
                            <a href='history_detail.php?order_id=<?= $group["order_id"] ?>' class="order-link">

                            <div class="product-item">
                                <div class="product-image">
                                    
                                        <img src='../uploads/<?= htmlspecialchars($product["img_front"]) ?>' alt="Product Image">
                                  
                                </div>
                                <div class="product-details">
                                    <div class="product-name">
                                        
                                            <?= htmlspecialchars($product["product_name"]) ?>
                                       
                                    </div>
                                    <div class="product-variant">
                                      Size: <?= isset($product["size"]) ? htmlspecialchars($product["size"]) : 'Default' ?>
                                        
                                    </div>
                                    <div class="product-quantity">x<?= htmlspecialchars($product["quantity"]) ?></div>
                                </div>
                                <div class="product-price">
                                    RM<?= number_format($product["price"], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Order Footer -->
                    <a href='history_detail.php?order_id=<?= $group["order_id"] ?>' class="order-link">
                    <div class="order-footer">
                        <div class="order-total">
                            Total Amount: <span class="total-price">RM<?= number_format($product['total_amount'], 2) ?></span>
                        </div>
                     
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-orders">No order records found</div>
        <?php endif; ?>
    </div>