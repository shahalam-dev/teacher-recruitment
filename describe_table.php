<?php
require_once __DIR__ . '/main/config/db.php';
// Override host for local testing if needed, but since we rely on user environment:
// We just try to run it.

$stmt = $pdo->query("DESCRIBE marketing_user_number");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " | " . $col['Type'] . "\n";
}
