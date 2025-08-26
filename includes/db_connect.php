<?php
// --- Database Connection Settings ---

// The server where your database is located. For XAMPP, this is almost always 'localhost'.
define('DB_HOST', 'localhost');

// The name of your database, as created in phpMyAdmin.
define('DB_NAME', 'foila_group');

// The database user. For a standard XAMPP installation, this is 'root'.
define('DB_USER', 'root');

// The password for the database user. For a standard XAMPP installation, this is empty.
define('DB_PASS', '');

// --- Establish the Connection ---

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);

    // Set the PDO error mode to exception. This will make PDO throw exceptions on errors,
    // which makes debugging much easier.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // If the connection fails, kill the script and display a friendly error message.
    // In a live production environment, you would log this error instead of showing it to the user.
    die("ERROR: Could not connect to the database. " . $e->getMessage());
}
?>