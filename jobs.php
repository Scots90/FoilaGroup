<?php
// Include the header for security, DB connection, and navigation
require_once 'includes/header.php';

// --- Get current user's division and selected status filter ---
$user_division = $_SESSION['division'];
$status_to_filter = $_GET['filter_status'] ?? ''; // Get status from URL, default to empty

// --- Build SQL Query ---
$params = [];
$sql_where_clauses = [];

// 1. Division Filtering
if ($user_division !== 'Group') {
    $sql_where_clauses[] = "c.customer_division = ?";
    $params[] = $user_division;
}

// 2. Status Filtering
if (!empty($status_to_filter)) {
    $sql_where_clauses[] = "j.status = ?";
    $params[] = $status_to_filter;
}

// Construct the final SQL query
$sql = "SELECT 
            j.id, j.customer_code, c.customer_name, 
            j.order_number, j.delivery_date, j.status 
        FROM jobs j
        JOIN customers c ON j.customer_code = c.customer_code";

if (!empty($sql_where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $sql_where_clauses);
}

$sql .= " ORDER BY j.delivery_date DESC";

// --- Execute Query ---
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Could not retrieve jobs from the database: " . $e->getMessage());
}

// Check for success or error messages from other actions
$status_message = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'created':
            $status_message = '<div class="status-message success">Job successfully created!</div>';
            break;
        case 'updated':
            $status_message = '<div class="status-message success">Job successfully updated!</div>';
            break;
        case 'deleted':
            $status_message = '<div class="status-message success">Job successfully deleted.</div>';
            break;
        case 'error':
            $status_message = '<div class="status-message error">An error occurred. Please try again.</div>';
            break;
    }
}
?>

<div class="page-header">
    <h1>Job Management</h1>
    <div>
        <?php // Only show export buttons to 'Group' users
        if ($_SESSION['division'] === 'Group'): ?>
            <a href="job_export.php?format=csv" class="btn btn-secondary">Export as CSV</a>
            <a href="job_export.php?format=txt" class="btn btn-secondary">Export as TXT</a>
        <?php endif; ?>
        <a href="job_form.php" class="btn btn-primary">Add New Job</a>
    </div>
</div>

<div class="filter-container" style="margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <form action="jobs.php" method="GET" style="display: flex; align-items: center; gap: 15px;">
        <label for="filter_status" style="font-weight: 600;">Filter by Status:</label>
        <select name="filter_status" id="filter_status" onchange="this.form.submit()" style="padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
            <option value="" <?php if (empty($status_to_filter)) echo 'selected'; ?>>All Statuses</option>
            <option value="Booked" <?php if ($status_to_filter == 'Booked') echo 'selected'; ?>>Booked</option>
            <option value="Exported to TMS" <?php if ($status_to_filter == 'Exported to TMS') echo 'selected'; ?>>Exported to TMS</option>
        </select>
    </form>
</div>

<?php echo $status_message; ?>

<div class="table-container">
    <table class="content-table">
        <thead>
            <tr>
                <th>Job ID</th>
                <th>Customer</th>
                <th>Order Number</th>
                <th>Delivery Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($jobs)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No jobs found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td data-label="Job ID"><?php echo htmlspecialchars($job['id']); ?></td>
                        <td data-label="Customer"><?php echo htmlspecialchars($job['customer_name']) . ' (' . htmlspecialchars($job['customer_code']) . ')'; ?></td>
                        <td data-label="Order Number"><?php echo htmlspecialchars($job['order_number']); ?></td>
                        <td data-label="Delivery Date"><?php echo date('d-m-Y', strtotime($job['delivery_date'])); ?></td>
                        <td data-label="Status"><?php echo htmlspecialchars($job['status']); ?></td>
                        <td data-label="Actions" class="table-actions">
                            <a href="job_form.php?id=<?php echo $job['id']; ?>" class="btn-action btn-edit">Edit</a>
                            <form action="job_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this job?');">
                                <input type="hidden" name="id" value="<?php echo $job['id']; ?>">
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