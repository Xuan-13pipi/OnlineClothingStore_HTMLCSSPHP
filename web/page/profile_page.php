<?php
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors to the browser

require '../_base.php';
$_title = 'Profile | EZ';
include '../_head.php';
include '../_nav.php';

// Database Connection
require '../config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['is_admin'])) {
    header("Location: ../page/login.php"); // Redirect if not logged in
    exit();
}

$errors = [];

// Get user information from session
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$hp = "";
$address = "";
$default_photo = '../images/default_profile.png'; // Define default photo path once
$photo = $default_photo;

try {
    // Get user data from database
    $stmt = $pdo->prepare("SELECT address, username, photo_path, email, dob, hp FROM member WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $address = $user['address'] ?? '';
        $username = $user['username'];
        $hp = $user['hp'] ?? '';
        $email = $user['email'];
        $dob = $user['dob'];

        if (!empty($user['photo_path'])) {
            $photo = $user['photo_path'];
        }
    }
} catch (PDOException $e) {
    temp('error', "Failed to fetch user data.");
}



// upload username
if (isset($_POST['save_username'])) {
    $new_username =  trim($_POST['username']);

    // Validate name
    if (empty($new_username)) {
        $errors['username'] = 'Name is required.';
    } elseif (strlen($new_username) > 100) {
        $errors['username'] = 'Name must be less than 100 characters.';
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE username = ?");
        $stmt->execute([$new_username]);
        if ($stmt->fetchColumn() > 0) {
            $errors['username'] = 'Username already exists.';
        }
    }


    if (empty($errors['username'])) {
        try {
            $stmt = $pdo->prepare("UPDATE member SET username = :username WHERE user_id = :user_id");
            $stmt->bindParam(':username', $new_username, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            // Update the values ​​on the session and page
            $_SESSION['username'] = $new_username;
            $username = $new_username;
            temp('success', "username upload successfully!");
        } catch (PDOException $e) {
            temp('error', "There was an error updating the username");
        }
    } else {
        // If there is an error, display the error message and redirect
        temp('error', $errors['username']);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// upload phone number
if (isset($_POST['save_hp'])) {
    $new_hp = trim($_POST['hp']);
    $errors = [];

    // Validate phone number
    if (empty($new_hp)) {
        $errors['hp'] = 'Phone number is required.';
    } elseif (!preg_match('/^0\d{10}$/', $new_hp)) {
        $errors['hp'] = 'Phone number must be 11 digits.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE member SET hp = :hp WHERE user_id = :user_id");
            $stmt->bindParam(':hp', $new_hp, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            $hp = $new_hp;
            temp('success', "Phone number updated successfully!");
        } catch (PDOException $e) {
            temp('error', "Failed to update phone number");
        }
    } else {
        temp('error', $errors['hp']);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// upload email
if (isset($_POST['save_email'])) {
    $new_email =  trim($_POST['email']);
    $errors = [];

    // Validate email
    if (empty($new_email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    } elseif (strlen($new_email) > 100) {
        $errors['email'] = 'Email must be less than 100 characters.';
    } else {
        // Check if email already exists (excluding current user)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE email = ? AND user_id != ?");
        $stmt->execute([$new_email, $user_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors['email'] = 'Email already exists.';
        }
    }


    // If no errors, proceed with email update
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE member SET email = :email WHERE user_id = :user_id");
            $stmt->bindParam(':email', $new_email, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['email'] = $new_email;
            $email = $new_email;

            temp('success', "email upload successfully!");
        } catch (PDOException $e) {
            temp('error', "Database error: " . $e->getMessage());
        }
    } else {
        temp('error', $errors['email']);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// upload address
if (isset($_POST['save_address'])) {
    $new_address =  trim($_POST['address']);
    $errors = [];

    //validate address
    if (empty($new_address)) {
        $errors['address'] = 'Address is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE member SET address = :address WHERE user_id = :user_id");
            $stmt->bindParam(':address', $new_address, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            $address = $new_address;

            temp('success', "address updated successfully!");
        } catch (PDOException $e) {
            temp('error', "The address is already used");
        }
    } else {
        temp('error', $errors['address']);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// change password
if (isset($_POST['change_password'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    $error = [];

    //  Validate current password
    if (empty($current_password)) {
        $errors[] = 'Current password is required.';
    }

    //  Validate new password
    if (empty($new_password)) {
        $errors[] = 'New password is required.';
    } elseif (strlen($new_password) < 5 || strlen($new_password) > 100) {
        $errors[] = 'New password must be between 5 and 100 characters.';
    }

    //  Validate confirm password
    if (empty($confirm_password)) {
        $errors[] = 'Confirm password is required.';
    } elseif ($new_password !== $confirm_password) {
        $errors[] = 'New password and confirm password do not match.';
    }

    if (empty($errors)) {
        try {
            // fetch the current passeord
            $stmt = $pdo->prepare("SELECT password_hash FROM member WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // verify the password
            if (password_verify($current_password, $row['password_hash'])) {

                $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);

                // upload password
                $stmt = $pdo->prepare("UPDATE member SET password_hash = :password WHERE user_id = :user_id");
                $stmt->bindParam(':password', $new_hashed, PDO::PARAM_STR);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();

                temp('success', "password change successfully!");
            } else {
                temp('error', "current passeord is incorrect");
            }
        } catch (PDOException $e) {
            temp('error', "Database error: " . $e->getMessage());
        }
    } else {
        foreach ($errors as $error) {
            temp('error', $error);
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// photo upload
if (isset($_FILES['photo'])) {
    $upload = $_FILES['photo'];
    $errors = [];
    $use_default = false;

    // Validate photo
    if (empty($upload['name'])) {
        $errors[] = 'Profile photo is required.';
        $use_default = true;
    } elseif ($upload['size'] > 10 * 1024 * 1024) {
        $errors[] = 'Profile photo must be less than 10MB.';
        $use_default = true;
    } elseif (!in_array($upload['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
        $errors['photo'] = 'Profile photo must be a JPEG, PNG, or GIF image.';
    }


    // If no errors, proceed with photo update
    if (!$use_default) {
        $photo_dir = '../images/uploads/';

        if (!is_dir($photo_dir)) {
            mkdir($photo_dir, 0755, true);
        }

        $photo_name = uniqid('', true) . '_' . basename($upload['name']); // Added entropy for more uniqueness
        $photo_path = $photo_dir . $photo_name;

        if (move_uploaded_file($upload['tmp_name'], $photo_path)) {
            try {
                $stmt = $pdo->prepare("UPDATE member SET photo_path = ? WHERE user_id = ?");
                if ($stmt->execute([$photo_path, $user_id])) {

                    // If there was a previous avatar and it's not the default one, delete the old file
                    $old_photo = $user['photo_path'] ?? '';
                    if ($old_photo && file_exists($old_photo) && $old_photo != $default_photo) {
                        unlink($old_photo);
                    }
                    $photo = $photo_path;
                    temp('success', "Profile picture updated successfully!");
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    temp('error', "Failed to update database.");
                    $use_default = true;
                }
            } catch (PDOException $e) {
                temp('error', "Database error: " . $e->getMessage());
                $use_default = true;
            }
        } else {
            // upload  fail
            $use_default = true;
            $upload_failed = true;
        }
    }

    // If need to use the default avatar (verification failed or upload failed)
    if ($use_default) {
        $stmt = $pdo->prepare("UPDATE member SET photo_path = ? WHERE user_id = ?");
        if ($stmt->execute([$default_photo, $user_id])) {
            // Delete the old avatar (if it exists and is not the default)
            $old_photo = $user['photo_path'] ?? '';
            if ($old_photo && file_exists($old_photo) && $old_photo != $default_photo) {
                unlink($old_photo);
            }
            $photo = $default_photo;

            // Display errors
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    temp('error', $error);
                }
                temp('warning', "Using default avatar instead.");
            } elseif (isset($upload_failed)) {
                temp('warning', "Failed to upload photo. Using default avatar.");
            } else {
                temp('warning', "An error occurred. Using default avatar.");
            }

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Delete profile photo
    if (isset($_POST['delete_photo'])) {
        try {
            // Get current photo path
            $stmt = $pdo->prepare("SELECT photo_path FROM member WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $current_photo = $stmt->fetchColumn();

            // Update to default photo in database
            $stmt = $pdo->prepare("UPDATE member SET photo_path = ? WHERE user_id = ?");
            $stmt->execute([$default_photo, $user_id]);

            // Delete the old photo file if it exists and isn't the default
            if ($current_photo && $current_photo !== $default_photo && file_exists($current_photo)) {
                unlink($current_photo);
            }

            $photo = $default_photo;
            temp('success', "Profile photo deleted successfully!");
        } catch (PDOException $e) {
            temp('error', "Failed to delete profile photo: " . $e->getMessage());
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}


// logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../page/login.php");
    exit();
}

?>

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
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>

</head>


<body>
    <!-- Sidenav Bar here -->
    <nav class="left-sidenav">
        <ul>
            <li><a class="active" href="../page/profile_page.php">User</a></li>
            <li><a href="../page/profile_history.php">History</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="content-title">
            <div style="text-align:center;padding-top:60px;">
                <span>User Profile</span>
                <form style="text-align: right;" method="post">
                    <button type="submit" name="logout" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>

        <div class="profile-container">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
                <div class="profile-photo">
                    <div class="image-wrapper">
                        <img id="profile-image" src="<?php echo $photo; ?>" alt="Profile Picture">
                    </div>
                    <label for="file-upload" class="upload-label">
                        <span>+</span>
                        <input type="file" id="file-upload" name="photo" accept="image/*" onchange="this.form.submit()">
                    </label>
                    <?php if ($photo !== $default_photo): ?>
                        <form method="post" style="display: inline;">
                            <button type="submit" name="delete_photo" class="delete-photo-btn"
                                onclick="return confirm('Are you sure you want to delete your profile photo?')">
                                <i class="fas fa-trash"></i> Change to Default Photo
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

            </form>

            <div class="detail-label">NAME<i class="fas fa-edit edit-icon" onclick="toggleForm('username-form')"></i></div>
            <div class="detail-value" id="accountName"><?php echo $username; ?></div>
            <div id="username-form" class="edit-form hidden">
                <form method="post">
                    <input class="address-input" name="username" placeholder="Enter your name">
                    <br>
                    <button type="submit" name="save_username" class="save-btn" data-confirm>Save</button>
                </form>
            </div>

            <div class="detail-label">HP NUMBER (+60)<i class="fas fa-edit edit-icon" onclick="toggleForm('hp-form')"></i></div>
            <div class="detail-value" id="accountHP"><?php echo $hp; ?></div>
            <div id="hp-form" class="edit-form hidden">
                <form method="post">
                    <input class="address-input" name="hp" placeholder="Enter your phone number">
                    <br>
                    <button type="submit" name="save_hp" class="save-btn" data-confirm>Save</button>
                </form>
            </div>

            <div class="detail-label">E-MAIL<i class="fas fa-edit edit-icon" onclick="toggleForm('email-form')"></i></div>
            <div class="detail-value" id="accountEmail"><?php echo $email; ?></div>
            <div id="email-form" class="edit-form hidden">
                <form method="post">
                    <input class="address-input" name="email" placeholder="Enter your email">
                    <br>
                    <button type="submit" name="save_email" class="save-btn" data-confirm>Save</button>
                </form>
            </div>

            <div class="detail-label">YOUR ADDRESS<i class="fas fa-edit edit-icon" onclick="toggleForm('address-form')"></i></div>
            <div class="detail-value"><?php echo $address; ?></div>
            <div id="address-form" class="edit-form hidden">
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="address-form">
                    <input class="address-input" name="address" placeholder="Enter your address">
                    <br>
                    <button type="submit" name="save_address" class="save-btn" data-confirm>Save</button>
                </form>
            </div>

            <div class="password-change">
                <div class="detail-label">Change Password<i class="fas fa-edit edit-icon" onclick="toggleForm('password-form-container')"></i></div>
                <div id="password-form-container" class="edit-form hidden">
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="password-form">
                        <input type="password" name="current_password" placeholder="Current Password" required>
                        <input type="password" name="new_password" placeholder="New Password" required>
                        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                        <button type="submit" name="change_password" data-c onfirm>Update Password</button>
                    </form>
                </div>
            </div>
            <br>


            <div class="detail-label">Date Of Birthday</div>
            <div class="detail-value"><?php echo $dob; ?></div>
        </div>
    </div>

    <script>
        function toggleForm(formId) {

            document.getElementById(formId).classList.toggle('hidden');
        }


        $('[data-confirm]').on('click', e => {
            const text = e.target.dataset.confirm || 'Are you sure?';
            if (!confirm(text)) {
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        });
    </script>
</body>

</html>