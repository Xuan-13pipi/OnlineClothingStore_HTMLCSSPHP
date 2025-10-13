<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../_base.php';

$_title = 'Admin Dashboard | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/admin_dashboard.css">

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
$query = "SELECT username, email, photo_path, created_at, is_admin FROM member WHERE is_admin = 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if not admin
if (!$current_user || $current_user['is_admin'] != 1) {
    header('Location: ../index.php'); 
    exit;
}

$sql = "SELECT SUM(DISTINCT orders.total_amount) AS total_revenue, COUNT(DISTINCT orders.user_id) AS total_customers, 
        COUNT(DISTINCT orders.order_id) AS total_transactions, SUM(order_details.quantity) AS total_products
        FROM orders
        LEFT JOIN order_details ON orders.order_id = order_details.order_id
        WHERE orders.order_status IN ('Paid', 'Shipped', 'Delivered')";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$totalRevenue = $data['total_revenue'] ?? 0;
$totalCustomers = $data['total_customers'] ?? 0;
$totalTransactions = $data['total_transactions'] ?? 0;
$totalProducts = $data['total_products'] ?? 0;

$query = $pdo->query("SELECT COALESCE(SUM(order_details.quantity),0) AS y, category.name AS label 
                      FROM order_details
                      INNER JOIN orders ON order_details.order_id = orders.order_id AND orders.order_status IN ('Paid', 'Shipped', 'Delivered')
                      JOIN product ON product.product_id = order_details.product_id
                      RIGHT JOIN category ON  category.cat_id = product.cat_id
                      GROUP BY category.name");
$dataPoints = $query->fetchAll(PDO::FETCH_ASSOC); // Fetch as associative array




$sql = "SELECT product.name,product.name, product.img_front, product.img_back, SUM(order_details.quantity) AS total_sales
        FROM order_details
        JOIN product  ON order_details.product_id = product.product_id
        JOIN orders ON order_details.order_id = orders.order_id
        WHERE orders.order_status IN ('Paid', 'Shipped', 'Delivered')
        GROUP BY product.name, product.img_front, product.img_back
        ORDER BY total_sales DESC
        LIMIT 3";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$top3 = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!-- Nav Bar here -->
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
<!-- Sidenav Bar here -->
<nav class="left-sidenav">
    <ul>
        <li><a class="active" href="../page/admin_dashboard.php">Dashboard</a></li>
        <li><a href="../page/admin_dashboard_products.php">Products</a></li>
        <li><a href="../page/admin_dashboard_user.php">Users</a></li>
        <li><a href="../page/admin_dashboard_orderdetail.php">Order Detail</a></li>
    </ul>
</nav>

<!-- Main Content -->
<div class="main-content">
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
    <h2>Dashboard</h2>
    <h4>Last 24 hour</h4>
        <div class="stats">
            <div class="card"><p>Total Revenue</p><?= htmlspecialchars($totalRevenue); ?> MYR</div>
            <div class="card"><p>Total Customer</p><?= htmlspecialchars($totalCustomers); ?></div>
            <div class="card"><p>Total Transaction</p><?= htmlspecialchars($totalTransactions); ?></div>
            <div class="card"><p>Total Product</p><?= htmlspecialchars($totalProducts); ?></div>
        </div>
    
    <div class="chart-product">
        <div id="chartContainer" style="height: 370px; width: 80%;"></div>
        <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>

    <div class="top-product">
        <div class="top-header">
            <h2>Top Product</h2>
        </div>
        <p class="top-subtext">Top 3 of today based on total sold</p>

        <div class="product-list">
            <?php 
            for ($i = 0; $i < 3; $i++): 
            $product = $top3[$i] ?? null;
            ?>
                <div class="product-card">
                    <?php if ($product): ?>
                        <img src="<?= htmlspecialchars($product['img_front']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p><?= $product['total_sales'] ?> sold</p>
                        </div>
                    <?php else: ?>
                        <img src="../images/placeholder.png" alt="No Product" />
                        <div class="product-info">
                            <h3>Unknown</h3>
                            <p>0 sold</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
        
    </div>
</body>

<script>
window.onload = function() {
 
var chart = new CanvasJS.Chart("chartContainer", {
	animationEnabled: true,
	theme: "light2",
	title:{
		text: "Product Category Statistics",
        fontSize: 23,
        fontWeight: "normal"
    },
	axisY: {
		title: "Number of Products",
        titleFontSize: 17,
        minimum: 0, // Start at 0
        maximum: 50, // Set max value
        interval: 10// Set step size
	},
	data: [{
		type: "column",
        color: "#9db4c0",
		yValueFormatString: "#,##0.## pieces",
        dataPointWidth: 10, // Adjust column width (default: auto)
		dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
	}]
});
chart.render();
 
}


</script>

</html>


