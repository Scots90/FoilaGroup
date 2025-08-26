<?php
// Include necessary files for session, security, and DB connection
require_once 'includes/header.php';

// 1. Validate that a job ID has been provided in the URL and is numeric
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // If no valid ID is provided, redirect back to the job list
    header("Location: jobs.php");
    exit();
}

// 2. Get the job ID from the URL
$job_id = $_GET['id'];

// 3. Prepare and execute the DELETE statement
try {
    $sql = "DELETE FROM jobs WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$job_id]);

    // If deletion is successful, redirect with a success message
    header("Location: jobs.php?status=deleted");
    exit();

} catch (PDOException $e) {
    // If a database error occurs, redirect with a generic error message.
    // In production, you would log this error: error_log($e->getMessage());
    header("Location: jobs.php?status=error");
    exit();
}
?>