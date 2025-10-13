<?php
require '../_base.php';

$_title = 'Register | EZ';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/register.css">
<?php
include '../_nav.php';
?>

<!-- Start coding here -->

<!-- Database Connection -->
<?php
require '../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$errors = [];
$email = $password = $confirm = $name = $dob = $photo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $hp = trim($_POST['hp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['retype_password'] ?? '');
    $name = trim($_POST['username'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $photo = $_FILES['photo'] ?? null;

    // Validate phone number
    if (empty($hp)) {
        $errors['hp'] = 'Phone number is required.';
    } elseif (!preg_match('/^0\d{10}$/', $hp)) {
        $errors['hp'] = 'Phone number must be 11 digits starting with 0.';
    } else {
        // Check if phone number already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE hp = ?");
        $stmt->execute([$hp]);
        if ($stmt->fetchColumn() > 0) {
            $errors['hp'] = 'Phone number already exists.';
        }
    }

    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'Email must be less than 100 characters.';
    } else {
        // Check if email already exists using PDO
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors['email'] = 'Email already exists.';
        }
    }

    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 5 || strlen($password) > 100) {
        $errors['password'] = 'Password must be between 5 and 100 characters.';
    }

    // Validate confirm password
    if (empty($confirm)) {
        $errors['retype_password'] = 'Confirm password is required.';
    } elseif ($confirm !== $password) {
        $errors['retype_password'] = 'Passwords do not match.';
    }

    // Validate name
    if (empty($name)) {
        $errors['username'] = 'Name is required.';
    } elseif (strlen($name) > 100) {
        $errors['username'] = 'Name must be less than 100 characters.';
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE username = ?");
        $stmt->execute([$name]);
        if ($stmt->fetchColumn() > 0) {
            $errors['username'] = 'Username already exists.';
        }
    }

    // Validate date of birth
    if (empty($dob)) {
        $errors['dob'] = 'Date of birth is required.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
        $errors['dob'] = 'Invalid date format. Use YYYY-MM-DD.';
    }

    // Validate profile photo
    if (!empty($photo['name'])) { // Only validate if photo was uploaded
        if ($photo['size'] > 10 * 1024 * 1024) { // 10MB limit
            $errors['photo'] = 'Profile photo must be less than 10MB.';
        } elseif (!in_array($photo['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
            $errors['photo'] = 'Profile photo must be a JPEG, PNG, or GIF image.';
        }
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        $photo_dir = '../images/uploads/';
        if (!is_dir($photo_dir)) {
            mkdir($photo_dir, 0755, true);
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $photo_name = uniqid() . '_' . basename($photo['name']);
        $photo_path = $photo_dir . $photo_name;

        $default_photo = '../images/default_profile.png';
        $photo_path = $default_photo; // Set default first
        // If no errors, proceed with registration
        if (empty($errors)) {
            $photo_dir = '../images/uploads/';
            if (!is_dir($photo_dir)) {
                mkdir($photo_dir, 0755, true);
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $default_photo = '../images/default_profile.png';
            $photo_path = $default_photo; // Set default first
    
            // If photo was uploaded
            if (!empty($photo['name'])) {
            $photo_name = uniqid() . '_' . basename($photo['name']);
            $photo_path = $photo_dir . $photo_name;
                if (!move_uploaded_file($photo['tmp_name'], $photo_path)) {
                    $photo_path = $default_photo; // Fallback to default if upload fails
                }
            }
    
            // Insert user into database (whether photo was uploaded or not)
            $stmt = $pdo->prepare("INSERT INTO member (username, password_hash, email, dob, photo_path, hp) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $hashed_password, $email, $dob, $photo_path, $hp])) {
                header('Location: login.php');
                exit;
            } else {
                $errors[] = 'Failed to create account. Please try again.';
            }
        }
    }
}
?>


<div class="container-register">
    <h1 class="header-register">Register</h1>

    <!-- Display error or success messages -->
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" class="form-register" enctype="multipart/form-data">
        <!-- Photo Upload Section -->
        <div class="profile-container">
        <div class="profile-photo">
            <div class="image-wrapper">
                <img id="profile-image" src="default-avatar.png">
            </div>
            <label for="file-upload" class="upload-label">
                <span>+</span>
                <input type="file" id="file-upload" name="photo" accept="image/*">
            </label>
        </div>
    </div>
        <input type="text" id="username" name="username" placeholder="username" maxlength="100" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        <input type="tel" id="hp" name="hp" placeholder="Phone number (e.g. 01234567890)" pattern="0\d{10}" required value="<?= htmlspecialchars($_POST['hp'] ?? '') ?>">
        <input type="text" id="email" name="email" placeholder="email" maxlength="100" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <input type="password" id="password" name="password" placeholder="password" maxlength="100" required>
        <input type="password" id="retype_password" name="retype_password" placeholder="retype password" maxlength="100" required>
        <input type="date" id="dob" name="dob" required value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>">
        <button type="submit">Register</button>
    </form>

    <?php if (!empty($errors) && !empty($_FILES['photo']['tmp_name'])): ?>
    <script>
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-image').src = e.target.result;
        }
        reader.readAsDataURL(new Blob([<?php echo json_encode(file_get_contents($_FILES['photo']['tmp_name'])); ?>]));
    </script>
    <?php endif; ?>

    <div class="alr-have-acc">
        <p>Already have an account? <a href="../page/login.php" style="font-weight: 700;">Sign in here!</a></p>
    </div>
</div>

<script src="../js/register.js"></script>
<?php
include '../_foot.php';
?>