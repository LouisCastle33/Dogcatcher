<?php
// Database Configuration
$host = 'localhost';
$db   = 'dog_registry_db'; // This must match the name in your phpMyAdmin
$user = 'root';            // Default XAMPP user
$pass = '';                // Default XAMPP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     // If you want to test if it works, uncomment the next line:
     // echo "Connected successfully"; 
} catch (\PDOException $e) {
     // If there is an error, this will tell you exactly what is wrong
     die("Database connection failed: " . $e->getMessage());
}
?>