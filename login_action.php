<?php
session_start();
require_once 'includes/db_connect.php';
date_default_timezone_set('Europe/London');

// --- 1. Basic Validation and Input Check ---
if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_POST['username']) || empty($_POST['password'])) {
    header("Location: login.php");
    exit();
}

// --- 2. Retrieve User Input and IP Address ---
$username = $_POST['username'];
$password = $_POST['password'];
$ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address

// --- NEW: Function to get location from IP address ---
function getLocationFromIp($ip) {
    // Use a free geolocation API (ip-api.com)
    $response = @file_get_contents("http://ip-api.com/json/{$ip}");
    if ($response === false) {
        return "Location lookup failed";
    }
    $data = json_decode($response, true);
    if ($data && $data['status'] === 'success') {
        return "{$data['city']}, {$data['regionName']}, {$data['country']}";
    }
    return "Unknown Location";
}

try {
    // --- 3. Fetch User from Database ---
    $sql = "SELECT id, username, password_hash, division FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- 4. Time-Based Login Restriction ---
    if ($user && $user['division'] !== 'Group') {
        $currentTime = date('H:i');
        if ($currentTime < '07:30' || $currentTime > '17:00') { 
            $location = getLocationFromIp($ip_address);
            $log_sql = "INSERT INTO login_attempts (username, attempt_time, status, ip_address, location) VALUES (?, NOW(), 'Out of hours', ?, ?)";
            $log_stmt = $pdo->prepare($log_sql);
            $log_stmt->execute([$username, $ip_address, $location]);
            
            header("Location: login.php?error=outofhours");
            exit();
        }
    }

    // --- 5. Verify Password and Create Session ---
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['division'] = $user['division'];
        header("Location: dashboard.php");
        exit();
    } else {
        $location = getLocationFromIp($ip_address);
        $log_sql = "INSERT INTO login_attempts (username, attempt_time, status, ip_address, location) VALUES (?, NOW(), 'Invalid credentials', ?, ?)";
        $log_stmt = $pdo->prepare($log_sql);
        $log_stmt->execute([$username, $ip_address, $location]);
        
        header("Location: login.php?error=1");
        exit();
    }

} catch (PDOException $e) {
    error_log("Login action failed: " . $e->getMessage());
    die("A database error occurred. Please try again later.");
}
?>