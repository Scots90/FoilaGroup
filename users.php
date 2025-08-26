<?php
require_once 'includes/header.php';

// --- Authorization Check: Only 'Group' users can access this page ---
if ($_SESSION['division'] !== 'Group') {
    echo "<div class='status-message error'>Access Denied: You do not have permission to view this page.</div>";
    require_once 'includes/footer.php';
    exit();
}

// Fetch all users from the database (except their password hashes)
try {
    $sql = "SELECT id, username, division FROM users ORDER BY username ASC";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Could not retrieve users from the database: " . $e->getMessage());
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

<?php
require_once 'includes/footer.php';
?>