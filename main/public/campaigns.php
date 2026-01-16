<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/AuthService.php';

// 1. Secure the page
AuthService::checkAuth();
$user_id = $_SESSION['user_id'];
$message = "";

// 2. Handle Campaign Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['launch_campaign'])) {
    $name = trim($_POST['campaign_name']);
    $channel_id = $_POST['channel_id'];
    $list_id = $_POST['list_id'];
    $group_id = $_POST['group_id'];

    if (!empty($name) && !empty($channel_id) && !empty($list_id) && !empty($group_id)) {
        $stmt = $pdo->prepare("INSERT INTO campaigns (campaign_name, channel_id, list_id, group_id, user_id, status) VALUES (?, ?, ?, ?, ?, 'running')");
        $stmt->execute([$name, $channel_id, $list_id, $group_id, $user_id]);
        $_SESSION['toast'] = ['message' => "🚀 Campaign launched successfully! The worker will begin sending shortly.", 'type' => "success"];
    }
    header("Location: campaigns.php");
    exit;
}

// 2.5 Handle Campaign Deletion
if (isset($_GET['delete_campaign'])) {
    $id = $_GET['delete_campaign'];
    $stmt = $pdo->prepare("DELETE FROM campaigns WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $_SESSION['toast'] = ['message' => "✅ Campaign deleted successfully!", 'type' => "success"];
    header("Location: campaigns.php");
    exit;
}

// 3. Fetch User's Data for Dropdowns
$channels = $pdo->prepare("SELECT id, name FROM channels WHERE user_id = ?");
$channels->execute([$user_id]);

$lists = $pdo->prepare("SELECT id, list_name FROM contact_lists WHERE user_id = ?");
$lists->execute([$user_id]);

$groups = $pdo->prepare("SELECT id, group_name FROM template_groups WHERE user_id = ?");
$groups->execute([$user_id]);

// 4. Pagination Setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Count total campaigns
$count_sql = "SELECT COUNT(*) FROM campaigns WHERE user_id = ?";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute([$user_id]);
$total_campaigns = $count_stmt->fetchColumn();
$total_pages = ceil($total_campaigns / $limit);

// 5. Fetch Existing Campaigns with Pagination
$campaigns = $pdo->prepare("SELECT c.*, ch.name as channel_name, cl.list_name 
                            FROM campaigns c 
                            JOIN channels ch ON c.channel_id = ch.id 
                            JOIN contact_lists cl ON c.list_id = cl.id 
                            WHERE c.user_id = :user_id 
                            ORDER BY c.created_at DESC 
                            LIMIT :limit OFFSET :offset");
$campaigns->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$campaigns->bindValue(':limit', $limit, PDO::PARAM_INT);
$campaigns->bindValue(':offset', $offset, PDO::PARAM_INT);
$campaigns->execute();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Campaign Manager | Marketing Manager</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="container">
        <h1>🚀 Campaign</h1>

        <div class="card">
            <h2>Launch New Campaign</h2>
            <form method="POST">
                <input type="hidden" name="launch_campaign" value="1">
                <div style="margin-bottom:15px;">
                    <label>Campaign Title</label>
                    <input type="text" name="campaign_name" placeholder="e.g., Winter Admission Drive" required
                        class="file-input" style="width:100%; border:1px solid #ddd;">
                </div>

                <div class="row">
                    <div style="flex:1;">
                        <label>Select Channel</label>
                        <select name="channel_id" required style="width:100%; padding:10px; border-radius:10px;">
                            <?php foreach ($channels->fetchAll() as $ch): ?>
                                <option value="<?php echo $ch['id']; ?>"><?php echo htmlspecialchars($ch['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Select Contact Base</label>
                        <select name="list_id" required style="width:100%; padding:10px; border-radius:10px;">
                            <?php foreach ($lists->fetchAll() as $li): ?>
                                <option value="<?php echo $li['id']; ?>"><?php echo htmlspecialchars($li['list_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Select Message Group</label>
                        <select name="group_id" required style="width:100%; padding:10px; border-radius:10px;">
                            <?php foreach ($groups->fetchAll() as $gp): ?>
                                <option value="<?php echo $gp['id']; ?>"><?php echo htmlspecialchars($gp['group_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" name="launch_campaign" class="btn" style="margin-top:20px; width:100%;">🚀 Launch
                    Campaign</button>
            </form>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h2>Recent Campaigns</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Channel</th>
                            <th>Base</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($campaigns->fetchAll() as $camp): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($camp['campaign_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($camp['channel_name']); ?></td>
                                <td><?php echo htmlspecialchars($camp['list_name']); ?></td>
                                <td><span class="badge <?php echo $camp['status'] == 'running' ? 'ok' : 'no'; ?>">
                                        <?php echo ucfirst($camp['status']); ?>
                                    </span></td>
                                <td>
                                    <a href="#" class="btn"
                                        style="background-color: #e74c3c; padding: 5px 10px; font-size: 0.8rem;"
                                        onclick="event.preventDefault(); Popup.confirm('Delete this campaign?', () => window.location.href='?delete_campaign=<?php echo $camp['id']; ?>', 'Confirm Deletion')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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