<?php
// Start the session to access session variables
session_start();

// Check if the user's session is active (i.e., they are already logged in)
if (isset($_SESSION['user_id'])) {
    // If logged in, redirect to the main dashboard
    header("Location: dashboard.php");
    exit(); // Always call exit() after a header redirect
} else {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}
?>