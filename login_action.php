<?php
// Start the session to store user data upon successful login
session_start();

// Include the database connection file
require_once 'includes/db_connect.php';

// --- 1. Basic Validation and Input Check ---

// Ensure the script is accessed via a POST request and that credentials are set
if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_POST['username']) || empty($_POST['password'])) {
    // If not, redirect to the login page
    header("Location: login.php");
    exit();
}

// --- 2. Retrieve and Sanitize User Input ---
$username = $_POST['username'];
$password = $_POST['password'];

// --- 3. Fetch User from Database ---

try {
    // Prepare a statement to select the user by their username
    $sql = "SELECT id, username, password_hash, division FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);

    // Fetch the user record
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- 4. Verify Password and Create Session ---

    // Check if a user was found AND if the provided password matches the stored hash
    if ($user && password_verify($password, $user['password_hash'])) {
        // Password is correct, so start a new session
        
        // Store user data in the session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['division'] = $user['division'];

        // Redirect to the main dashboard
        header("Location: dashboard.php");
        exit();

    } else {
        // No user found or password was incorrect. Redirect back to the login page with an error flag.
        header("Location: login.php?error=1");
        exit();
    }

} catch (PDOException $e) {
    // In case of a database error, redirect with a generic error
    // In a real application, you would log this error: error_log($e->getMessage());
    die("A database error occurred. Please try again later.");
}

?>