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
    die("Database connection failed: " . $e->getMessage() . "\nMake sure MySQL is running on port 3306 on localhost.");
}

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Disable FK checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

    echo "Updating database schema...\n";

    // 1. Create template_groups table
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'template_groups'");
    if ($stmt->rowCount() == 0) {
        $sql = "CREATE TABLE template_groups (
            id INT AUTO_INCREMENT PRIMARY KEY,
            group_name VARCHAR(100) NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        echo "Created table 'template_groups'.\n";
    } else {
        echo "Table 'template_groups' already exists.\n";
    }

    // 2. Modify message_templates
    // We'll clear the table first to avoid foreign key/not null constraint issues during migration
    // since the structure changes fundamentally.
    $pdo->exec("TRUNCATE TABLE message_templates");
    echo "Truncated 'message_templates' to prepare for schema change.\n";

    // Check columns
    $stmt = $pdo->query("SHOW COLUMNS FROM message_templates LIKE 'group_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE message_templates ADD COLUMN group_id INT NOT NULL");
        echo "Added 'group_id' to 'message_templates'.\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM message_templates LIKE 'template_name'");
    if ($stmt->rowCount() > 0) {
        $pdo->exec("ALTER TABLE message_templates DROP COLUMN template_name");
        echo "Dropped 'template_name' from 'message_templates'.\n";
    }

    // 3. Modify campaigns
    // Truncate campaigns too as they link to old templates
    $pdo->exec("TRUNCATE TABLE campaigns");
    echo "Truncated 'campaigns' to prepare for schema change.\n";

    $stmt = $pdo->query("SHOW COLUMNS FROM campaigns LIKE 'template_id'");
    if ($stmt->rowCount() > 0) {
        // Find and drop FK on template_id
        $sql = "SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'campaigns' 
                AND COLUMN_NAME = 'template_id' 
                AND TABLE_SCHEMA = '$db'";
        $fkStmt = $pdo->query($sql);
        $fkName = $fkStmt->fetchColumn();
        
        if ($fkName) {
            $pdo->exec("ALTER TABLE campaigns DROP FOREIGN KEY `$fkName`");
            echo "Dropped Foreign Key '$fkName' on 'campaigns.template_id'.\n";
        }
        
        $pdo->exec("ALTER TABLE campaigns DROP COLUMN template_id");
        echo "Dropped 'template_id' from 'campaigns'.\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM campaigns LIKE 'group_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE campaigns ADD COLUMN group_id INT NOT NULL");
        echo "Added 'group_id' to 'campaigns'.\n";
    }

    echo "Database schema updated successfully.\n";

} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
}
