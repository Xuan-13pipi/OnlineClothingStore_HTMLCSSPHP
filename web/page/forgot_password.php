<?php
require '../_base.php';
$_title = 'Forgot Password | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/forgot_password.css">
<?php
include '../_nav.php';
require '../config.php'; // Database Connection
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Validate email
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    } else {
        // Check if email exists in the database
        $stmt = $pdo->prepare("SELECT user_id FROM member WHERE email = ?");
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        if (!$u) {
            $errors[] = 'Email not found.';
        }
    }

    // If no errors, generate a reset token and send email
    if (empty($errors)) {
        $token = sha1(rand()); // Generate a secure token

        // Delete old tokens and insert new token
        $stmt = $pdo->prepare('
        DELETE FROM password_resets WHERE email = ?;
        INSERT INTO password_resets (email, token, expires_at)
        VALUES (?, ?, ADDTIME(NOW(), "00:05"))
        ');

        $stmt->execute([$email, $email, $token]);
        // Send email with reset link
        $mail = new PHPMailer(true);
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'webappezshop@gmail.com'; // App Email
        $mail->Password = 'tdsv ufwy iiux fdsj'; // App Password
        $mail->CharSet = 'utf-8';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('webappezshop@gmail.com', 'EZ Support');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $reset_link = "http://localhost/page/reset_password.php?token=$token";
        $mail->Body = "Click the link below to reset your password:<br><br>
                       <a href='$reset_link'>Reset Password</a><br><br>
                       This link will expire in 1 hour.";

        $mail->send();
        $success = true;
    }
}
?>

<div class="container-forgot-password">
    <h1 class="header-forgot-password">Forgot Password</h1>

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
            <p>An email with a password reset link has been sent to your email address.</p>
        </div>
    <?php endif; ?>

    <form method="post" class="form-forgot-password">
        <input type="email" id="email" name="email" placeholder="Enter your email" required autofocus>
        <button type="submit">Send Reset Link</button>
    </form>

    <div class="back-to-login">
        <p>Remember your password? <a href="../page/login.php">Log in here!</a></p>
    </div>
</div>

<?php
include '../_foot.php';
?>