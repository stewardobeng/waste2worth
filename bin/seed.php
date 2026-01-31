<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "Seeding default admin user...\n";

try {
    $db = Database::getInstance()->getConnection();
    
    $email = 'admin@waste2worth.com';
    $password = 'Admin123!';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo "Admin user already exists.\n";
    } else {
        $stmt = $db->prepare("INSERT INTO users (role, email, phone, password_hash, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $email, '0000000000', $passwordHash, 'active']);
        echo "Admin user created successfully!\n";
        echo "Email: $email\n";
        echo "Password: $password\n";
    }
} catch (Exception $e) {
    echo "Seeding failed: " . $e->getMessage() . "\n";
}
