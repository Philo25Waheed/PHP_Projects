<?php
/**
 * Coptic Hero Website Setup Script
 * 
 * This script helps you set up the database and configure the website.
 * Run this script once after uploading the files to your server.
 */

// Include database configuration
require_once 'php/config/database.php';

// Set content type
header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coptic Hero Website - Setup</title>
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #8b4513;
            text-align: center;
            margin-bottom: 30px;
        }
        .step {
            background: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            border-left: 4px solid #8b4513;
        }
        .step h3 {
            color: #8b4513;
            margin-top: 0;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border-color: #ffc107;
        }
        .btn {
            background: #8b4513;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #d2691e;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Coptic Hero Website Setup</h1>
        
        <div class="step">
            <h3>Welcome to the Setup Wizard</h3>
            <p>This script will help you configure your Coptic Hero website. Please follow the steps below to complete the setup.</p>
        </div>

        <?php
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'setup_database') {
                setupDatabaseAction();
            } elseif ($action === 'test_connection') {
                testConnectionAction();
            }
        }
        
        // Check current status
        $dbExists = checkDatabaseExists();
        $tablesExist = checkTablesExist();
        ?>

        <div class="step">
            <h3>📊 Database Configuration</h3>
            
            <?php if ($dbExists): ?>
                <div class="status success">
                    ✅ Database connection successful!
                </div>
            <?php else: ?>
                <div class="status error">
                    ❌ Database connection failed. Please check your configuration.
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="test_connection">
                <button type="submit" class="btn">Test Database Connection</button>
            </form>
        </div>

        <?php if ($dbExists): ?>
        <div class="step">
            <h3>🗄️ Database Tables</h3>
            
            <?php if ($tablesExist): ?>
                <div class="status success">
                    ✅ Database tables are ready!
                </div>
            <?php else: ?>
                <div class="status warning">
                    ⚠️ Database tables need to be created.
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="setup_database">
                    <button type="submit" class="btn">Create Database Tables</button>
                </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="step">
            <h3>📧 Email Configuration</h3>
            <p>To configure email notifications for the contact form:</p>
            <ol>
                <li>Edit <code>php/contact.php</code></li>
                <li>Find the <code>sendEmail()</code> function</li>
                <li>Update the <code>$to</code> variable with your email address</li>
                <li>Ensure your server supports PHP's <code>mail()</code> function</li>
            </ol>
        </div>

        <div class="step">
            <h3>🖼️ Image Setup</h3>
            <p>Add the following images to the <code>images/</code> directory:</p>
            <ul>
                <li><code>saint-refael.jpg</code> - Hero image</li>
                <li><code>egypt-map.jpg</code> - Map image</li>
                <li><code>icon-saint-refael.jpg</code> - Icon image</li>
                <li><code>church-saint-refael.jpg</code> - Church image</li>
                <li><code>manuscript.jpg</code> - Manuscript image</li>
                <li><code>monastery.jpg</code> - Monastery image</li>
            </ul>
        </div>

        <div class="step">
            <h3>✅ Final Steps</h3>
            <ol>
                <li>Delete this <code>setup.php</code> file for security</li>
                <li>Update the content in <code>index.html</code> with your information</li>
                <li>Test the contact form functionality</li>
                <li>Configure your web server for optimal performance</li>
            </ol>
        </div>

        <div class="step">
            <h3>🔗 Quick Links</h3>
            <a href="index.html" class="btn">View Website</a>
            <a href="README.md" class="btn btn-secondary">View Documentation</a>
        </div>
    </div>
</body>
</html>

<?php
/**
 * Functions for setup actions
 */

function setupDatabaseAction() {
    echo '<div class="step">';
    echo '<h3>🗄️ Database Setup Results</h3>';
    
    try {
        if (setupDatabase()) {
            echo '<div class="status success">✅ Database tables created successfully!</div>';
        } else {
            echo '<div class="status error">❌ Failed to create database tables.</div>';
        }
    } catch (Exception $e) {
        echo '<div class="status error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    
    echo '</div>';
}

function testConnectionAction() {
    echo '<div class="step">';
    echo '<h3>📊 Connection Test Results</h3>';
    
    try {
        if (testDatabaseConnection()) {
            echo '<div class="status success">✅ Database connection successful!</div>';
        } else {
            echo '<div class="status error">❌ Database connection failed.</div>';
        }
    } catch (Exception $e) {
        echo '<div class="status error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    
    echo '</div>';
}

function checkDatabaseExists() {
    try {
        return testDatabaseConnection();
    } catch (Exception $e) {
        return false;
    }
}

function checkTablesExist() {
    try {
        $pdo = getDatabaseConnection();
        if (!$pdo) return false;
        
        $stmt = $pdo->query("SHOW TABLES LIKE 'contact_messages'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}
?> 