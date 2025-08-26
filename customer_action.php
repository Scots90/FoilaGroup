<?php
// Include necessary files for session, security, and DB connection
require_once 'includes/header.php';

// 1. Security Checks
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: customers.php");
    exit();
}
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die("CSRF validation failed. Request rejected.");
}

// 2. Retrieve all form data from $_POST
$customer_code = trim($_POST['customer_code'] ?? '');
$original_code = trim($_POST['original_code'] ?? ''); // Hidden field for updates
$customer_name = trim($_POST['customer_name'] ?? '');
$customer_address_1 = trim($_POST['customer_address_1'] ?? '');
$customer_address_2 = trim($_POST['customer_address_2'] ?? '');
$customer_address_3 = trim($_POST['customer_address_3'] ?? '');
$customer_address_4 = trim($_POST['customer_address_4'] ?? '');
$customer_postcode = trim($_POST['customer_postcode'] ?? '');
$customer_division = trim($_POST['customer_division'] ?? '');
$customer_contact_name = trim($_POST['customer_contact_name'] ?? '');
$customer_telephone = trim($_POST['customer_telephone'] ?? '');
$customer_mobile = trim($_POST['customer_mobile'] ?? '');
$customer_email = trim($_POST['customer_email'] ?? '');

// 3. Basic Server-Side Validation
if (empty($customer_code) || empty($customer_name) || empty($customer_address_1) || empty($customer_division)) {
    header("Location: customer_form.php?code=" . urlencode($original_code) . "&error=required");
    exit();
}

// 4. Determine if this is an UPDATE or CREATE operation
$is_update = !empty($original_code);

try {
    if ($is_update) {
        // --- UPDATE Operation ---
        $sql = "UPDATE customers SET 
                    customer_code = ?, customer_name = ?, customer_address_1 = ?, 
                    customer_address_2 = ?, customer_address_3 = ?, customer_address_4 = ?, 
                    customer_postcode = ?, customer_division = ?, customer_contact_name = ?, 
                    customer_telephone = ?, customer_mobile = ?, customer_email = ?
                WHERE customer_code = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $customer_code, $customer_name, $customer_address_1,
            $customer_address_2, $customer_address_3, $customer_address_4,
            $customer_postcode, $customer_division, $customer_contact_name,
            $customer_telephone, $customer_mobile, $customer_email,
            $original_code // Use the original code in the WHERE clause
        ]);

        header("Location: customers.php?status=updated");
        exit();

    } else {
        // --- CREATE Operation ---
        $sql = "INSERT INTO customers (
                    customer_code, customer_name, customer_address_1, 
                    customer_address_2, customer_address_3, customer_address_4, 
                    customer_postcode, customer_division, customer_contact_name, 
                    customer_telephone, customer_mobile, customer_email
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $customer_code, $customer_name, $customer_address_1,
            $customer_address_2, $customer_address_3, $customer_address_4,
            $customer_postcode, $customer_division, $customer_contact_name,
            $customer_telephone, $customer_mobile, $customer_email
        ]);
        
        header("Location: customers.php?status=created");
        exit();
    }
} catch (PDOException $e) {
    if ($e->getCode() == '23000') { // Integrity constraint violation (e.g., duplicate code)
        header("Location: customer_form.php?error=duplicate&code_val=" . urlencode($customer_code));
        exit();
    } else {
        error_log("Customer action failed: " . $e->getMessage());
        header("Location: customers.php?status=error");
        exit();
    }
}
?>