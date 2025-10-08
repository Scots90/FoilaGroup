<?php
session_start();
require_once 'includes/db_connect.php';

// --- Authorization & Security Checks ---
if (!isset($_SESSION['user_id']) || $_SESSION['division'] !== 'Group') {
    die("Access Denied.");
}

/**
 * Custom CSV writing function to force CRLF (\r\n) line endings.
 */
function fputcsv_crlf($handle, array $fields, $delimiter = ',', $enclosure = '"') {
    $data = '';
    $first = true;
    foreach ($fields as $field) {
        if (!$first) { $data .= $delimiter; }
        $field = (string) $field;
        if (preg_match('/[",\r\n]/', $field)) {
            $field = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
        }
        $data .= $field;
        $first = false;
    }
    fwrite($handle, $data . "\r\n");
}

// --- Fetch only customers that have NOT been exported ---
try {
    $sql = "SELECT * FROM customers WHERE xero_export_status = 'Not Exported' ORDER BY customer_name ASC";
    $stmt = $pdo->query($sql);
    $customers_to_export = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// --- Set HTTP Headers for CSV Download ---
$filename = "XeroContactsExport_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// --- Generate File Output ---
$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF"); // Add UTF-8 BOM

// 1. Define the exact header row from your template
$header = [
    'ContactName', 'AccountNumber', 'EmailAddress', 'FirstName', 'LastName', 'POAttentionTo', 
    'POAddressLine1', 'POAddressLine2', 'POAddressLine3', 'POAddressLine4', 'POCity', 
    'PORegion', 'POPostalCode', 'POCountry', 'SAAttentionTo', 'SAAddressLine1', 'SAAddressLine2', 
    'SAAddressLine3', 'SAAddressLine4', 'SACity', 'SARegion', 'SAPostalCode', 'SACountry', 
    'PhoneNumber', 'FaxNumber', 'MobileNumber', 'DDINumber', 'SkypeName', 'BankAccountName', 
    'BankAccountNumber', 'BankAccountParticulars', 'TaxNumber', 'AccountsReceivableTaxCodeName', 
    'AccountsPayableTaxCodeName', 'Website', 'LegalName', 'Discount', 'CompanyNumber', 
    'DueDateBillDay', 'DueDateBillTerm', 'DueDateSalesDay', 'DueDateSalesTerm', 'SalesAccount', 
    'PurchasesAccount', 'TrackingName1', 'SalesTrackingOption1', 'PurchasesTrackingOption1', 
    'TrackingName2', 'SalesTrackingOption2', 'PurchasesTrackingOption2', 'BrandingTheme', 
    'DefaultTaxBills', 'DefaultTaxSales', 'Person1FirstName', 'Person1LastName', 'Person1Email', 
    'Person1IncludeInEmail', 'Person2FirstName', 'Person2LastName', 'Person2Email', 
    'Person2IncludeInEmail', 'Person3FirstName', 'Person3LastName', 'Person3Email', 
    'Person3IncludeInEmail', 'Person4FirstName', 'Person4LastName', 'Person4Email', 
    'Person4IncludeInEmail', 'Person5FirstName', 'Person5LastName', 'Person5Email', 
    'Person5IncludeInEmail'
];
fputcsv_crlf($output, $header, ',');

// 2. Loop through customers and map data to the correct columns
foreach ($customers_to_export as $customer) {
    // Logic to split the contact name into first and last name
    $contact_parts = explode(' ', $customer['customer_contact_name'], 2);
    $contact_first_name = $contact_parts[0] ?? '';
    $contact_last_name = $contact_parts[1] ?? '';

    $csv_row = [
        /* A */ $customer['customer_name'],           // ContactName
        /* B */ $customer['customer_code'],           // AccountNumber
        /* C */ $customer['customer_email'],          // EmailAddress
        /* D */ $customer['customer_contact_name'],   // FirstName
        /* E */ '',                                   // LastName
        /* F */ $customer['customer_contact_name'],   // POAttentionTo
        /* G */ $customer['customer_address_1'],      // POAddressLine1
        /* H */ $customer['customer_address_2'],      // POAddressLine2
        /* I */ $customer['customer_address_3'],      // POAddressLine3
        /* J */ $customer['customer_address_4'],      // POAddressLine4
        /* K */ '',                                   // POCity
        /* L */ '',                                   // PORegion
        /* M */ $customer['customer_postcode'],       // POPostalCode
        /* N */ '',                                   // POCountry
        /* O */ '', /* P */ '', /* Q */ '', /* R */ '', /* S */ '', /* T */ '', /* U */ '', /* V */ '', /* W */ '', // SAAddress fields (9 empty)
        /* X */ $customer['customer_telephone'],      // PhoneNumber
        /* Y */ '',                                   // FaxNumber
        /* Z */ $customer['customer_mobile'],         // MobileNumber
        /* AA-AV */ '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', // 22 empty columns
        /* AW */ '',                                  // SalesTrackingOption2
        /* AX */ '',                                  // PurchasesTrackingOption2
        /* AY */ '',                                  // BrandingTheme
        /* AZ */ '',                                  // DefaultTaxBills
        /* BA */ '',                                  // DefaultTaxSales
        /* BB */ $contact_first_name,                  // Person1FirstName
        /* BC */ $contact_last_name,                   // Person1LastName
        /* BD */ $customer['customer_email'],          // Person1Email
        /* BE-CT */ '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '' // 20 final empty columns
    ];
    
    fputcsv_crlf($output, $csv_row, ',');
}

fclose($output);

// 3. Update the status of exported customers
$customer_codes = array_column($customers_to_export, 'customer_code');

if (!empty($customer_codes)) {
    try {
        $placeholders = implode(',', array_fill(0, count($customer_codes), '?'));
        $update_sql = "UPDATE customers SET xero_export_status = 'Exported' WHERE customer_code IN ($placeholders)";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute($customer_codes);
    } catch (PDOException $e) {
        error_log("Failed to update customer Xero status after export: " . $e->getMessage());
    }
}

exit();
?>