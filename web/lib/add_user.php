<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php';
require_once '../_base.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $photo = $_FILES['photo_path'] ?? null;

    $errors = [];

    // Required fields check
    if (empty($username)) $errors[] = "Username is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if (empty($confirm)) $errors[] = "Confirm Password is required.";
    
    // Username check
    if (!empty($username)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username already exists.";
        }
    }

    // Email check
    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email already exists.";
        }
    }

    // Password match
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    // File upload check
    if (!isset($photo) || $photo['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Photo upload failed.";
    }

    // If there are errors, redirect with all errors as one flash message
    if (!empty($errors)) {
        $error_html = "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>";
        temp("error", $error_html);
        header("Location: ../page/admin_dashboard_adduser.php");
        exit;
    }

    // All good, proceed
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $upload_dir = '../images/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $photo_name = uniqid() . '_' . basename($photo['name']);
    $photo_path = $upload_dir . $photo_name;

    if (!move_uploaded_file($photo['tmp_name'], $photo_path)) {
        temp("error", "Error uploading photo.");
        header("Location: ../page/admin_dashboard_adduser.php");
        exit;
    }

    // Insert into DB
    $stmt = $pdo->prepare("
        INSERT INTO member (username, password_hash, email, photo_path, is_admin)
        VALUES (:username, :password_hash, :email, :photo_path, 1)
    ");

    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password_hash', $hashed_password);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':photo_path', $photo_path);

    if ($stmt->execute()) {
        temp("success", "User created successfully.");
        header("Location: ../page/admin_dashboard_user.php");
        exit;
    } else {
        temp("error", "Error inserting user: " . implode(" ", $stmt->errorInfo()));
        header("Location: ../page/admin_dashboard_adduser.php");
    }
    exit;
}
?>
