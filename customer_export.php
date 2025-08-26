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
    $division_filter = " WHERE customer_division = ? ";
    $params[] = $user_division;
}

// --- Fetch Data from Database ---
try {
    $sql = "SELECT * FROM customers" . $division_filter . " ORDER BY customer_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// --- Handle File Format ---
$format = $_GET['format'] ?? 'csv';
if ($format === 'txt') {
    $filename = "customers_" . date('Y-m-d') . ".txt";
    $content_type = 'text/plain; charset=utf-8';
    $delimiter = "\t";
} else {
    $filename = "customers_" . date('Y-m-d') . ".csv";
    $content_type = 'text/csv; charset=utf-8';
    $delimiter = ",";
}

// --- Set HTTP Headers for Download ---
header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . $filename . '"');

// --- Generate File Output ---
$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF"); // Add UTF-8 BOM

$header = [
    'Customer Code', 'Customer Name', 'Customer Address 1', 'Customer Address 2',
    'Customer Address 3', 'Customer Address 4', 'Customer Postcode', 'Customer Division',
    'Customer Contact Name', 'Customer Telephone', 'Customer Mobile', 'Customer Email'
];
// Use the new custom function to write the header
fputcsv_crlf($output, $header, $delimiter);

// Loop through the database results and write each row
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Use the new custom function to write the data row
    fputcsv_crlf($output, $row, $delimiter);
}

fclose($output);
exit();
?>