<?php
/**
 * =====================================================
 * FitZone Database Configuration
 * =====================================================
 * Secure database connection using PDO
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'fitzone_database');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password is empty

// PDO connection options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

// Create PDO instance
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        $options
    );
} catch (PDOException $e) {
    // Log error (in production, log to file instead)
    error_log("Database connection failed: " . $e->getMessage());
    
    // Return JSON error for API requests
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed. Please check your configuration.'
    ]);
    exit;
}

/**
 * Get the PDO database connection
 * @return PDO
 */
function getDB() {
    global $pdo;
    return $pdo;
}
?>
