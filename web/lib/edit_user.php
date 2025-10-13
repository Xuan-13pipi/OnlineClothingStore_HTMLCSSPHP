<?php
require '../_base.php';

$_title = 'Admin Dashboard | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/edit_user.css">

<?php
// Database Connection
require '../config.php';

// Get the user_id from the URL
if (!isset($_GET['user_id'])) {
    die("User ID is missing.");
}
$userId = $_GET['user_id'];

// Fetch admin details from the database using PDO
try {
    $sql = "SELECT * FROM member WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    // Check if the product exists
    if ($stmt->rowCount() === 0) {
        die("User not found.");
    }

    // Fetch the admin details
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

?>

</head>
<body>
    <header>
        <a href="/"><img src="../images/Logo.png" alt="Logo" class="logo"></a>
        <nav>
            <span>Admin Dashboard</span>
            <a href="../page/profile_page.php"><img src="../images/images_nav/user.svg" alt="User" height="28" width="28"></a>
        </nav>
    </header>

<main>
    <div class="main-content">
        <h1>Edit Account</h1>
        <form id="editAdminForm" action="../lib/update_admin.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId) ?>">

                <div class="img-upload-container">
                    <div class="img-upload">
                        <h3>Profile Picture</h3>
                        <img id="preview" src="<?php echo $user['photo_path'] ?? '#'; ?>" alt="Profile Picture Preview" style="display:none;">
                        <input type="file" id="photo_path" name="photo_path" accept="image/*" >
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Admin Name</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="hp">Phone Number</label>
                    <input type="tel" id="hp" name="hp" placeholder="Phone number (e.g. 01234567890)" pattern="0\d{10}" value="<?= htmlspecialchars($user['hp']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Addresss</label>
                    <input type="text" id="address" name="address" placeholder="Enter your address" value="<?= htmlspecialchars($user['address']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Date Created</label>
                    <p class="readonly-text"><?= htmlspecialchars($user['created_at']) ?></p>
                </div>
            
                <button type="submit" class="save">Save Changes</button>
                
            </form>

            <button type="button" class="back" onclick="window.location.href='../page/admin_dashboard_user.php'">Cancle Changes</button>

        </div>
    </main>

    <script src="../js/user_form.js"></script>
</body>
</html>
