<?php
require '../_base.php';

$_title = 'Admin Dashboard | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/admin_dashboard_user.css">

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

$valid_sort_fields = ['user_id','username', 'created_at'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $valid_sort_fields) ? $_GET['sort'] : 'user_id';
$dir = isset($_GET['dir']) ? $_GET['dir'] : 'desc'; 

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tab = isset($_GET['tab']) && $_GET['tab'] == 'member' ? 'member' : 'admin';
function getNextDir($currentSort, $currentDir, $column) {
    if ($currentSort === $column) {
        return $currentDir === 'asc' ? 'desc' : 'asc';
    }
    return 'asc';
}

//Paging
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

$count_sql = "SELECT COUNT(*) FROM member WHERE username LIKE :search AND is_admin = :is_admin";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$count_stmt->bindValue(':is_admin', ($tab == 'admin' ? 1 : 0), PDO::PARAM_INT);
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

$sql = "SELECT user_id, username, hp, email, photo_path, created_at, address
        FROM member  
        WHERE username LIKE :search AND is_admin = :is_admin
        ORDER BY $sort $dir
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':is_admin', ($tab == 'admin' ? 1 : 0), PDO::PARAM_INT);
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$admins = $tab === 'admin' ? $results : [];
$members = $tab === 'member' ? $results :   [];

?>


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

<!-- Sidenav Bar -->
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
    <p>User Mangament</p>

    <div class="tabs">
        <a href="?tab=admin&search=<?= htmlspecialchars($search) ?>" class="<?= $tab == 'admin' ? 'active' : '' ?>">Admin</a>
        <a href="?tab=member&search=<?= htmlspecialchars($search) ?>" class="<?= $tab == 'member' ? 'active' : '' ?>">Member</a>
    </div>
    
    <div class="search-container">
        <form method="GET">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
        <input type="text" name="search" id="search" placeholder="Search for users.." value="<?= htmlspecialchars($search) ?>">
        </form>

    </div>

    <?php if ($tab == 'admin'): ?>
    <div id="admin-list">
    
        <div class="record-container">
            <h4><?= count($admins) ?> Admin(s) / <?= $total_records ?> total</h4>
            <a href="../page/admin_dashboard_adduser.php" class="add-admin">Add User</a>
        </div>

        <table>
            <thead>
            <tr>
                <th>No.</th>
                <th></th>
                <th>
                    <a class="sort" href="?tab=<?= $tab ?>&search=<?= htmlspecialchars($search) ?>&sort=username&dir=<?= getNextDir($sort, $dir, 'username') ?>">
                    Username <?= $sort == 'username' ? ($dir == 'asc' ? '▴' : ($dir == 'desc' ? '▾' : '')) : '' ?>
                    </a>
                </th>
                <th>Phone Number</th>
                <th>Email</th>
                <th>Address</th>
                <th>
                <a class="sort" href="?tab=<?= $tab ?>&search=<?= htmlspecialchars($search) ?>&sort=created_at&dir=<?= getNextDir($sort, $dir, 'created_at') ?>">
                    Date Created <?= $sort == 'created_at' ? ($dir == 'desc' ? '▾' : ($dir == 'asc' ? '▴' : '')) : '' ?>
                    </a>
                </th>
                <th></th>
            </tr>
            </thead>
            <?php if (!empty($admins)): ?>
            <?php $i = $offset + 1; foreach ($admins as $s): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><img class="user-photo" src="<?= htmlspecialchars('../' . ($s['photo_path'] ?: 'images/default_user.png')) ?>" alt="User Photo"></td>
                <td><?= htmlspecialchars($s['username']) ?></td>
                <td><?= htmlspecialchars($s['hp']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><?= htmlspecialchars($s['address']) ?></td>
                <td><?= htmlspecialchars($s['created_at']) ?></td>
                <td>
                    <a class= "edit-btn" href="../lib/edit_user.php?user_id=<?= $s['user_id'] ?>">Edit</a>
                    <a class= "delete-btn" href="../lib/delete_user.php?user_id=<?= $s['user_id'] ?>" onclick="return confirm('Are you sure you want to delete this admin?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No user found</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if ($tab == 'member'): ?>
    <div id="member-list">
    <h4><?= count($members) ?> Member(s) / <?= $total_records ?> total</h4>
        <table>
            <thead>
            <tr>
                <th>No.</th>
                <th></th>
                <th>
                <a class="sort" href="?tab=<?= $tab ?>&search=<?= htmlspecialchars($search) ?>&sort=username&dir=<?= getNextDir($sort, $dir, 'username') ?>">
                    Username <?= $sort == 'username' ? ($dir == 'asc' ? '▴' : ($dir == 'desc' ? '▾' : '')) : '' ?>
                    </a>
                </th>
                <th>Phone Number</th>
                <th>Email</th>
                <th>Address</th>
                <th>
                <a class="sort" href="?tab=<?= $tab ?>&search=<?= htmlspecialchars($search) ?>&sort=created_at&dir=<?= getNextDir($sort, $dir, 'created_at') ?>">
                    Date Created <?= $sort == 'created_at' ? ($dir == 'desc' ? '▾' : ($dir == 'asc' ? '▴' : '')) : '' ?>
                    </a>
                </th>
                <th></th>
            </tr>
            </thead>
            <?php if (!empty($members)): ?>
                <?php $i = $offset + 1; foreach ($members as $s): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><img class="user-photo" src="<?= htmlspecialchars('../' . ($s['photo_path'] ?: 'images/default_user.png')) ?>" alt="User Photo"></td>
                    <td><?= htmlspecialchars($s['username']) ?></td>
                    <td><?= htmlspecialchars($s['hp']) ?></td>
                    <td><?= htmlspecialchars($s['email']) ?></td>
                    <td><?= htmlspecialchars($s['address']) ?></td>
                    <td><?= htmlspecialchars($s['created_at']) ?></td>
                    <td>
                        <a class= "edit-btn" href="../lib/edit_user.php?user_id=<?= $s['user_id'] ?>">Edit</a>
                        <a class= "delete-btn" href="../lib/delete_user.php?user_id=<?= $s['user_id'] ?>" onclick="return confirm('Are you sure you want to delete this admin?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align: center;">No user found</td>
            </tr>
        <?php endif; ?>
    </table>
    </div>
    <?php endif; ?>

<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
        <a href="?tab=<?= $tab ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&dir=<?= $dir ?>&page=<?= $p ?>" class="<?= ($p == $page) ? 'active' : '' ?>">
            <?= $p ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>
