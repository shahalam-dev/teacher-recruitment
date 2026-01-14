<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Services/AuthService.php';

AuthService::checkAuth();
$user_id = $_SESSION['user_id'];
$message = "";

// Validate Group ID
if (!isset($_GET['group_id'])) {
    header("Location: templates.php");
    exit;
}

$group_id = $_GET['group_id'];
$groupStmt = $pdo->prepare("SELECT * FROM template_groups WHERE id = ? AND user_id = ?");
$groupStmt->execute([$group_id, $user_id]);
$group = $groupStmt->fetch(PDO::FETCH_ASSOC);

if (!$group) {
    die("Invalid Group or Access Denied.");
}

// Handle New Message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_message'])) {
    $content = trim($_POST['content']);
    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO message_templates (content, group_id, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$content, $group_id, $user_id]);
        $_SESSION['toast'] = ['message' => "✅ Variation added!", 'type' => "success"];
    }
    header("Location: manage_messages.php?group_id=" . $group_id);
    exit;
}

// Handle Delete Message
if (isset($_GET['delete_msg'])) {
    $msg_id = $_GET['delete_msg'];
    $stmt = $pdo->prepare("DELETE FROM message_templates WHERE id = ? AND group_id = ? AND user_id = ?");
    $stmt->execute([$msg_id, $group_id, $user_id]);
    $_SESSION['toast'] = ['message' => "✅ Variation deleted successfully!", 'type' => "success"];
    header("Location: manage_messages.php?group_id=$group_id");
    exit;
}

// Fetch Messages
$stmt = $pdo->prepare("SELECT * FROM message_templates WHERE group_id = ? ORDER BY id DESC");
$stmt->execute([$group_id]);
$variations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Variations | Marketing Manager</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container">
        <h1>📝 Group: <?php echo htmlspecialchars($group['group_name']); ?></h1>
        <p><a href="templates.php">← Back to Groups</a></p>

        <div class="card">
            <h2>Add New Message Variation</h2>
            <form method="POST">
                <input type="hidden" name="add_message" value="1">
                <div style="margin-bottom:15px;">
                    <label>Message Content (Bengali/English)</label>
                    <textarea name="content" rows="5" required class="file-input"
                        style="width:100%; border:1px solid #ddd; font-family: inherit;"></textarea>
                    <small>Add 5-10 variations to avoid spam detection.</small>
                </div>
                <button type="submit" name="add_message" class="btn">Add Variation</button>
            </form>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h2>Current Variations (<?php echo count($variations); ?>)</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Content</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($variations)): ?>
                            <tr>
                                <td colspan="3" style="text-align:center;">No variations added yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($variations as $var): ?>
                                <tr>
                                    <td>#<?php echo $var['id']; ?></td>
                                    <td style="white-space: pre-wrap;"><?php echo htmlspecialchars($var['content']); ?></td>
                                    <td>
                                        <a href="#" class="btn"
                                            style="background-color: #e74c3c; padding: 5px 10px; font-size: 0.8rem;"
                                            onclick="event.preventDefault(); Popup.confirm('Remove this variation?', () => window.location.href='?group_id=<?php echo $group_id; ?>&delete_msg=<?php echo $var['id']; ?>', 'Confirm Deletion')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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