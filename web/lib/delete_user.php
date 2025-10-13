<?php
require '../_base.php';

$_title = 'Admin Dashboard | EZ';
include '../_head.php';

require '../config.php';


if (!isset($_GET['user_id'])) {
    die('User ID not provided.');
    exit;
}

$user_id = $_GET['user_id'];

if ($_SESSION['user_id'] == $user_id) {
    temp('error', 'You cannot delete your own account.');
    header('Location: ../page/admin_dashboard_user.php');
    exit;
}

try {
    //Check user 
    $sql = "SELECT user_id FROM member WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $sql = "DELETE FROM member WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        temp('success', 'User deleted successfully.');
    } else {
        temp('error', 'User not found or could not be deleted.');
    }
} catch (Exception $e) {
    temp('error', 'An error occurred: ' . $e->getMessage());
}

header('Location: ../page/admin_dashboard_user.php');
exit;
?>
