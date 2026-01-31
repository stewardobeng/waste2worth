<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Database;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

echo "Starting migration...\n";

try {
    $db = Database::getInstance()->getConnection();
    $sql = file_get_contents(__DIR__ . '/../database/init.sql');

    // Split SQL by semicolon, but handle cases where semicolon might be inside strings (basic handle)
    // For a robust migration, we could use PDO::exec on the whole file if the driver supports it,
    // but some drivers have issues with multiple statements.
    
    // Using exec on the whole content as it's a fresh init script
    $db->exec($sql);
    
    echo "Database initialized successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
