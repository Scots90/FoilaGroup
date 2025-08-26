<?php
// --- PRODUCTION SETTINGS ---
ini_set('display_errors', '0'); // Don't show errors to the user
ini_set('log_errors', '1'); // Log errors to a file
// IMPORTANT: Set the path below to a private directory on your live server.
ini_set('error_log', '/path/on/your/server/php-error.log'); 
error_reporting(E_ALL);


// --- Database Connection Settings ---
// IMPORTANT: Replace these with your live database credentials from your web host.
define('DB_HOST', 'localhost'); // This is often correct, but might be different.
define('DB_NAME', 'foila_group');
define('DB_USER', 'root'); //Enter your database username
define('DB_PASS', ''); //Enter your database password


// --- Establish the Connection ---
try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);

    // Set the PDO error mode to exception.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Log the detailed error to your private error log.
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Show a generic, user-friendly message on screen.
    die("ERROR: A database connection error occurred. Please try again later.");
}
?>