<?php
require_once 'includes/header.php';

// Check for POST request and validate CSRF token
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die("Invalid request.");
}

// Validate that a customer code has been provided
if (!isset($_POST['code']) || empty($_POST['code'])) {
    header("Location: customers.php");
    exit();
}

$customer_code = $_POST['code'];

// Prepare and execute the DELETE statement
try {
    $sql = "DELETE FROM customers WHERE customer_code = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customer_code]);

    // If deletion is successful, redirect with a success message
    header("Location: customers.php?status=deleted");
    exit();

} catch (PDOException $e) {
    // If an error occurs, redirect with a generic error message.
    error_log("Error deleting customer: " . $e->getMessage());
    header("Location: customers.php?status=error");
    exit();
}
?>