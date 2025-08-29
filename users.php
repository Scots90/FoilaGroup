<?php
require_once 'includes/header.php';

// --- Authorization Check: Only 'Group' users can access this page ---
if ($_SESSION['division'] !== 'Group') {
    echo "<div class='status-message error'>Access Denied: You do not have permission to view this page.</div>";
    require_once 'includes/footer.php';
    exit();
}

// Fetch all users and recent login attempts
try {
    $sql_users = "SELECT id, username, division FROM users ORDER BY username ASC";
    $stmt_users = $pdo->query($sql_users);
    $users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

    $sql_attempts = "SELECT username, attempt_time, status, ip_address, location FROM login_attempts ORDER BY attempt_time DESC LIMIT 50";
    $stmt_attempts = $pdo->query($sql_attempts);
    $login_attempts = $stmt_attempts->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Could not retrieve data from the database: " . $e->getMessage());
}

// Check for status messages
$status_message = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'created':
            $status_message = '<div class="status-message success">User successfully created!</div>';
            break;
        case 'updated':
            $status_message = '<div class="status-message success">User successfully updated!</div>';
            break;
        case 'deleted':
            $status_message = '<div class="status-message success">User successfully deleted.</div>';
            break;
        case 'error':
            $status_message = '<div class="status-message error">An error occurred. Please try again.</div>';
            break;
    }
}
?>

<div class="page-header">
    <h1>User Management</h1>
    <div>
        <a href="user_form.php" class="btn btn-primary">Add New User</a>
    </div>
</div>

<?php echo $status_message; ?>

<div class="table-container">
    <table class="content-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Division</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td data-label="ID"><?php echo htmlspecialchars($user['id']); ?></td>
                    <td data-label="Username"><?php echo htmlspecialchars($user['username']); ?></td>
                    <td data-label="Division"><?php echo htmlspecialchars($user['division']); ?></td>
                    <td data-label="Actions" class="table-actions">
                        <a href="user_form.php?id=<?php echo $user['id']; ?>" class="btn-action btn-edit">Edit</a>
                        <?php // Prevent an admin from deleting their own account
                        if ($user['id'] !== $_SESSION['user_id']): ?>
                            <form action="user_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <button type="submit" class="btn-action btn-delete">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="page-header" style="margin-top: 40px;">
    <h2>Recent Failed Login Attempts</h2>
</div>
<div class="table-container">
    <table class="content-table">
        <thead>
            <tr>
                <th>Username Attempted</th>
                <th>Time of Attempt</th>
                <th>Reason</th>
                <th>IP Address</th>
                <th>Approx. Location</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($login_attempts)): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No failed login attempts recorded.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($login_attempts as $attempt): ?>
                    <tr>
                        <td data-label="Username"><?php echo htmlspecialchars($attempt['username']); ?></td>
                        <td data-label="Time"><?php echo date('d-m-Y H:i:s', strtotime($attempt['attempt_time'])); ?></td>
                        <td data-label="Reason"><?php echo htmlspecialchars($attempt['status']); ?></td>
                        <td data-label="IP Address"><?php echo htmlspecialchars($attempt['ip_address']); ?></td>
                        <td data-label="Location"><?php echo htmlspecialchars($attempt['location']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once 'includes/footer.php';
?>