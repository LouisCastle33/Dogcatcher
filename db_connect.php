<?php
date_default_timezone_set('Asia/Manila');
$host = 'localhost';
$db   = 'dog_registry_db';
$user = 'root';
$pass = ''; // Ensure this matches your production password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Log error to a file instead of showing it to the user
    error_log($e->getMessage());
    die("A database error occurred. Please contact the IT Unit.");
}
?>