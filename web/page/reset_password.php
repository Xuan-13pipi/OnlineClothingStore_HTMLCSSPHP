<?php
require '../_base.php';
$_title = 'Reset Password | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/reset_password.css">
<!-- Nav Here -->
</head>
<body>
    <header>
        <a href="/"><img src="../images/Logo.png" alt="Logo" class="logo"></a>
</header>
<main>

<?php
require '../config.php';
$errors = [];
$success = false;

// Get the token from the URL
$token = $_GET['token'] ?? '';

// Validate the token
if (empty($token)) {
    $errors[] = 'Invalid or expired reset link.';
} else {
    // Check if the token exists and is not expired
    $stmt = $pdo->prepare('SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()');
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $errors[] = 'Invalid or expired reset link.';
    }
}

// Handle the password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['retype_password'] ?? '');

    // Validate the new password
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 5) {
        $errors[] = 'Password must be at least 5 characters.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // If no errors, update the password
    if (empty($errors)) {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update the user's password in the database
        $stmt = $pdo->prepare('UPDATE member SET password_hash = ? WHERE email = ?');
        $stmt->execute([$hashed_password, $reset['email']]);

        // Delete the used token
        $stmt = $pdo->prepare('DELETE FROM password_resets WHERE token = ?');
        $stmt->execute([$token]);

        // Set success message
        $success = true;
    }
}
?>

<div class="container-reset-password">
        <h1>Reset Password</h1>
        <p>Enter a new password below to change your password.</p>
        <!-- Display errors -->
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Display success message -->
        <?php if ($success): ?>
            <div class="success">
                <p>Your password has been reset successfully. <br> <a href="../page/login.php">Log in here!</a></p>
            </div>
        <?php elseif (!empty($token)): ?>
            <!-- Password reset form -->
            <form method="post">
                <input type="password" id="password" name="password" placeholder="new password" required autofocus>    
                <input type="password" id="retype_password" name="retype_password" placeholder="reenter password" required>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
<?php
include '../_foot.php';
?>