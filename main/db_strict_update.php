<?php
// Standalone connection for local execution script
$host = '127.0.0.1';
$db   = 'mehedi';
$user = 'root';
$pass = 'admin';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

try {
    echo "Verifying Database Schema...\n";
    // 1. Check constraints on message_templates
    $sql = "SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'message_templates' 
            AND COLUMN_NAME = 'group_id' 
            AND TABLE_SCHEMA = '$db'";
    $stmt = $pdo->query($sql);
    $fkName = $stmt->fetchColumn();

    if ($fkName) {
        // Drop existing to recreate with cascade
        $pdo->exec("ALTER TABLE message_templates DROP FOREIGN KEY `$fkName`");
    }
    
    // Add FK with Cascade
    $pdo->exec("ALTER TABLE message_templates 
                ADD CONSTRAINT fk_group_message 
                FOREIGN KEY (group_id) REFERENCES template_groups(id) 
                ON DELETE CASCADE");
    
    echo "Enforced ON DELETE CASCADE for message_templates.\n";

} catch (Exception $e) {
    echo "Update Note: " . $e->getMessage() . "\n";
}
