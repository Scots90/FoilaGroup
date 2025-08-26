<?php
// Include the header for security, session, and database connection
require_once 'includes/header.php';

// --- Initial Setup & Mode Determination ---
$is_update = false;
$job = [];
$page_header = "Add New Job";
$submit_button_text = "Create Job";

// Check if a job ID is passed in the URL (which means we are in Edit Mode)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_update = true;
    $job_id = $_GET['id'];
    $page_header = "Edit Job #" . $job_id;
    $submit_button_text = "Update Job";

    // Fetch the existing job data
    try {
        $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
        $stmt->execute([$job_id]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$job) {
            header("Location: jobs.php?status=notfound");
            exit();
        }
    } catch (PDOException $e) {
        die("Error fetching job data: " . $e->getMessage());
    }
}

// --- Fetch Data for Dropdowns ---
// 1. Fetch the list of customers the user is allowed to see
$user_division = $_SESSION['division'];
$customers = [];
$division_filter = "";
$params = [];
if ($user_division !== 'Group') {
    $division_filter = " WHERE customer_division = ? ";
    $params[] = $user_division;
}
try {
    $sql = "SELECT customer_code, customer_name FROM customers" . $division_filter . " ORDER BY customer_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching customer list: " . $e->getMessage());
}

// 2. Define options for ENUM fields
$time_types = ['Fixed', 'None', 'Booked In', 'Time Slot', 'AM', 'PM'];
$job_statuses = ['Booked', 'In Process']; // Status list for the form

// --- Handle Error Messages ---
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'required':
            $error_message = '<div class="status-message error">Please fill in all required fields.</div>';
            break;
        case 'date':
            $error_message = '<div class="status-message error">Validation Error: The collection date cannot be after the delivery date.</div>';
            break;
    }
}
?>

<div class="page-header">
    <h1><?php echo $page_header; ?></h1>
    <a href="jobs.php" class="btn btn-secondary">Back to List</a>
</div>

<?php echo $error_message; ?>

<div class="form-container">
    <form action="job_action.php" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($job['id'] ?? ''); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

        <div class="form-grid">
            <div class="form-group full-width">
                <h3>Job Details</h3>
            </div>

            <div class="form-group">
                <label for="customer_code">Customer *</label>
                <select id="customer_code" name="customer_code" required>
                    <option value="">-- Select a Customer --</option>
                    <?php foreach ($customers as $customer_item): ?>
                        <option value="<?php echo htmlspecialchars($customer_item['customer_code']); ?>" <?php echo (isset($job['customer_code']) && $job['customer_code'] == $customer_item['customer_code']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer_item['customer_name']) . ' (' . htmlspecialchars($customer_item['customer_code']) . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status">Job Status *</label>
                <?php if (isset($job['status']) && $job['status'] === 'Exported to TMS'): ?>
                    <input type="text" value="Exported to TMS" readonly style="background-color: #e9ecef;">
                    <input type="hidden" name="status" value="Exported to TMS">
                <?php else: ?>
                    <select id="status" name="status" required>
                        <?php foreach ($job_statuses as $status): ?>
                            <option value="<?php echo $status; ?>" <?php echo (isset($job['status']) && $job['status'] == $status) ? 'selected' : ''; ?>>
                                <?php echo $status; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="order_number">Order Number</label>
                <input type="text" id="order_number" name="order_number" value="<?php echo htmlspecialchars($job['order_number'] ?? ''); ?>" maxlength="50">
            </div>

            <div class="form-group">
                <label for="second_reference">Second Reference</label>
                <input type="text" id="second_reference" name="second_reference" value="<?php echo htmlspecialchars($job['second_reference'] ?? ''); ?>" maxlength="50">
            </div>

            <div class="form-group">
                <label for="third_reference">Third Reference</label>
                <input type="text" id="third_reference" name="third_reference" value="<?php echo htmlspecialchars($job['third_reference'] ?? ''); ?>" maxlength="50">
            </div>

            <hr class="full-width">
            <div class="form-group full-width">
                <h3>Collection Details</h3>
            </div>
            
            <div class="form-group">
                <label for="collection_address_1">Address 1 *</label>
                <input type="text" id="collection_address_1" name="collection_address_1" value="<?php echo htmlspecialchars($job['collection_address_1'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="collection_address_2">Address 2</label>
                <input type="text" id="collection_address_2" name="collection_address_2" value="<?php echo htmlspecialchars($job['collection_address_2'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="collection_address_3">Address 3</label>
                <input type="text" id="collection_address_3" name="collection_address_3" value="<?php echo htmlspecialchars($job['collection_address_3'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="collection_address_4">Address 4</label>
                <input type="text" id="collection_address_4" name="collection_address_4" value="<?php echo htmlspecialchars($job['collection_address_4'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="collection_postcode">Postcode</label>
                <input type="text" id="collection_postcode" name="collection_postcode" value="<?php echo htmlspecialchars($job['collection_postcode'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="collection_date">Date *</label>
                <input type="date" id="collection_date" name="collection_date" value="<?php echo htmlspecialchars($job['collection_date'] ?? date('Y-m-d')); ?>" required>
            </div>
            <div class="form-group">
                <label for="collection_time_type">Time Type *</label>
                <select id="collection_time_type" name="collection_time_type" required>
                    <?php foreach ($time_types as $type): ?>
                        <option value="<?php echo $type; ?>" <?php echo (isset($job['collection_time_type']) && $job['collection_time_type'] == $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="collection_time_1">Time 1 *</label>
                <input type="time" id="collection_time_1" name="collection_time_1" value="<?php echo htmlspecialchars($job['collection_time_1'] ?? '00:00'); ?>" required>
            </div>
            <div class="form-group" id="collection_time_2_group" style="display: none;">
                <label for="collection_time_2">Time 2 (for Time Slot)</label>
                <input type="time" id="collection_time_2" name="collection_time_2" value="<?php echo htmlspecialchars($job['collection_time_2'] ?? '23:59'); ?>">
            </div>

            <hr class="full-width">
            <div class="form-group full-width">
                <h3>Delivery Details</h3>
            </div>

            <div class="form-group">
                <label for="delivery_address_1">Address 1 *</label>
                <input type="text" id="delivery_address_1" name="delivery_address_1" value="<?php echo htmlspecialchars($job['delivery_address_1'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="delivery_address_2">Address 2</label>
                <input type="text" id="delivery_address_2" name="delivery_address_2" value="<?php echo htmlspecialchars($job['delivery_address_2'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="delivery_address_3">Address 3</label>
                <input type="text" id="delivery_address_3" name="delivery_address_3" value="<?php echo htmlspecialchars($job['delivery_address_3'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="delivery_address_4">Address 4</label>
                <input type="text" id="delivery_address_4" name="delivery_address_4" value="<?php echo htmlspecialchars($job['delivery_address_4'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="delivery_postcode">Postcode</label>
                <input type="text" id="delivery_postcode" name="delivery_postcode" value="<?php echo htmlspecialchars($job['delivery_postcode'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="delivery_date">Date *</label>
                <input type="date" id="delivery_date" name="delivery_date" value="<?php echo htmlspecialchars($job['delivery_date'] ?? date('Y-m-d')); ?>" required>
            </div>
            <div class="form-group">
                <label for="delivery_time_type">Time Type *</label>
                <select id="delivery_time_type" name="delivery_time_type" required>
                    <?php foreach ($time_types as $type): ?>
                        <option value="<?php echo $type; ?>" <?php echo (isset($job['delivery_time_type']) && $job['delivery_time_type'] == $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="delivery_time_1">Time 1 *</label>
                <input type="time" id="delivery_time_1" name="delivery_time_1" value="<?php echo htmlspecialchars($job['delivery_time_1'] ?? '00:00'); ?>" required>
            </div>
             <div class="form-group" id="delivery_time_2_group" style="display: none;">
                <label for="delivery_time_2">Time 2 (for Time Slot)</label>
                <input type="time" id="delivery_time_2" name="delivery_time_2" value="<?php echo htmlspecialchars($job['delivery_time_2'] ?? '23:59'); ?>">
            </div>

            <hr class="full-width">
            <div class="form-group full-width">
                <h3>Goods Details</h3>
            </div>

            <div class="form-group full-width">
                <label for="goods_description">Description *</label>
                <textarea id="goods_description" name="goods_description" rows="4" required><?php echo htmlspecialchars($job['goods_description'] ?? ''); ?></textarea>
            </div>
             <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" step="0.001" value="<?php echo htmlspecialchars($job['quantity'] ?? '0.000'); ?>">
            </div>
            <div class="form-group">
                <label for="weight">Weight (kg)</label>
                <input type="number" id="weight" name="weight" step="0.001" value="<?php echo htmlspecialchars($job['weight'] ?? '0.000'); ?>">
            </div>
            <div class="form-group">
                <label for="volume">Volume (m³)</label>
                <input type="number" id="volume" name="volume" step="0.001" value="<?php echo htmlspecialchars($job['volume'] ?? '0.000'); ?>">
            </div>
             <div class="form-group">
                <label for="job_value">Value (£)</label>
                <input type="number" id="job_value" name="job_value" step="0.001" value="<?php echo htmlspecialchars($job['job_value'] ?? '0.000'); ?>">
            </div>
            
            <hr class="full-width">
            <div class="form-group full-width">
                <h3>Notes</h3>
                <textarea id="notes" name="notes" rows="4"><?php echo htmlspecialchars($job['notes'] ?? ''); ?></textarea>
            </div>

            <div class="form-group full-width">
                <button type="submit" class="btn btn-primary"><?php echo $submit_button_text; ?></button>
            </div>
        </div>
    </form>
</div>

<script>
// JavaScript to dynamically show/hide the 'Time 2' fields
document.addEventListener('DOMContentLoaded', function() {
    const collectionTimeType = document.getElementById('collection_time_type');
    const collectionTime2Group = document.getElementById('collection_time_2_group');
    const deliveryTimeType = document.getElementById('delivery_time_type');
    const deliveryTime2Group = document.getElementById('delivery_time_2_group');

    function toggleTime2Field(typeSelect, time2Group) {
        if (!typeSelect || !time2Group) return; // Defensive check
        if (typeSelect.value === 'Time Slot') {
            time2Group.style.display = 'block';
        } else {
            time2Group.style.display = 'none';
        }
    }

    // Add event listeners
    collectionTimeType.addEventListener('change', () => toggleTime2Field(collectionTimeType, collectionTime2Group));
    deliveryTimeType.addEventListener('change', () => toggleTime2Field(deliveryTimeType, deliveryTime2Group));

    // Run on page load to set the initial state
    toggleTime2Field(collectionTimeType, collectionTime2Group);
    toggleTime2Field(deliveryTimeType, deliveryTime2Group);
});
</script>

<?php
// Include the footer
require_once 'includes/footer.php';
?>