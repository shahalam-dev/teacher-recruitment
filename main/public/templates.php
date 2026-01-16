<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/AuthService.php';

// 1. Secure the page
AuthService::checkAuth();
$user_id = $_SESSION['user_id'];
$message = "";

// 2. Handle New Group Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $name = trim($_POST['group_name']);

    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO template_groups (group_name, user_id) VALUES (?, ?)");
            $stmt->execute([$name, $user_id]);
            $_SESSION['toast'] = ['message' => "✅ Template Group created successfully!", 'type' => "success"];
        } catch (PDOException $e) {
            $_SESSION['toast'] = ['message' => "❌ Error creating group: " . $e->getMessage(), 'type' => "error"];
        }
    } else {
        $_SESSION['toast'] = ['message' => "❌ Group name cannot be empty.", 'type' => "error"];
    }
    header("Location: templates.php");
    exit;
}

// Handle Group Deletion
if (isset($_GET['delete_group'])) {
    $id = $_GET['delete_group'];
    // Delete messages in group
    $pdo->prepare("DELETE FROM message_templates WHERE group_id = ? AND user_id = ?")->execute([$id, $user_id]);
    // Delete group
    $stmt = $pdo->prepare("DELETE FROM template_groups WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $_SESSION['toast'] = ['message' => "✅ Group deleted successfully!", 'type' => "success"];
    header("Location: templates.php");
    exit;
}

// 3. Fetch Groups with Pagination
$groups = [];
try {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 5;
    $offset = ($page - 1) * $limit;

    // Count total groups
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM template_groups WHERE user_id = ?");
    $countStmt->execute([$user_id]);
    $total_groups = $countStmt->fetchColumn();
    $total_pages = ceil($total_groups / $limit);

    // Fetch groups
    $stmt = $pdo->prepare("SELECT * FROM template_groups WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    if (!isset($_SESSION['toast'])) {
        $_SESSION['toast'] = ['message' => "❌ Error fetching groups: " . $e->getMessage(), 'type' => "error"];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Template Groups | Marketing Manager</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container">
        <h1>📂 Template Groups</h1>

        <div class="card">
            <h2>Create New Group</h2>
            <form method="POST" class="row">
                <input type="hidden" name="create_group" value="1">
                <input type="text" name="group_name" placeholder="e.g., Green Tuition Promo" required class="file-input"
                    style="border: 1px solid #ddd;">
                <button type="submit" name="create_group" class="btn">Create Group</button>
            </form>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h2>Your Groups</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Group Name</th>
                            <th>Variations</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($groups)): ?>
                            <tr>
                                <td colspan="4" style="text-align:center;">No groups found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($groups as $g):
                                $countParams = [$g['id']];
                                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM message_templates WHERE group_id = ?");
                                $countStmt->execute($countParams);
                                $varCount = $countStmt->fetchColumn();
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($g['group_name']); ?></strong></td>
                                    <td><?php echo $varCount; ?> Variations</td>
                                    <td><?php echo $g['created_at']; ?></td>
                                    <td>
                                        <a href="manage_messages.php?group_id=<?php echo $g['id']; ?>"
                                            class="btn secondary">Manage Messages</a>
                                        <a href="#" class="btn"
                                            style="background-color: #e74c3c; padding: 5px 10px; font-size: 0.8rem; margin-left: 5px;"
                                            onclick="event.preventDefault(); Popup.confirm('Delete this group and all its messages?', () => window.location.href='?delete_group=<?php echo $g['id']; ?>', 'Confirm Deletion')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
                <div class="row" style="margin-top: 20px; justify-content: center; align-items: center; gap: 10px;">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="btn secondary" style="padding: 8px 16px;">&laquo; Previous</a>
                    <?php endif; ?>

                    <span class="muted" style="color: var(--muted); font-weight: 500;">
                        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                    </span>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="btn secondary" style="padding: 8px 16px;">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="row" style="margin-top: 20px;">
            <a href="index.php" class="btn">Back to Dashboard</a>
        </div>
    </div>

    <!-- Popup Assets -->
    <link rel="stylesheet" href="assets/popup.css">
    <script src="assets/popup.js"></script>
    <?php if (isset($_SESSION['toast'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Popup.toast('<?php echo $_SESSION['toast']['message']; ?>', '<?php echo $_SESSION['toast']['type']; ?>');
            });
        </script>
        <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>
    <!-- Custom Scripts -->
    <script src="assets/form.js"></script>
</body>

</html>