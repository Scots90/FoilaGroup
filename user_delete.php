<?php
require_once 'includes/header.php';

// --- Authorization & Security Checks ---
// 1. Ensure user is a Group admin
if ($_SESSION['division'] !== 'Group') { 
    die("Access Denied."); 
}
// 2. Ensure the request is a POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    die("Invalid request method."); 
}
// 3. Validate the CSRF token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { 
    die("CSRF validation failed."); 
}

// --- Get User ID to Delete ---
$user_id_to_delete = $_POST['id'] ?? '';

// --- Critical Self-Delete Check ---
if ($user_id_to_delete == $_SESSION['user_id']) {
    die("Error: You cannot delete your own account. Please go back.");
}

// --- Execute Deletion ---
if (!empty($user_id_to_delete) && is_numeric($user_id_to_delete)) {
    try {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id_to_delete]);
        header("Location: users.php?status=deleted");
    } catch (PDOException $e) {
        // In production, you would log this error instead of killing the script
        // error_log("Error deleting user: " . $e->getMessage());
        header("Location: users.php?status=error");
    }
} else {
    // Redirect if the ID is missing or invalid
    header("Location: users.php?status=error");
}
exit();
?>