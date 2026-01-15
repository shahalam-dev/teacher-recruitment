<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Services/AuthService.php';

// 1. Secure the page
AuthService::checkAuth();
$user_id = $_SESSION['user_id'];
$message = "";

// Delete Logic
if (isset($_GET['delete_channel'])) {
    $id = $_GET['delete_channel'];
    $stmt = $pdo->prepare("DELETE FROM channels WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $_SESSION['toast'] = ['message' => "✅ Channel deleted successfully!", 'type' => "success"];
    header("Location: channels.php");
    exit;
}

// 2. Handle New Channel Addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_channel'])) {
    $name = trim($_POST['channel_name']);
    $endpoint = trim($_POST['api_endpoint']);
    $api_key = trim($_POST['api_key']);

    if (!empty($name) && !empty($endpoint) && !empty($api_key)) {
        $stmt = $pdo->prepare("INSERT INTO channels (name, api_endpoint, api_key, user_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $endpoint, $api_key, $user_id]);
        $_SESSION['toast'] = ['message' => "✅ Channel added successfully!", 'type' => "success"];
    } else {
        $_SESSION['toast'] = ['message' => "❌ All fields are required.", 'type' => "error"];
    }
    header("Location: channels.php");
    exit;
}

// 3. Fetch User's Channels with Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Count total channels
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM channels WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$total_channels = $count_stmt->fetchColumn();
$total_pages = ceil($total_channels / $limit);

// Fetch channels
$stmt = $pdo->prepare("SELECT * FROM channels WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$myChannels = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage API Channels | Marketing Manager</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container">
        <h1>🔌 API Channel Management</h1>

        <div class="card">
            <h2>Add New Sending Account</h2>
            <form method="POST">
                <input type="hidden" name="add_channel" value="1">
                <div style="margin-bottom:15px;">
                    <label>Channel Name (e.g., Account 1)</label>
                    <input type="text" name="channel_name" required class="file-input" style="width:100%; border:1px solid #ddd;">
                </div>
                <div style="margin-bottom:15px;">
                    <label>API Endpoint URL</label>
                    <input type="url" name="api_endpoint" value="https://api.360messenger.com/v2/sendMessage" required class="file-input" style="width:100%; border:1px solid #ddd;">
                </div>
                <div style="margin-bottom:15px;">
                    <label>API Key / Token</label>
                    <input type="text" name="api_key" required class="file-input" style="width:100%; border:1px solid #ddd;">
                </div>
                <button type="submit" name="add_channel" class="btn">Save Channel</button>
            </form>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h2>Your Configured Channels</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Account Name</th>
                            <th>Endpoint</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($myChannels)): ?>
                            <tr>
                                <td colspan="4" style="text-align:center;">No channels configured yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($myChannels as $chan): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($chan['name']); ?></td>
                                    <td><code><?php echo htmlspecialchars($chan['api_endpoint']); ?></code></td>
                                    <td><span class="badge ok">Active</span></td>
                                    <td><?php echo $chan['created_at']; ?></td>
                                    <td>
                                        <a href="#" class="btn" style="background-color: #e74c3c; padding: 5px 10px; font-size: 0.8rem;" onclick="event.preventDefault(); Popup.confirm('Delete this channel?', () => window.location.href='?delete_channel=<?php echo $chan['id']; ?>', 'Confirm Deletion')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <?php if (isset($total_pages) && $total_pages > 1): ?>
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