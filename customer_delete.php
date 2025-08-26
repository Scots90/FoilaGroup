<?php
// Include necessary files for session, security, and DB connection
require_once 'includes/header.php';

// 1. Validate that a customer code has been provided in the URL
if (!isset($_GET['code']) || empty($_GET['code'])) {
    // If no code is provided, redirect back to the customer list
    header("Location: customers.php");
    exit();
}

// 2. Get the customer code from the URL
$customer_code = $_GET['code'];

// 3. Prepare and execute the DELETE statement
try {
    $sql = "DELETE FROM customers WHERE customer_code = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customer_code]);

    // If deletion is successful, redirect with a success message
    header("Location: customers.php?status=deleted");
    exit();

} catch (PDOException $e) {
    // If an error occurs (e.g., a foreign key constraint violation because the customer has jobs),
    // redirect with a generic error message.
    // In production, you would log this error: error_log($e->getMessage());
    header("Location: customers.php?status=error");
    exit();
}
?>