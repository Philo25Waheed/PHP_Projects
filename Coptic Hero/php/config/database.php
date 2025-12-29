<?php
/**
 * Database Configuration for Coptic Hero Website
 * 
 * IMPORTANT: Update these settings with your actual database credentials
 * and ensure the database exists before using the contact form.
 */

// Database configuration
$dbConfig = [
    'host' => 'localhost',           // Database host (usually localhost)
    'database' => 'coptic_hero',     // Database name
    'username' => 'root',            // Database username
    'password' => '',                // Database password
    'charset' => 'utf8mb4',          // Character set
    'collation' => 'utf8mb4_unicode_ci' // Collation
];

// Optional: PDO connection options
$dbOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

/**
 * Get database connection
 */
function getDatabaseConnection() {
    global $dbConfig, $dbOptions;
    
    try {
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbOptions);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Test database connection
 */
function testDatabaseConnection() {
    $pdo = getDatabaseConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            error_log('Database test failed: ' . $e->getMessage());
            return false;
        }
    }
    return false;
}

/**
 * Create database if it doesn't exist
 */
function createDatabaseIfNotExists() {
    global $dbConfig;
    
    try {
        // Connect without specifying database
        $dsn = "mysql:host={$dbConfig['host']};charset={$dbConfig['charset']}";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
        
        // Create database if it doesn't exist
        $sql = "CREATE DATABASE IF NOT EXISTS `{$dbConfig['database']}` 
                CHARACTER SET {$dbConfig['charset']} 
                COLLATE {$dbConfig['collation']}";
        
        $pdo->exec($sql);
        return true;
        
    } catch (PDOException $e) {
        error_log('Database creation failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Initialize database tables
 */
function initializeDatabase() {
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        return false;
    }
    
    try {
        // Create contact_messages table
        $sql = "CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            subject VARCHAR(200) DEFAULT 'General Inquiry',
            message TEXT NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_created_at (created_at),
            INDEX idx_subject (subject)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        
        // Create testimonials table (for future use)
        $sql = "CREATE TABLE IF NOT EXISTS testimonials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255),
            location VARCHAR(100),
            testimony TEXT NOT NULL,
            approved BOOLEAN DEFAULT FALSE,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_approved (approved),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        
        // Create site_analytics table (for tracking)
        $sql = "CREATE TABLE IF NOT EXISTS site_analytics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page VARCHAR(100) NOT NULL,
            action VARCHAR(50) NOT NULL,
            user_ip VARCHAR(45),
            user_agent TEXT,
            referrer VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_page (page),
            INDEX idx_action (action),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        
        return true;
        
    } catch (PDOException $e) {
        error_log('Database initialization failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Setup database (run this once)
 */
function setupDatabase() {
    // Create database if it doesn't exist
    if (!createDatabaseIfNotExists()) {
        return false;
    }
    
    // Initialize tables
    if (!initializeDatabase()) {
        return false;
    }
    
    return true;
}

// Uncomment the line below to automatically set up the database
// setupDatabase();

?> 