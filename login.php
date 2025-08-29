<?php
session_start();

// If user is already logged in, redirect them to the dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Check for login errors or other status messages
$message = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 1) {
        $message = '<div class="status-message error">Invalid username or password. Please try again.</div>';
    }
    if ($_GET['error'] == 'session') {
        $message = '<div class="status-message error">Your session was invalid. Please log in again.</div>';
    }
    if ($_GET['error'] == 'outofhours') {
        $message = '<div class="status-message error">Access denied. Login is only permitted between 7:30 AM and 5:30 PM.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Foila Group CRM</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Add specific styles for the login page body to center the form */
        body.login-page {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--dark-color);
            }
        </style>
</head>
<body class="login-page">

    <div class="form-container" style="max-width: 400px; width: 100%;">
        <div class="logo" style="background-color: var(--dark-color); text-align: center; margin-bottom: 20px;">
            <img src="assets/logo.png" alt="Foila Group Logo" style="height: 100px; width: auto;">
        </div>
        
        <h2 style="text-align: center; margin-bottom: 20px;">CRM Login</h2>

        <?php echo $message; ?>

        <form action="login_action.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </div>
        </form>
    </div>

</body>
</html>