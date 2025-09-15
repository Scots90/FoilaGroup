<?php
// Include necessary files for session, security, and DB connection
require_once 'includes/header.php';

// 1. Check if the form was submitted via POST and validate CSRF token
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: jobs.php");
    exit();
}
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die("CSRF validation failed.");
}


// 2. Retrieve all form data from $_POST and trim whitespace
$job_id = trim($_POST['id'] ?? '');
$customer_code = trim($_POST['customer_code'] ?? '');
$order_number = trim($_POST['order_number'] ?? '');
$second_reference = trim($_POST['second_reference'] ?? '');
$third_reference = trim($_POST['third_reference'] ?? '');
$collection_address_1 = trim($_POST['collection_address_1'] ?? '');
$collection_address_2 = trim($_POST['collection_address_2'] ?? '');
$collection_address_3 = trim($_POST['collection_address_3'] ?? '');
$collection_address_4 = trim($_POST['collection_address_4'] ?? '');
$collection_postcode = trim($_POST['collection_postcode'] ?? '');
$collection_date = trim($_POST['collection_date'] ?? '');
$collection_time_type = trim($_POST['collection_time_type'] ?? '');
$collection_time_1 = trim($_POST['collection_time_1'] ?? '');
$collection_time_2 = trim($_POST['collection_time_2'] ?? '');
$delivery_address_1 = trim($_POST['delivery_address_1'] ?? '');
$delivery_address_2 = trim($_POST['delivery_address_2'] ?? '');
$delivery_address_3 = trim($_POST['delivery_address_3'] ?? '');
$delivery_address_4 = trim($_POST['delivery_address_4'] ?? '');
$delivery_postcode = trim($_POST['delivery_postcode'] ?? '');
$delivery_date = trim($_POST['delivery_date'] ?? '');
$delivery_time_type = trim($_POST['delivery_time_type'] ?? '');
$delivery_time_1 = trim($_POST['delivery_time_1'] ?? '');
$delivery_time_2 = trim($_POST['delivery_time_2'] ?? '');
$goods_description = trim($_POST['goods_description'] ?? '');
$status = trim($_POST['status'] ?? 'Booked');
$quantity = trim($_POST['quantity'] ?? 0);
$weight = trim($_POST['weight'] ?? 0);
$volume = trim($_POST['volume'] ?? 0);
$job_value = trim($_POST['job_value'] ?? 0);
$notes = trim($_POST['notes'] ?? '');


// 3. Server-Side Validation
if (empty($customer_code) || empty($collection_address_1) || empty($delivery_address_1) || empty($goods_description)) {
    $_SESSION['old_form_data'] = $_POST;
    header("Location: job_form.php?id=" . urlencode($job_id) . "&error=required");
    exit();
}

if ($collection_date > $delivery_date) {
    $_SESSION['old_form_data'] = $_POST;
    $redirect_url = "job_form.php?error=date";
    if (!empty($job_id)) {
        $redirect_url .= "&id=" . urlencode($job_id);
    }
    header("Location: " . $redirect_url);
    exit();
}

// Handle optional time slot field
if ($collection_time_type !== 'Time Slot') {
    $collection_time_2 = null;
}
if ($delivery_time_type !== 'Time Slot') {
    $delivery_time_2 = null;
}

// 4. Determine if this is an UPDATE or CREATE operation
$is_update = !empty($job_id);

try {
    if ($is_update) {
        // --- UPDATE Operation ---

        // Check current status before updating to enforce the "Exported to TMS" lock
        $check_stmt = $pdo->prepare("SELECT status FROM jobs WHERE id = ?");
        $check_stmt->execute([$job_id]);
        $current_status = $check_stmt->fetchColumn();

        if ($current_status === 'Exported to TMS') {
            $status = 'Exported to TMS';
        }
        
        $sql = "UPDATE jobs SET 
                    customer_code = ?, order_number = ?, second_reference = ?, third_reference = ?,
                    collection_address_1 = ?, collection_address_2 = ?, collection_address_3 = ?, collection_address_4 = ?, collection_postcode = ?,
                    collection_date = ?, collection_time_type = ?, collection_time_1 = ?, collection_time_2 = ?,
                    delivery_address_1 = ?, delivery_address_2 = ?, delivery_address_3 = ?, delivery_address_4 = ?, delivery_postcode = ?,
                    delivery_date = ?, delivery_time_type = ?, delivery_time_1 = ?, delivery_time_2 = ?,
                    goods_description = ?, status = ?, quantity = ?, weight = ?, volume = ?, job_value = ?, notes = ?
                WHERE id = ?";
        
        $params = [
            $customer_code, $order_number, $second_reference, $third_reference,
            $collection_address_1, $collection_address_2, $collection_address_3, $collection_address_4, $collection_postcode,
            $collection_date, $collection_time_type, $collection_time_1, $collection_time_2,
            $delivery_address_1, $delivery_address_2, $delivery_address_3, $delivery_address_4, $delivery_postcode,
            $delivery_date, $delivery_time_type, $delivery_time_1, $delivery_time_2,
            $goods_description, $status, $quantity, $weight, $volume, $job_value,
            $notes,
            $job_id
        ];

    } else {
        // --- CREATE Operation ---
        $sql = "INSERT INTO jobs (
                    customer_code, order_number, second_reference, third_reference,
                    collection_address_1, collection_address_2, collection_address_3, collection_address_4, collection_postcode,
                    collection_date, collection_time_type, collection_time_1, collection_time_2,
                    delivery_address_1, delivery_address_2, delivery_address_3, delivery_address_4, delivery_postcode,
                    delivery_date, delivery_time_type, delivery_time_1, delivery_time_2,
                    goods_description, status, quantity, weight, volume, job_value, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $customer_code, $order_number, $second_reference, $third_reference,
            $collection_address_1, $collection_address_2, $collection_address_3, $collection_address_4, $collection_postcode,
            $collection_date, $collection_time_type, $collection_time_1, $collection_time_2,
            $delivery_address_1, $delivery_address_2, $delivery_address_3, $delivery_address_4, $delivery_postcode,
            $delivery_date, $delivery_time_type, $delivery_time_1, $delivery_time_2,
            $goods_description, $status, $quantity, $weight, $volume, $job_value,
            $notes
        ];
    }
    
    // Execute the prepared statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Redirect with a success message
    $redirect_status = $is_update ? 'updated' : 'created';
    header("Location: jobs.php?status=" . $redirect_status);
    exit();

} catch (PDOException $e) {
    error_log("Job action failed: " . $e->getMessage());
    header("Location: jobs.php?status=error");
    exit();
}
?>