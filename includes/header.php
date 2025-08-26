<?php
// Start the session on every page
session_start();

// Include the database connection file
require_once 'includes/db_connect.php';

// --- CSRF Token Generation ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];


// --- Security Check ---
$current_page = basename($_SERVER['PHP_SELF']);
// List of public pages that don't require a login
$public_pages = ['login.php', 'login_action.php'];

// 1. Check if user is logged in at all
if (!isset($_SESSION['user_id']) && !in_array($current_page, $public_pages)) {
    header("Location: login.php");
    exit();
}

// 2. Check if a logged-in user has a valid session
if (isset($_SESSION['user_id']) && !isset($_SESSION['division'])) {
    // If user is logged in but has no division, the session is corrupt.
    // Destroy the session and force a re-login.
    session_unset();
    session_destroy();
    header("Location: login.php?error=session");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foila Group CRM</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo">
            <a href="dashboard.php">
                <img src="assets/logo.png" alt="Foila Group Logo">
            </a>
        </div>
        
        <button class="hamburger">&#9776;</button>

        <div class="navbar-collapse">
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="customers.php">Customers</a>
                <a href="jobs.php">Jobs</a>
                <?php // Only show the 'Manage Users' link to Group admins
                if (isset($_SESSION['division']) && $_SESSION['division'] === 'Group'): ?>
                    <a href="users.php">Manage Users</a>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <?php if (isset($_SESSION['username'])): ?>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="btn btn-primary">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">