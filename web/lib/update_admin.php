<?php
require '../_base.php';
include '../config.php';

?>
<link rel="stylesheet" href="../css/edit_user.css">

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $photo = $_FILES['photo_path'];
    $userId = $_POST['user_id'];
    $userName = $_POST['username'];
    $hp = $_POST['hp'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    // Query raw data
    $stmt = $pdo->prepare("SELECT password_hash, photo_path FROM member WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Error: User not found.");
    }

    $old_photo_path = $user['photo_path'];
    $photo_path = $old_photo_path; 
    $default_photo = '../images/default_profile.png'; // Define default photo path once

    //Upload Photo
    if (isset($_FILES['photo_path']) && $_FILES['photo_path']['error'] === UPLOAD_ERR_OK) {
        $photo = $_FILES['photo_path'];
        $upload_dir = '../images/uploads/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $photo_name = uniqid() . '_' . basename($photo['name']);
        $photo_path = $upload_dir . $photo_name;

        if (move_uploaded_file($photo['tmp_name'], $photo_path)) {
            // Delete Picture if exist
            if (!empty($old_photo_path) && file_exists($old_photo_path)) {
                unlink($old_photo_path);
            }
        } else {
            die("Error: Failed to upload new photo.");
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

    // Use PDO prepared statements
    $sql = "UPDATE member 
            SET photo_path =:photo_path, username = :username, hp = :hp, email = :email, address = :address
            WHERE user_id = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':photo_path', $photo_path, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT); 
    $stmt->bindParam(':username', $userName, PDO::PARAM_STR);
    $stmt->bindParam(':hp', $hp, PDO::PARAM_STR); 
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':address', $address, PDO::PARAM_STR); 

    if ($stmt->execute()) {
        echo "User updated successfully!";
    } else {
        echo "Error updating user.";
    }

    header("Location: ../page/admin_dashboard_user.php");
    exit();
}
?>