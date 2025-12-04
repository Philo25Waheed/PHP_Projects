<?php
// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'my_projects';

mysqli_report(MYSQLI_REPORT_STRICT);

try {
    // Try to connect to the database
    $conn = new mysqli($host, $user, $pass, $dbName);
} catch (mysqli_sql_exception $e) {
    // If the database doesn't exist (error code 1049), create it
    if ($e->getCode() === 1049) {
        try {
            $tmp = new mysqli($host, $user, $pass);
            $createDbSql = "CREATE DATABASE IF NOT EXISTS `" . $dbName . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
            if (!$tmp->query($createDbSql)) {
                throw new Exception('Failed to create database: ' . $tmp->error);
            }
            $tmp->close();

            // Connect again, now that the database exists
            $conn = new mysqli($host, $user, $pass, $dbName);
        } catch (Exception $ex) {
            die('Database creation/connection failed: ' . $ex->getMessage());
        }
    } else {
        die('Database connection failed: ' . $e->getMessage());
    }
}

// Ensure the tasks table exists
$createTable = "CREATE TABLE IF NOT EXISTS `tasks` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `task` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($createTable)) {
    die('Table creation failed: ' . $conn->error);
}
?>
