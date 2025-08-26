<?php
require_once 'includes/header.php';

// --- Authorization & Security Checks ---
if ($_SESSION['division'] !== 'Group') { 
    die("Access Denied."); 
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    die("Invalid request method."); 
}
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { 
    die("CSRF validation failed."); 
}

// --- Get Form Data ---
$user_id = trim($_POST['id'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$division = trim($_POST['division'] ?? '');
$is_update = !empty($user_id);

// --- Validation ---
if (empty($username) || empty($division) || (!$is_update && empty($password))) {
    header("Location: users.php?status=error");
    exit();
}

try {
    if ($is_update) {
        // --- UPDATE User Logic ---
        if (!empty($password)) {
            // If a new password was provided, hash it and update the password_hash column
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET username = ?, password_hash = ?, division = ? WHERE id = ?";
            $params = [$username, $password_hash, $division, $user_id];
        } else {
            // If the password field was left blank, do NOT update the password_hash column
            $sql = "UPDATE users SET username = ?, division = ? WHERE id = ?";
            $params = [$username, $division, $user_id];
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        header("Location: users.php?status=updated");

    } else {
        // --- CREATE User Logic ---
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password_hash, division) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $password_hash, $division]);
        header("Location: users.php?status=created");
    }
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        die("Error: A user with that username already exists. Please go back and choose a different username.");
    } else {
        error_log("User action failed: " . $e->getMessage());
        die("Database error. Please check the logs.");
    }
}
exit();
?>