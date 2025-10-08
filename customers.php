<?php
// Include the header, which handles security, database connection, and the navbar
require_once 'includes/header.php';

// Get the user's division from the session
$user_division = $_SESSION['division'];

// Prepare the SQL filter based on the user's division
$division_filter = "";
$params = [];
if ($user_division !== 'Group') {
    $division_filter = " WHERE customer_division = ? ";
    $params[] = $user_division;
}

// Fetch all customers from the database, including the new xero_export_status column
try {
    $sql = "SELECT customer_code, customer_name, customer_division, customer_contact_name, customer_email, xero_export_status 
            FROM customers" . $division_filter . " ORDER BY xero_export_status ASC, customer_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Could not retrieve customers from database: " . $e->getMessage());
}

// Check for success messages from other actions (create, update, delete)
$status_message = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'created':
            $status_message = '<div class="status-message success">Customer successfully created!</div>';
            break;
        case 'updated':
            $status_message = '<div class="status-message success">Customer successfully updated!</div>';
            break;
        case 'deleted':
            $status_message = '<div class="status-message success">Customer successfully deleted.</div>';
            break;
        case 'error':
            $status_message = '<div class="status-message error">An error occurred. Please try again.</div>';
            break;
    }
}
?>

<div class="page-header">
    <h1>Customer Management</h1>
    <div>
        <?php // Only show export buttons to 'Group' users
        if ($_SESSION['division'] === 'Group'): ?>
            <a href="customer_accounting_export.php" class="btn btn-secondary">Export to Xero</a>
            <a href="customer_export.php?format=csv" class="btn btn-secondary">Export as CSV</a>
            <a href="customer_export.php?format=txt" class="btn btn-secondary">Export as TXT</a>
        <?php endif; ?>
        <a href="customer_form.php" class="btn btn-primary">Add New Customer</a>
    </div>
</div>

<?php echo $status_message; ?>

<div class="table-container">
    <table class="content-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Division</th>
                <th>Contact</th>
                <th>Xero Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No customers found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td data-label="Code"><?php echo htmlspecialchars($customer['customer_code']); ?></td>
                        <td data-label="Name"><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                        <td data-label="Division"><?php echo htmlspecialchars($customer['customer_division']); ?></td>
                        <td data-label="Contact"><?php echo htmlspecialchars($customer['customer_contact_name']); ?></td>
                        <td data-label="Xero Status"><?php echo htmlspecialchars($customer['xero_export_status']); ?></td>
                        <td data-label="Actions" class="table-actions">
                            <a href="customer_form.php?code=<?php echo urlencode($customer['customer_code']); ?>" class="btn-action btn-edit">Edit</a>
                            <form action="customer_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                <input type="hidden" name="code" value="<?php echo htmlspecialchars($customer['customer_code']); ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <button type="submit" class="btn-action btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Include the footer
require_once 'includes/footer.php';
?>