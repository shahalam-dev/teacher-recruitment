<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/Services/MessengerService.php';

// Parse the channel_id from the command line argument
$options = getopt("", ["channel_id:"]);
$channelId = $options['channel_id'] ?? null;

if (!$channelId)
    exit("No channel ID provided.\n");

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

    if (!$campaign)
        exit;

    // 2. Fetch templates
    $msgStmt = $pdo->prepare("SELECT content FROM message_templates WHERE group_id = ?");
    $msgStmt->execute([$campaign['group_id']]);
    $allMessages = $msgStmt->fetchAll(PDO::FETCH_COLUMN);

    // 3. Fetch a batch (e.g., 10) of recipients
    $batchSize = 10;
    $recipientStmt = $pdo->prepare("
        SELECT id, phone_number 
        FROM marketing_user_number 
        WHERE list_id = ? AND user_id = ? AND is_sent = 0 
        LIMIT $batchSize
    ");
    $recipientStmt->execute([$campaign['list_id'], $campaign['user_id']]);
    $recipients = $recipientStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($recipients)) {
        $pdo->prepare("UPDATE campaigns SET status = 'completed' WHERE id = ?")->execute([$campaign['id']]);
        exit;
    }

    // 4. PRE-UPDATE: Mark IDs as "processing" (status 3) to prevent other workers from picking them
    $ids = array_column($recipients, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $pdo->prepare("UPDATE marketing_user_number SET is_sent = 3 WHERE id IN ($placeholders)")->execute($ids);

    $successIds = [];
    $failedIds = [];

    // 5. Process the batch
    foreach ($recipients as $recipient) {
        $message = $allMessages[array_rand($allMessages)];

        $response = MessengerService::send(
            $campaign['api_endpoint'],
            $campaign['api_key'],
            $recipient['phone_number'],
            $message
        );

        if ($response) {
            $successIds[] = $recipient['id'];
        } else {
            $failedIds[] = $recipient['id'];
        }
    }

    // 6. BATCH UPDATE: Update database once for success and once for failure
    if (!empty($successIds)) {
        $placeholders = implode(',', array_fill(0, count($successIds), '?'));
        $pdo->prepare("UPDATE marketing_user_number SET is_sent = 1 WHERE id IN ($placeholders)")->execute($successIds);
    }

    if (!empty($failedIds)) {
        $placeholders = implode(',', array_fill(0, count($failedIds), '?'));
        $pdo->prepare("UPDATE marketing_user_number SET is_sent = 2 WHERE id IN ($placeholders)")->execute($failedIds);
    }

} catch (Exception $e) {
    error_log("Worker $channelId Error: " . $e->getMessage());
}