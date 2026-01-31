<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS waste2worth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Database created or already exists.\n";
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
