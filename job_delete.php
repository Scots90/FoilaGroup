<?php
require_once 'includes/header.php';

// Check for POST request and validate CSRF token
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die("Invalid request.");
}

// Validate that a job ID has been provided and is numeric
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header("Location: jobs.php");
    exit();
}

$job_id = $_POST['id'];

// Prepare and execute the DELETE statement
try {
    $sql = "DELETE FROM jobs WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$job_id]);

    // If deletion is successful, redirect with a success message
    header("Location: jobs.php?status=deleted");
    exit();

} catch (PDOException $e) {
    // If a database error occurs, redirect with a generic error message.
    error_log("Error deleting job: " . $e->getMessage());
    header("Location: jobs.php?status=error");
    exit();
}
?>