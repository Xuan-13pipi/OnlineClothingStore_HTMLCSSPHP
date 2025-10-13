<?php
require '../_base.php';

$_title = 'Admin Dashboard | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/admin_dashboard_products.css">
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

// Fetch products from database
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

//Paging
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

$count_sql = "SELECT COUNT(*) FROM product WHERE name LIKE :search";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);


$sql = "SELECT P.product_id, P.name, P.price, P.color, P.img_front, P.img_back 
        FROM product P 
        WHERE P.name LIKE :search
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
$searchParam = "%$search%";
$stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

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

            <div class="search-container">
                <form method="GET" action="">
                    <input type="text" id="search" name="search" placeholder="Search for products..." value="<?= htmlspecialchars($search) ?>">
                </form>
            </div>

            <div class="addproduct-container">
                <a href="../page/admin_dashboard_addproducts.php">
                    <img src="../images/images_nav/addproduct.svg" alt="Add Product" height="35" width="35">
                    <p>Add Product</p>
                </a>
            </div>

            <div class="product-container">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product">
                            <div class="product-img-container">
                                <img src="../<?= htmlspecialchars($product['img_front']) ?>" class="front" alt="<?= htmlspecialchars($product['name']) ?> Front">
                                <img src="../<?= htmlspecialchars($product['img_back']) ?>" class="back" alt="<?= htmlspecialchars($product['name']) ?> Back">
                            </div>
                            <div class="product-info">
                                <h2><?= htmlspecialchars($product['name']) ?></h2>
                                <p><b><?= number_format($product['price'], 2) ?> MYR </b> | <?= htmlspecialchars($product['color']) ?></p>
                                <!-- Add Edit Button -->
                                <a href="../lib/edit_product.php?product_id=<?= $product['product_id'] ?>" class="edit-btn">Edit</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <img src="../images/no-product-found.svg" alt="No Products">
                        <p>No products found. Add some products!</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <a href="?search=<?= urlencode($search) ?>&page=<?= $p ?>" class="<?= ($p == $page) ? 'active' : '' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>