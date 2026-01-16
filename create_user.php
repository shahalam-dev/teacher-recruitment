<?php
require_once __DIR__ . '/main/config/db.php';

$username = 'admin2';
$email = 'admin@example2.com';
$password = 'Mehedi@123'; // Change this!
$hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Secure hashing

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
    $stmt->execute([$username, $email, $hashedPassword]);
    echo "Admin user created successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
