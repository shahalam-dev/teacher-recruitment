<?php
require_once __DIR__ . '/main/config/db.php';

echo "--- Campaigns ---\n";
$stmt = $pdo->query("SELECT id, campaign_name, status, list_id FROM campaigns");
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($campaigns);

foreach ($campaigns as $c) {
    if ($c['status'] == 'running') {
        echo "--- Recipients for Campaign {$c['id']} (List {$c['list_id']}) ---\n";
        $stmt2 = $pdo->prepare("SELECT COUNT(*) as total, SUM(is_sent) as sent FROM marketing_user_number WHERE list_id = ?");
        $stmt2->execute([$c['list_id']]);
        $stats = $stmt2->fetch(PDO::FETCH_ASSOC);
        echo "Total: " . $stats['total'] . ", Sent: " . $stats['sent'] . "\n";
        
        $stmt3 = $pdo->prepare("SELECT id, phone_number, is_sent FROM marketing_user_number WHERE list_id = ? AND is_sent = 0");
        $stmt3->execute([$c['list_id']]);
        $unsent = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        echo "Unsent count: " . count($unsent) . "\n";
        if (count($unsent) > 0) {
            echo "First 5 unsent:\n";
            print_r(array_slice($unsent, 0, 5));
        }
    }
}
