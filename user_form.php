<?php
require_once 'includes/header.php';

// --- Authorization Check: Only 'Group' users can access this page ---
if ($_SESSION['division'] !== 'Group') {
    echo "<div class='status-message error'>Access Denied: You do not have permission to view this page.</div>";
    require_once 'includes/footer.php';
    exit();
}

// --- Mode Determination: Create vs. Edit ---
$is_update = false;
$user = [];
$page_header = "Add New User";

// Check if a user ID is passed in the URL (Edit Mode)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_update = true;
    $user_id = $_GET['id'];
    $page_header = "Edit User";

    try {
        $stmt = $pdo->prepare("SELECT id, username, division FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // If no user is found with that ID, redirect to the list
        if (!$user) { 
            header("Location: users.php"); 
            exit(); 
        }
    } catch (PDOException $e) {
        die("Error fetching user data: " . $e->getMessage());
    }
}

// Define the list of available divisions
$divisions = ['Ipswich', 'London', 'Teesside', 'Group'];
?>

<div class="page-header">
    <h1><?php echo $page_header; ?></h1>
    <a href="users.php" class="btn btn-secondary">Back to List</a>
</div>

<div class="form-container" style="max-width: 500px;">
    <form action="user_action.php" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id'] ?? ''); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

        <div class="form-group">
            <label for="username">Username *</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password <?php if (!$is_update) echo '*'; ?></label>
            <input type="password" id="password" name="password" <?php if (!$is_update) echo 'required'; ?>>
            <?php if ($is_update): ?>
                <small>Leave blank to keep the current password.</small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="division">Division *</label>
            <select id="division" name="division" required>
                <?php foreach ($divisions as $division): ?>
                    <option value="<?php echo $division; ?>" <?php echo (isset($user['division']) && $user['division'] == $division) ? 'selected' : ''; ?>>
                        <?php echo $division; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary"><?php echo $is_update ? 'Update User' : 'Create User'; ?></button>
        </div>
    </form>
</div>

<?php
require_once 'includes/footer.php';
?>