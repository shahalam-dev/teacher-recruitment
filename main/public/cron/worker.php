<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../services/MessengerService.php';

// Parse the channel_id from the command line argument
$options = getopt("", ["channel_id:"]);
$channelId = $options['channel_id'] ?? null;

if (!$channelId) {
    file_put_contents(__DIR__ . '/worker_error.log', "[" . date('Y-m-d H:i:s') . "] No channel ID provided.\n", FILE_APPEND);
    exit("No channel ID provided.\n");
}

function logWorker($message, $channelId)
{
    $logMsg = "[" . date('Y-m-d H:i:s') . "] [Channel $channelId] $message\n";
    file_put_contents(__DIR__ . '/worker.log', $logMsg, FILE_APPEND);
}

try {
    // 1. Get the oldest running campaign for THIS channel
    $campStmt = $pdo->prepare("
        SELECT c.id, c.user_id, c.list_id, c.group_id, ch.api_endpoint, ch.api_key 
        FROM campaigns c
        JOIN channels ch ON c.channel_id = ch.id
        WHERE c.status = 'running' AND c.channel_id = ?
        ORDER BY c.id ASC LIMIT 1
    ");
    $campStmt->execute([$channelId]);
    $campaign = $campStmt->fetch(PDO::FETCH_ASSOC);

    if (!$campaign) {
        // No active campaign for this channel
        exit;
    }

    // 2. Fetch templates
    $msgStmt = $pdo->prepare("SELECT content FROM message_templates WHERE group_id = ?");
    $msgStmt->execute([$campaign['group_id']]);
    $allMessages = $msgStmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($allMessages)) {
        logWorker("No messages found in Group ID {$campaign['group_id']}.", $channelId);
        exit;
    }

    // 3. Fetch ONE recipient
    $recipientStmt = $pdo->prepare("
        SELECT id, phone_number 
        FROM marketing_user_number 
        WHERE list_id = ? AND user_id = ? AND is_sent = 0 
        LIMIT 1
    ");
    $recipientStmt->execute([$campaign['list_id'], $campaign['user_id']]);
    $recipient = $recipientStmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipient) {
        // No more numbers to send? Mark campaign as completed
        $pdo->prepare("UPDATE campaigns SET status = 'completed' WHERE id = ?")->execute([$campaign['id']]);
        logWorker("Campaign {$campaign['id']} completed.", $channelId);
        exit;
    }

    // 4. Process the single recipient
    $phone = $recipient['phone_number'];
    $message = $allMessages[array_rand($allMessages)];

    $response = MessengerService::send(
        $campaign['api_endpoint'],
        $campaign['api_key'],
        $phone,
        $message
    );

    if ($response) {
        $update = $pdo->prepare("UPDATE marketing_user_number SET is_sent = 1 WHERE id = ?");
        $update->execute([$recipient['id']]);
        logWorker("✓ Sent to $phone", $channelId);
    } else {
        $update = $pdo->prepare("UPDATE marketing_user_number SET is_sent = 2 WHERE id = ?");
        $update->execute([$recipient['id']]);
        logWorker("❌ Failed to send to $phone", $channelId);
    }

    // No sleep needed for single execution

} catch (Exception $e) {
    file_put_contents(__DIR__ . '/worker_error.log', "[" . date('Y-m-d H:i:s') . "] Worker $channelId Error: " . $e->getMessage() . "\n", FILE_APPEND);
}
