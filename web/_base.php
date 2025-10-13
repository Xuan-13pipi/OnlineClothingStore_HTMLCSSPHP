<?php

// ============================================================================
// PHP Setups
// ============================================================================
date_default_timezone_set('Asia/Kuala_Lumpur');
if (session_status() === PHP_SESSION_NONE) {    // Make sure session don't start twice
    session_start();
}

require_once 'lib/PHPMailer.php';
require_once 'lib/SMTP.php'; 

// ============================================================================
// Database Setups
// ============================================================================

if (!function_exists('isIdUnique')) {
    function isIdUnique($id, $pdo, $table, $column) {
        try {
            // Prepare the SQL query using PDO
            $sql = "SELECT $column FROM $table WHERE $column = :id";
            $stmt = $pdo->prepare($sql);
            
            // Bind the parameter
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
    
            // Execute the query
            $stmt->execute();
    
            // Check if any rows were returned
            return $stmt->rowCount() === 0; // Return true if the ID is unique
        } catch (PDOException $e) {
            // Handle database errors
            die("Database error: " . $e->getMessage());
        }
    }
}

// ============================================================================
// HTML Helper
// ============================================================================
function temp($key, $value = null) {
    if ($value !== null) {
        $_SESSION[$key] = $value; 
        return true;
    }
    if (isset($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]); 
        return $message;
    }
    return null; 
}
?>

