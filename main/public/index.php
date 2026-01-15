<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Services/AuthService.php';

// 1. Secure the page
AuthService::checkAuth();
$user_id = $_SESSION['user_id'];

// 2. Fetch User-Specific Statistics
// Count total sent vs pending for this specific user
$statsQuery = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN is_sent = 1 THEN 1 ELSE 0 END) as sent_count,
        SUM(CASE WHEN is_sent = 0 THEN 1 ELSE 0 END) as pending_count
    FROM marketing_user_number 
    WHERE user_id = ?
");
$statsQuery->execute([$user_id]);
$stats = $statsQuery->fetch(PDO::FETCH_ASSOC);

// 3. Fetch Active Campaigns
$campaignQuery = $pdo->prepare("
    SELECT c.*, ch.name as channel_name, cl.list_name 
    FROM campaigns c
    JOIN channels ch ON c.channel_id = ch.id
    JOIN contact_lists cl ON c.list_id = cl.id
    WHERE c.user_id = ? AND c.status = 'running'
    ORDER BY c.created_at DESC LIMIT 5
");
$campaignQuery->execute([$user_id]);
$activeCampaigns = $campaignQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard | Marketing Manager</title>
    <link rel="stylesheet" href="assets/style.css?v=<?php echo filemtime('assets/style.css'); ?>">
</head>

<body>
    <div class="container">
        <div class="card" style="margin-top: 20px;">
            <div class="row" style="justify-content: space-between; align-items: center;">
                <h2 style="margin: 0; text-align: left;">GREEN TUITION</h2>
                <a href="logout.php" style="color: white; display: flex; align-items: center; text-decoration: none; border: 2px solid #f1310bff; background-color: #ff5454ff; padding: 5px 10px; border-radius: 10px;">
                    <span style="font-size: 14px; font-weight: 600;">Logout</span>
                    <!-- <img src="assets/exit.png" alt="Logout" style="width: 24px; height: 24px; margin-right: 5px;"> -->
                </a>
            </div>
            <div class="row">
                <button onclick="openTab('marketing', this)" class="btn tab-btn active" style="flex:1; text-align:center; background-color: #10b981; transform: scale(1);">Marketing</button>
                <button onclick="openTab('teacher', this)" class="btn tab-btn" style="flex:1; text-align:center; background-color: #3b82f6; transform: scale(1);">Teacher</button>
                <button onclick="openTab('tution', this)" class="btn tab-btn" style="flex:1; text-align:center; background-color: #8b5cf6; transform: scale(1);">Tution</button>
                <button onclick="openTab('payment', this)" class="btn tab-btn" style="flex:1; text-align:center; background-color: #f59e0b; transform: scale(1);">Payment</button>
            </div>
        </div>

        <!-- Marketing Tab -->
        <div id="marketing" class="tab-content">
            <div class="card" style="margin-top: 20px;">
                <h2>🛠 Management Tools</h2>
                <div class="row" style="gap: 10px;">
                    <a href="campaigns.php" class="btn secondary" style="flex:1; text-align:center;">🚀 Campaigns</a>
                    <a href="contacts.php" class="btn secondary" style="flex:1; text-align:center;">📱 Contacts</a>
                    <a href="templates.php" class="btn secondary" style="flex:1; text-align:center;">📝 Templates</a>
                    <a href="channels.php" class="btn secondary" style="flex:1; text-align:center;">🔌 Channels</a>
                </div>
            </div>

            <div class="card" style="margin-top: 20px;">
                <h2>🚀 Active Campaigns</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Campaign Name</th>
                                <th>Channel</th>
                                <th>Target Base</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activeCampaigns)): ?>
                                <tr>
                                    <td colspan="4" style="text-align:center;">No active campaigns running.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($activeCampaigns as $camp): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($camp['campaign_name']); ?></td>
                                        <td><?php echo htmlspecialchars($camp['channel_name']); ?></td>
                                        <td><?php echo htmlspecialchars($camp['list_name']); ?></td>
                                        <td><span class="badge ok">Running</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- <div class="row" style="margin-top: 20px;">
                <a href="campaigns.php" class="btn">Create New Campaign</a>
            </div> -->
        </div>

        <!-- Teacher Tab -->
        <div id="teacher" class="tab-content" style="display: none;">
            <div class="card" style="margin-top: 20px;">
                <h2>Teacher Management</h2>
                <h3 class="text-center text-gray">coming soon...</h3>
            </div>
        </div>

        <!-- Tution Tab -->
        <div id="tution" class="tab-content" style="display: none;">
            <div class="card" style="margin-top: 20px;">
                <h2>Tution Management</h2>
                <h3 class="text-center text-gray">coming soon...</h3>
            </div>
        </div>

        <!-- Payment Tab -->
        <div id="payment" class="tab-content" style="display: none;">
            <div class="card" style="margin-top: 20px;">
                <h2>Payment Management</h2>
                <h3 class="text-center text-gray">coming soon...</h3>
            </div>
        </div>

    </div>

    <script>
        function openTab(tabName, btnElement) {
            // Hide all tab contents
            const contents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < contents.length; i++) {
                contents[i].style.display = 'none';
            }

            // Reset active state (scale) for all buttons
            const buttons = document.getElementsByClassName('tab-btn');
            for (let i = 0; i < buttons.length; i++) {
                buttons[i].classList.remove('active');
                buttons[i].style.transform = 'scale(1)';
            }

            // Show current tab
            document.getElementById(tabName).style.display = 'block';

            // Highlight current button
            if (btnElement) {
                btnElement.classList.add('active');
                btnElement.style.transform = 'scale(1.1)';
            }
        }

        // Initialize - ensure default state matches (already set inline for marketing)
        // No extra JS needed for initialization as inline styles handle default active state
    </script>
</body>

</html>