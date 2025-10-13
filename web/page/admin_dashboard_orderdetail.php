<?php
require '../_base.php';

$_title = 'Admin Dashboard | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/admin_dashboard_orderdetail.css">

<?php
// Database Connection

require '../config.php';

// Check if user is logged in

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get the current user ID
$user_id = $_SESSION['user_id'];

try {
    // Get user role (check if admin)
    $query = "SELECT is_admin FROM member WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Redirect if user is not an admin
    if (!$user || $user['is_admin'] != 1) {
        header('Location: ../index.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$order_status = isset($_GET['order_status']) ? trim($_GET['order_status']) : '';

// Query orders
$sql = "SELECT o.order_id, o.user_id, m.username, o.total_amount, o.order_status, o.created_at 
        FROM orders o
        LEFT JOIN member m ON o.user_id = m.user_id
        WHERE 1=1";



// Handle search input
if (!empty($search)) {
    if (is_numeric($search)) {
        $sql .= " AND o.order_id = :search";
    } else {
        $sql .= " AND m.username LIKE :search";
    }
}

if (!empty($order_status)) {
    $sql .= " AND o.order_status = :order_status";
}


$stmt = $pdo->prepare($sql);


// Bind search parameters
if (!empty($search)) {
    if (is_numeric($search)) {
        $stmt->bindParam(':search', $search, PDO::PARAM_INT);
    } else {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    }
}


if (!empty($order_status)) {
    $stmt->bindParam(':order_status', $order_status, PDO::PARAM_STR);
}

$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Navigation Bar -->
</head>

<body>
    <header>
        <a href="/"><img src="../images/Logo.png" alt="Logo" class="logo"></a>
        <nav>
            <span> <a class="admin-dashboard-btn" href="../page/admin_dashboard.php">Admin Dashboard</a></span>
            <a href="../page/profile_page.php"><img src="../images/images_nav/user.svg" alt="User" height="28" width="28"></a>
        </nav>
    </header>

    <main>
        <!-- Sidebar Navigation -->
        <nav class="left-sidenav">
            <ul>
                <li><a href="../page/admin_dashboard.php">Dashboard</a></li>
                <li><a href="../page/admin_dashboard_products.php">Products</a></li>
                <li><a href="../page/admin_dashboard_user.php">Users</a></li>
                <li><a class="active" href="../page/admin_dashboard_orderdetail.php">Order Detail</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="search-container">
                <form method="GET" action="">
                    <input type="text" id="search" name="search" placeholder="Search orders..." value="<?= htmlspecialchars($search) ?>">

                </form>

                <p>
                    <a href="?" <?= empty($order_status) ? 'class="active"' : '' ?>>All</a> |
                    <a href="?order_status=pending" <?= $order_status === 'pending' ? 'class="active"' : '' ?>>Pending</a> |
                    <a href="?order_status=paid" <?= $order_status === 'paid' ? 'class="active"' : '' ?>>Paid</a> |
                    <a href="?order_status=shipped" <?= $order_status === 'shipped' ? 'class="active"' : '' ?>>Shipped</a> |
                    <a href="?order_status=delivered" <?= $order_status === 'delivered' ? 'class="active"' : '' ?>>Delivered</a> |
                    <a href="?order_status=cancelled" <?= $order_status === 'cancelled' ? 'class="active"' : '' ?>>Cancelled</a>
                </p>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Order Date</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_id']) ?></td>
                                    <td><?= htmlspecialchars($order['username']) ?></td>
                                    <td><?= htmlspecialchars($order['created_at']) ?></td>
                                    <td><?= htmlspecialchars(number_format($order['total_amount'], 2)) ?>&nbsp;MYR</td>
                                    <td class="status status-<?= strtolower($order['order_status']) ?>">
                                        <?= htmlspecialchars($order['order_status']) ?>
                                    </td>
                                    <td>
                                        <a href="order_details.php?id=<?= $order['order_id'] ?>" class="view-btn">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No orders found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>

</html>