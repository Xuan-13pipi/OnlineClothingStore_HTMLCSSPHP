<?php
require_once '../_base.php';
$_title = 'Admin Dashboard | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/admin_dashboard_adduser.css">
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

<?php include '../lib/add_user.php'; ?>

<body>
    <header>
        <a href="/"><img src="../images/Logo.png" alt="Logo" class="logo"></a>
        <nav>
        <span> <a class="admin-dashboard-btn" href="../page/admin_dashboard.php">Admin Dashboard</a></span>
            <a href="../page/profile_page.php"><img src="../images/images_nav/user.svg" alt="User" height="28" width="28"></a>
        </nav>
    </header>

    <main>
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
    <?php endif; 
    ?>

    
        <!-- Sidenav Bar here -->
        <nav class="left-sidenav">
            <ul>
                <li><a href="../page/admin_dashboard.php">Dashboard</a></li>
                <li><a href="../page/admin_dashboard_products.php">Products</a></li>
                <li><a class="active" href="../page/admin_dashboard_user.php">Users</a></li>
                <li><a href="../page/admin_dashboard_orderdetail.php">Order Detail</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Add New Admin</h1>
            <form id="adduser-form" action="../lib/add_user.php" method="POST" enctype="multipart/form-data">
            <!-- Image Upload -->
            <div class="img-upload-container">
                <div class="img-upload">
                    <h3>Profile Picture</h3>
                    <input type="file" id="photo_path" name="photo_path" accept="image/*" required>
                     <img id="preview" src="#" alt="Front Image Preview" style="display:none;">
                </div>
            </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Insert name here..." required>
                </div>

                <div class="form-group">
                    <label for="hp">Phone Number</label>
                    <input type="tel" id="hp" name="hp" placeholder="Phone number (e.g. 01234567890)" pattern="0\d{10}" required value="<?= htmlspecialchars($_POST['hp'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" placeholder="example@gmail.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Please enter your" maxlength="100" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Please enter your" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label>Date Created</label>
                    <p class="readonly-text"><?php echo date("Y-m-d H:i:s"); ?></p>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="save">Add New Admin</button>
            </form>

            <button type="button" class="back" onclick="window.location.href='../page/admin_dashboard_user.php'">Cancle</button>

        </div>
    </main>
    <script src="../js/user_form.js"></script>
</body>
</html>