<?php
session_start();
require_once 'includes/db_connect.php';

/**
 * Custom CSV writing function to force CRLF (\r\n) line endings.
 * This is required by the target system's import settings.
 */
function fputcsv_crlf($handle, array $fields, $delimiter = ',', $enclosure = '"') {
    $data = '';
    $first = true;
    foreach ($fields as $field) {
        if (!$first) {
            $data .= $delimiter;
        }
        $field = (string) $field;
        // Enclose field in double quotes if it contains the delimiter, enclosure, or a newline
        if (preg_match('/[",\r\n]/', $field)) {
            $field = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
        }
        $data .= $field;
        $first = false;
    }
    fwrite($handle, $data . "\r\n"); // Force CRLF
}


// --- Security and Division Filtering ---
if (!isset($_SESSION['user_id'])) {
    die("Access denied. You must be logged in to export data.");
}

$user_division = $_SESSION['division'];
$params = [];
$division_filter = "";

if ($user_division !== 'Group') {
    $division_filter = " WHERE c.customer_division = ? ";
    $params[] = $user_division;
}

// --- Fetch Data ---
try {
    $sql = "SELECT j.*, c.customer_name, c.customer_division 
            FROM jobs j 
            JOIN customers c ON j.customer_code = c.customer_code" 
            . $division_filter . 
            " ORDER BY j.delivery_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs_to_export = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// --- Handle File Format ---
$format = $_GET['format'] ?? 'csv';
if ($format === 'txt') {
    $filename = "jobs_" . date('Y-m-d') . ".txt";
    $content_type = 'text/plain; charset=utf-8';
    $delimiter = "\t";
} else {
    $filename = "jobs_" . date('Y-m-d') . ".csv";
    $content_type = 'text/csv; charset=utf-8';
    $delimiter = ","; // Confirmed by settings: Field delimiter is Comma
}

// --- Set HTTP Headers for Download ---
header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . $filename . '"');

// --- Generate File Output ---
$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF"); // Add UTF-8 BOM for maximum compatibility

$header = [
    'Job ID', 'Customer Code', 'Customer Name', 'Order Number', 'Second Reference', 'Third Reference',
    'Collection Address 1', 'Collection Address 2', 'Collection Address 3', 'Collection Address 4', 'Collection Postcode',
    'Collection Date', 'Collection Time Type', 'Collection Time 1', 'Collection Time 2',
    'Delivery Address 1', 'Delivery Address 2', 'Delivery Address 3', 'Delivery Address 4', 'Delivery Postcode',
    'Delivery Date', 'Delivery Time Type', 'Delivery Time 1', 'Delivery Time 2',
    'Goods Description', 'Status', 'Quantity', 'Weight', 'Volume', 'Job Value', 'Division'
];
// Use the new custom function to write the header
fputcsv_crlf($output, $header, $delimiter);

foreach ($jobs_to_export as $row) {
    $csv_row = [
        $row['id'], $row['customer_code'], $row['customer_name'], $row['order_number'], 
        $row['second_reference'], $row['third_reference'], $row['collection_address_1'], 
        $row['collection_address_2'], $row['collection_address_3'], $row['collection_address_4'], 
        $row['collection_postcode'], $row['collection_date'], $row['collection_time_type'], 
        $row['collection_time_1'], $row['collection_time_2'], $row['delivery_address_1'], 
        $row['delivery_address_2'], $row['delivery_address_3'], $row['delivery_address_4'], 
        $row['delivery_postcode'], $row['delivery_date'], $row['delivery_time_type'], 
        $row['delivery_time_1'], $row['delivery_time_2'], $row['goods_description'], 
        $row['status'], $row['quantity'], $row['weight'], $row['volume'], $row['job_value'],
        $row['customer_division']
    ];
    // Use the new custom function to write the data row
    fputcsv_crlf($output, $csv_row, $delimiter);
}

fclose($output);

// --- Update Status of Exported Jobs ---
$job_ids = array_column($jobs_to_export, 'id');
if (!empty($job_ids)) {
    try {
        $placeholders = implode(',', array_fill(0, count($job_ids), '?'));
        $update_sql = "UPDATE jobs SET status = 'Exported to TMS' WHERE id IN ($placeholders)";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute($job_ids);
    } catch (PDOException $e) {
        error_log("Failed to update job statuses after export: " . $e->getMessage());
    }
}

exit();
?>