<?php
// Include the header for security, session, and database connection
require_once 'includes/header.php';

// --- Determine Mode: Create vs. Edit ---
$is_update = false;
$customer = [];
$page_header = "Add Customer";

// The list of available divisions
$divisions = ['Ipswich', 'London', 'Teesside', 'Group'];

// Check if a customer code is passed in the URL (Edit Mode)
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $is_update = true;
    $customer_code = $_GET['code'];
    $page_header = "Edit Customer";

    // Fetch existing customer data from the database
    try {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_code = ?");
        $stmt->execute([$customer_code]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        // If no customer is found with that code, redirect away
        if (!$customer) {
            header("Location: customers.php?status=notfound");
            exit();
        }
    } catch (PDOException $e) {
        die("Error fetching customer data: " . $e->getMessage());
    }
}

// Check for error messages passed in the URL
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'required':
            $error_message = '<div class="status-message error">Please fill in all required fields.</div>';
            break;
        case 'duplicate':
            $error_message = '<div class="status-message error">Error: A customer with the code "' . htmlspecialchars($_GET['code_val']) . '" already exists.</div>';
            break;
    }
}
?>

<div class="page-header">
    <h1><?php echo $page_header; ?></h1>
    <a href="customers.php" class="btn btn-secondary">Back to List</a>
</div>

<?php echo $error_message; ?>

<div class="form-container">
    <form action="customer_action.php" method="POST">
        <input type="hidden" name="original_code" value="<?php echo htmlspecialchars($customer['customer_code'] ?? ''); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

        <div class="form-group">
            <label for="customer_code">Customer Code *</label>
            <input type="text" id="customer_code" name="customer_code" value="<?php echo htmlspecialchars($customer['customer_code'] ?? ''); ?>" maxlength="6" <?php if ($is_update) echo 'readonly'; ?> required>
            <?php if ($is_update): ?>
                <small>The customer code cannot be changed.</small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="customer_name">Customer Name *</label>
            <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($customer['customer_name'] ?? ''); ?>" maxlength="50" required>
        </div>

        <div class="form-group">
            <label for="customer_division">Division *</label>
            <?php if ($_SESSION['division'] === 'Group'): ?>
                <select id="customer_division" name="customer_division" required>
                    <?php foreach ($divisions as $division): ?>
                        <option value="<?php echo $division; ?>" <?php echo (isset($customer['customer_division']) && $customer['customer_division'] == $division) ? 'selected' : ''; ?>>
                            <?php echo $division; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="text" id="customer_division" name="customer_division" value="<?php echo htmlspecialchars($_SESSION['division']); ?>" readonly>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="customer_address_1">Address Line 1 *</label>
            <input type="text" id="customer_address_1" name="customer_address_1" value="<?php echo htmlspecialchars($customer['customer_address_1'] ?? ''); ?>" maxlength="50" required>
        </div>

        <div class="form-group">
            <label for="customer_address_2">Address Line 2</label>
            <input type="text" id="customer_address_2" name="customer_address_2" value="<?php echo htmlspecialchars($customer['customer_address_2'] ?? ''); ?>" maxlength="50">
        </div>

        <div class="form-group">
            <label for="customer_address_3">Address Line 3</label>
            <input type="text" id="customer_address_3" name="customer_address_3" value="<?php echo htmlspecialchars($customer['customer_address_3'] ?? ''); ?>" maxlength="50">
        </div>

        <div class="form-group">
            <label for="customer_address_4">Address Line 4</label>
            <input type="text" id="customer_address_4" name="customer_address_4" value="<?php echo htmlspecialchars($customer['customer_address_4'] ?? ''); ?>" maxlength="50">
        </div>

        <div class="form-group">
            <label for="customer_postcode">Postcode</label>
            <input type="text" id="customer_postcode" name="customer_postcode" value="<?php echo htmlspecialchars($customer['customer_postcode'] ?? ''); ?>" maxlength="15">
        </div>

        <hr style="margin: 20px 0;">

        <div class="form-group">
            <label for="customer_contact_name">Contact Name</label>
            <input type="text" id="customer_contact_name" name="customer_contact_name" value="<?php echo htmlspecialchars($customer['customer_contact_name'] ?? ''); ?>" maxlength="50">
        </div>

        <div class="form-group">
            <label for="customer_telephone">Telephone</label>
            <input type="text" id="customer_telephone" name="customer_telephone" value="<?php echo htmlspecialchars($customer['customer_telephone'] ?? ''); ?>" maxlength="50">
        </div>

        <div class="form-group">
            <label for="customer_mobile">Mobile</label>
            <input type="text" id="customer_mobile" name="customer_mobile" value="<?php echo htmlspecialchars($customer['customer_mobile'] ?? ''); ?>" maxlength="50">
        </div>

        <div class="form-group">
            <label for="customer_email">Email Address</label>
            <input type="email" id="customer_email" name="customer_email" value="<?php echo htmlspecialchars($customer['customer_email'] ?? ''); ?>" maxlength="50">
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary"><?php echo $is_update ? 'Update Customer' : 'Create Customer'; ?></button>
        </div>
    </form>
</div>

<?php
// Include the footer
require_once 'includes/footer.php';
?>