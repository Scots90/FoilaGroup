<?php
// Include the header, which handles session start, security, and the navbar
require_once 'includes/header.php';

// Get the user's division from the session for filtering queries
$user_division = $_SESSION['division'];

// --- Prepare SQL Filters based on Division ---
$division_filter_customers = "";
$division_filter_jobs = "";
$params = [];

if ($user_division !== 'Group') {
    // SQL filter for the customers table
    $division_filter_customers = " WHERE customer_division = ? ";
    
    // SQL filter for the jobs table (requires joining with customers)
    $division_filter_jobs = " WHERE c.customer_division = ? ";
    
    // The parameter to bind to the queries
    $params = [$user_division];
}

// --- KPI Calculation Functions ---

/**
 * A helper function to execute a COUNT query and return the result.
 * @param PDO $pdo The database connection object.
 * @param string $sql The SQL query to execute.
 * @param array $params The parameters to bind to the query.
 * @return int The count result.
 */
function getCount(PDO $pdo, string $sql, array $params = []): int {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}


// 1. Total Customers
$sql_customers = "SELECT COUNT(*) FROM customers" . $division_filter_customers;
$total_customers = getCount($pdo, $sql_customers, $params);

// 2. Total Jobs (All Time)
$sql_total_jobs = "SELECT COUNT(j.id) FROM jobs j JOIN customers c ON j.customer_code = c.customer_code" . $division_filter_jobs;
$total_jobs = getCount($pdo, $sql_total_jobs, $params);

// 3. Jobs for Today
$sql_jobs_today = "SELECT COUNT(j.id) FROM jobs j JOIN customers c ON j.customer_code = c.customer_code" . $division_filter_jobs . " AND j.delivery_date = CURDATE()";
$jobs_today = getCount($pdo, $sql_jobs_today, $params);

// 4. Jobs for Tomorrow
$sql_jobs_tomorrow = "SELECT COUNT(j.id) FROM jobs j JOIN customers c ON j.customer_code = c.customer_code" . $division_filter_jobs . " AND j.delivery_date = CURDATE() + INTERVAL 1 DAY";
$jobs_tomorrow = getCount($pdo, $sql_jobs_tomorrow, $params);

// 5. Jobs in the Next 7 Days
$sql_jobs_7_days = "SELECT COUNT(j.id) FROM jobs j JOIN customers c ON j.customer_code = c.customer_code" . $division_filter_jobs . " AND j.delivery_date BETWEEN CURDATE() AND CURDATE() + INTERVAL 6 DAY";
$jobs_7_days = getCount($pdo, $sql_jobs_7_days, $params);

// 6. Jobs in the Next 30 Days
$sql_jobs_30_days = "SELECT COUNT(j.id) FROM jobs j JOIN customers c ON j.customer_code = c.customer_code" . $division_filter_jobs . " AND j.delivery_date BETWEEN CURDATE() AND CURDATE() + INTERVAL 29 DAY";
$jobs_30_days = getCount($pdo, $sql_jobs_30_days, $params);

?>

<div class="page-header">
    <h1>Dashboard</h1>
</div>

<div class="dashboard-grid">

    <div class="stat-card">
        <h3>Total Customers</h3>
        <p><?php echo $total_customers; ?></p>
    </div>

    <div class="stat-card">
        <h3>Total Jobs</h3>
        <p><?php echo $total_jobs; ?></p>
    </div>

    <div class="stat-card">
        <h3>Jobs Today</h3>
        <p><?php echo $jobs_today; ?></p>
    </div>

    <div class="stat-card">
        <h3>Jobs Tomorrow</h3>
        <p><?php echo $jobs_tomorrow; ?></p>
    </div>

    <div class="stat-card">
        <h3>Jobs - Next 7 Days</h3>
        <p><?php echo $jobs_7_days; ?></p>
    </div>

    <div class="stat-card">
        <h3>Jobs - Next 30 Days</h3>
        <p><?php echo $jobs_30_days; ?></p>
    </div>

</div>

<?php
// Include the footer to close the HTML document
require_once 'includes/footer.php';
?>