<?php
require_once __DIR__ . '/../../config/db.php';

// Get the absolute path to PHP on Hostinger (usually /usr/local/bin/php)
// When running from web, PHP_BINARY might point to FPM/CGI which isn't what we want for CLI.
// We'll try to detect 'php' in path or default to common locations.
$phpPath = 'php'; // Default to trying PATH
if (defined('PHP_BINARY') && PHP_BINARY && in_array(basename(PHP_BINARY), ['php', 'php-cli', 'php8.1', 'php8.2', 'php8.3'])) {
    $phpPath = PHP_BINARY;
}

try {
    $stmt = $pdo->query("SELECT DISTINCT channel_id FROM campaigns WHERE status = 'running'");
    $channels = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($channels)) {
        echo "No running campaigns found.<br>\n";
    }

    foreach ($channels as $channelId) {
        $workerPath = __DIR__ . "/worker.php";

        // Restore production logging (discard output)
        $cmd = "nohup $phpPath $workerPath --channel_id=$channelId > /dev/null 2>&1 &";

        exec($cmd);
        echo "Background worker started for Channel ID: $channelId<br>\n";
    }
} catch (Exception $e) {
    error_log("Master Error: " . $e->getMessage());
}
