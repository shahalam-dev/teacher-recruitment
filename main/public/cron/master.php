<?php
require_once __DIR__ . '/../../config/db.php';

// Get the absolute path to PHP on Hostinger (usually /usr/local/bin/php)
$phpPath = PHP_BINARY ?: '/usr/local/bin/php';

try {
    $stmt = $pdo->query("SELECT DISTINCT channel_id FROM campaigns WHERE status = 'running'");
    $channels = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($channels as $channelId) {
        $workerPath = __DIR__ . "/worker.php";

        // Command breakdown:
        // nohup: keeps the process running even if the terminal/script closes
        // > /dev/null 2>&1: discards output/errors (prevents filling up logs unnecessarily)
        // &: runs the process in the background
        $cmd = "nohup $phpPath $workerPath --channel_id=$channelId > /dev/null 2>&1 &";

        exec($cmd);
        echo "Background worker started for Channel ID: $channelId\n";
    }
} catch (Exception $e) {
    error_log("Master Error: " . $e->getMessage());
}