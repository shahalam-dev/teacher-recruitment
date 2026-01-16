<?php
// 1. Load configuration and services
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../services/MessengerService.php';

/**
 * BACKGROUND WORKER LOGIC
 * This script should be triggered by a Cron Job every 1 to 5 minutes.
 */

try {
    // 2. Find the oldest active campaign to process
    $campStmt = $pdo->query("
        SELECT c.id, c.user_id, c.list_id, c.group_id, ch.api_endpoint, ch.api_key 
        FROM campaigns c
        JOIN channels ch ON c.channel_id = ch.id
        WHERE c.status = 'running' 
        LIMIT 1
    ");
    $campaign = $campStmt->fetch(PDO::FETCH_ASSOC);

    if (!$campaign) {
        exit("No active campaigns found.\n");
    }

    // Fetch pool of messages from the group
    $msgStmt = $pdo->prepare("SELECT content FROM message_templates WHERE group_id = ?");
    $msgStmt->execute([$campaign['group_id']]);
    $allMessages = $msgStmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($allMessages)) {
        exit("No messages found in Group ID {$campaign['group_id']}.\n");
    }

    // 3. Fetch a batch of recipients for THIS specific user and list
    $recipientStmt = $pdo->prepare("
        SELECT id, phone_number 
        FROM marketing_user_number 
        WHERE list_id = ? AND user_id = ? AND is_sent = 0 
        LIMIT 5
    ");
    $recipientStmt->execute([$campaign['list_id'], $campaign['user_id']]);
    $recipients = $recipientStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($recipients)) {
        // No more numbers to send? Mark campaign as completed
        $pdo->prepare("UPDATE campaigns SET status = 'completed' WHERE id = ?")
            ->execute([$campaign['id']]);
        exit("Campaign {$campaign['id']} completed.\n");
    }

    // 4. Loop through recipients and send
    foreach ($recipients as $row) {
        $phone = $row['phone_number'];

        // Randomly pick one message
        $message = $allMessages[array_rand($allMessages)];

        // Use the Service to handle the API call
        $response = MessengerService::send(
            $campaign['api_endpoint'],
            $campaign['api_key'],
            $phone,
            $message
        );

        // 5. Log success and update database status
        if ($response) {
            $update = $pdo->prepare("UPDATE marketing_user_number SET is_sent = 1 WHERE id = ?");
            $update->execute([$row['id']]);
            echo "✓ Successfully sent to $phone\n";
        } else {
            // Mark as failed (2) so it's not retried indefinitely
            $update = $pdo->prepare("UPDATE marketing_user_number SET is_sent = 2 WHERE id = ?");
            $update->execute([$row['id']]);
            echo "❌ Failed to send to $phone (Marked as failed)\n";
        }

        // Anti-spam delay to prevent API blocking
        sleep(2);
    }
} catch (Exception $e) {
    error_log("Worker Error: " . $e->getMessage());
    exit("An error occurred. Check logs.\n");
}
