<?php
/**
 * Contact Form Handler for Coptic Hero Website
 * Handles form submissions, validation, and email sending
 */

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

// Allow CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate required fields
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address';
}

if (empty($message)) {
    $errors[] = 'Message is required';
}

// Additional validation
if (strlen($name) > 100) {
    $errors[] = 'Name is too long (maximum 100 characters)';
}

if (strlen($email) > 255) {
    $errors[] = 'Email is too long';
}

if (strlen($message) > 2000) {
    $errors[] = 'Message is too long (maximum 2000 characters)';
}

if (strlen($message) < 10) {
    $errors[] = 'Message is too short (minimum 10 characters)';
}

// Check for spam indicators
if (containsSpam($message) || containsSpam($name)) {
    $errors[] = 'Your message contains content that appears to be spam';
}

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please correct the following errors:',
        'errors' => $errors
    ]);
    exit;
}

// Sanitize data
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Prepare email content
$emailSubject = "Coptic Hero Website - " . ($subject ?: 'General Inquiry');
$emailBody = prepareEmailBody($name, $email, $subject, $message);

// Send email
$emailSent = sendEmail($emailSubject, $emailBody);

// Store in database (if configured)
$dbStored = false;
if (isDatabaseConfigured()) {
    $dbStored = storeInDatabase($name, $email, $subject, $message);
}

// Return response
if ($emailSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We will get back to you soon.',
        'data' => [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, there was an error sending your message. Please try again later.'
    ]);
}

/**
 * Check if message contains spam indicators
 */
function containsSpam($text) {
    $spamKeywords = [
        'buy now', 'click here', 'free money', 'make money fast',
        'viagra', 'casino', 'loan', 'debt', 'credit card',
        'http://', 'www.', 'click', 'subscribe', 'unsubscribe'
    ];
    
    $text = strtolower($text);
    
    foreach ($spamKeywords as $keyword) {
        if (strpos($text, $keyword) !== false) {
            return true;
        }
    }
    
    // Check for excessive links
    $linkCount = substr_count($text, 'http') + substr_count($text, 'www');
    if ($linkCount > 3) {
        return true;
    }
    
    // Check for excessive repetition
    $words = explode(' ', $text);
    $wordCount = array_count_values($words);
    foreach ($wordCount as $word => $count) {
        if (strlen($word) > 3 && $count > 5) {
            return true;
        }
    }
    
    return false;
}

/**
 * Prepare email body
 */
function prepareEmailBody($name, $email, $subject, $message) {
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background: #8b4513; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #8b4513; }
            .message { background: #f8f9fa; padding: 15px; border-left: 4px solid #8b4513; margin: 20px 0; }
            .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>Coptic Hero Website - Contact Form</h1>
        </div>
        
        <div class='content'>
            <div class='field'>
                <span class='label'>Name:</span> {$name}
            </div>
            
            <div class='field'>
                <span class='label'>Email:</span> {$email}
            </div>
            
            <div class='field'>
                <span class='label'>Subject:</span> " . ($subject ?: 'General Inquiry') . "
            </div>
            
            <div class='field'>
                <span class='label'>Message:</span>
                <div class='message'>
                    " . nl2br($message) . "
                </div>
            </div>
            
            <div class='field'>
                <span class='label'>Submitted:</span> " . date('F j, Y \a\t g:i A') . "
            </div>
        </div>
        
        <div class='footer'>
            <p>This message was sent from the Coptic Hero website contact form.</p>
            <p>IP Address: " . $_SERVER['REMOTE_ADDR'] . " | User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "</p>
        </div>
    </body>
    </html>
    ";
    
    return $body;
}

/**
 * Send email using PHP mail() function
 */
function sendEmail($subject, $body) {
    // Email configuration
    $to = 'info@coptichero.com'; // Change this to your email
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Coptic Hero Website <noreply@coptichero.com>',
        'Reply-To: noreply@coptichero.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Try to send email
    try {
        $result = mail($to, $subject, $body, implode("\r\n", $headers));
        return $result;
    } catch (Exception $e) {
        error_log('Email sending failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Check if database is configured
 */
function isDatabaseConfigured() {
    return file_exists(__DIR__ . '/config/database.php');
}

/**
 * Store contact form data in database
 */
function storeInDatabase($name, $email, $subject, $message) {
    try {
        // Include database configuration
        require_once __DIR__ . '/config/database.php';
        
        // Create database connection
        $pdo = new PDO(
            "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4",
            $dbConfig['username'],
            $dbConfig['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        
        // Prepare SQL statement
        $sql = "INSERT INTO contact_messages (name, email, subject, message, ip_address, user_agent, created_at) 
                VALUES (:name, :email, :subject, :message, :ip_address, :user_agent, NOW())";
        
        $stmt = $pdo->prepare($sql);
        
        // Execute with parameters
        $result = $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':subject' => $subject ?: 'General Inquiry',
            ':message' => $message,
            ':ip_address' => $_SERVER['REMOTE_ADDR'],
            ':user_agent' => $_SERVER['HTTP_USER_AGENT']
        ]);
        
        return $result;
        
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Create database table (run this once to set up the database)
 */
function createContactTable() {
    try {
        require_once __DIR__ . '/config/database.php';
        
        $pdo = new PDO(
            "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4",
            $dbConfig['username'],
            $dbConfig['password']
        );
        
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
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        return true;
        
    } catch (PDOException $e) {
        error_log('Table creation error: ' . $e->getMessage());
        return false;
    }
}

// Log the contact attempt
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    'name' => $name,
    'email' => $email,
    'subject' => $subject,
    'success' => $emailSent
];

error_log('Contact form submission: ' . json_encode($logData));
?> 