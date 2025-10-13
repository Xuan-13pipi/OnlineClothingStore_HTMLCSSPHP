<?php
require '../_base.php';

$_title = 'Login | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/login.css">
<link rel="stylesheet" href="../css/fixed_footer.css">
<?php
include '../_nav.php';
?>
<!-- Start coding here -->

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require '../config.php';

// Initialize variables
$errorMessage = '';


// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get form data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($username) || empty($password)) {
        $errorMessage = "Username and password are required.";
    } else {
        try {
            // Query the database using PDO
            $query = "SELECT * FROM member WHERE username = :username";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            // Check if a user was found
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verify password using password_verify()
                if (password_verify($password, $user['password_hash'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin']; // Store admin status

                    // Redirect based on role
                    if ($user['is_admin'] == 1) {
                        temp('success', "Welcome back, admin.");
                        header("Location: ../page/admin_dashboard.php");
                        exit();
                    } else {
                        temp('success', "You've successfully logged in.");
                        header("Location: ../index.php");
                        exit();
                    }
                } else {
                    $errorMessage = "Invalid username or password. Please try again.";
                }
            } else {
                $errorMessage = "Invalid username or password. Please try again.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="container-login-image">
    <img src="../images/prettygirl.png" alt="Pretty Girl" max-width="100%" height="850px">
</div>

<div class="container-login">
    <h1 class="login">Login</h1>
    <form class="login-form" action="" method="post">
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <input type="text" name="username" placeholder="username" autofocus><br>
        
        <!-- Password field with eye image toggle -->
        <div class="password-wrapper" style="position: relative; display: inline-block;">
            <input type="password" name="password" placeholder="password" id="password"><br>
            <img src="../images/visiblePassOff.svg" class="toggle-password" 
                 style="position: absolute; right: 15px; top: 14px; cursor: pointer; width: 25px; height: 25px; cursor: pointer;">
        </div>
        
        <span><a href="../page/forgot_password.php" class="forgot-password">Forgot Password?</a></span>
        <button type="submit">Sign in</button>
    </form>
    <span class="register-span">Not a member? <a href="../page/register.php" class="register-now">Register Now!</a></span>
</div>
<script>
$(document).ready(function() {
    $('.toggle-password').click(function() {
        const input = $('#password');
        const icon = $('.toggle-password');

        if (input.prop('type') === 'password') {
            input.prop('type', 'text');
            icon.prop('src', '../images/visiblePassOn.svg'); // Change to open eye
        } else {
            input.prop('type', 'password');
            icon.prop('src', '../images/visiblePassOff.svg'); // Change to closed eye
        }
    });
});
</script>
<?php
include '../_foot.php';
?>