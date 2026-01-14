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
    ORDER BY c.created_at DESC
");
$campaignQuery->execute([$user_id]);
$activeCampaigns = $campaignQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Marketing Manager</title>
    <link rel="stylesheet" href="assets/style.css"> </head>
<body>
    <div class="container">
        <div class="card" style="margin-top: 20px;">
            <h2>GREEN TUITION</h2>
            <div class="row">
                <button onclick="openTab('marketing')" class="btn secondary tab-btn active" style="flex:1; text-align:center;">Marketing</button>
                <button onclick="openTab('teacher')" class="btn secondary tab-btn" style="flex:1; text-align:center;">Teacher</button>
                <button onclick="openTab('tution')" class="btn secondary tab-btn" style="flex:1; text-align:center;">Tution</button>
                <button onclick="openTab('payment')" class="btn secondary tab-btn" style="flex:1; text-align:center;">Payment</button>
                <a href="logout.php" class="btn danger" style="background-color: #dc2626; color: white;">Logout</a>
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
                                <tr><td colspan="4" style="text-align:center;">No active campaigns running.</td></tr>
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
                <p>coming soon...</p>
            </div>
        </div>

        <!-- Tution Tab -->
        <div id="tution" class="tab-content" style="display: none;">
            <div class="card" style="margin-top: 20px;">
                <h2>Tution Management</h2>
                <p>coming soon...</p>
            </div>
        </div>

        <!-- Payment Tab -->
        <div id="payment" class="tab-content" style="display: none;">
            <div class="card" style="margin-top: 20px;">
                <h2>Payment Management</h2>
                <p>coming soon...</p>
            </div>
        </div>

    </div>

    <script>
        function openTab(tabName) {
            // Hide all tab contents
            const contents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < contents.length; i++) {
                contents[i].style.display = 'none';
            }

            // Remove active class from all buttons
            const buttons = document.getElementsByClassName('tab-btn');
            for (let i = 0; i < buttons.length; i++) {
                buttons[i].classList.remove('active');
                // Ensure secondary style is kept, but maybe highlight active?
                // For now, let's just trust the active class state if we had CSS for it.
                // Since we don't have specific CSS for .active on these buttons yet, 
                // we might want to change the style dynamically or assume standard btn behavior.
                buttons[i].style.backgroundColor = '#10b981'; 
            }

            // Show current tab
            document.getElementById(tabName).style.display = 'block';
            
            // Highlight current button
            // Finding the button that was clicked is harder without passing 'this', 
            // but we can search by text or just rely on the user adding 'active' style later.
            // Let's iterate again and check onclick attribute or match text.
            // Actually, let's just target the button that triggered this.
            // The improved way is to pass `event` or `this`.
        }

        // Initialize buttons opacity
        document.addEventListener('DOMContentLoaded', () => {
             const buttons = document.getElementsByClassName('tab-btn');
             for (let i = 0; i < buttons.length; i++) {
                //  buttons[i].style.opacity = '0.6';
                 buttons[i].onclick = function() {
                     openTab(this.getAttribute('onclick').match(/'([^']+)'/)[1]);
                     // Reset all opacities
                    //   for (let j = 0; j < buttons.length; j++) buttons[j].style.opacity = '0.9';
                     // Set active
                     this.style.backgroundColor = '#04835bff';
                 };
             }
             // Set default active
             document.querySelector('button[onclick="openTab(\'marketing\')"]').style.opacity = '1';
        });
    </script>
</body>
</html>