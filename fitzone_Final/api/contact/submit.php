<?php
/**
 * =====================================================
 * FitZone API - Submit Contact Form
 * =====================================================
 * POST /api/contact/submit.php
 * 
 * Required fields: name, email, message
 */

require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/db.php';

// Handle CORS
handleCORS();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed. Use POST.', 405);
}

// Get POST data
$data = getPostData();

// Validate required fields
$missing = validateRequired(['name', 'email', 'message'], $data);
if (!empty($missing)) {
    errorResponse('Missing required fields: ' . implode(', ', $missing));
}

// Sanitize inputs
$name = sanitize($data['name']);
$email = sanitize($data['email']);
$message = sanitize($data['message']);

// Validate email format
if (!validateEmail($email)) {
    errorResponse('Invalid email format');
}

// Validate message length
if (strlen($message) < 10) {
    errorResponse('Message must be at least 10 characters long');
}

if (strlen($message) > 2000) {
    errorResponse('Message must be less than 2000 characters');
}

try {
    $db = getDB();
    
    // Insert contact message
    $stmt = $db->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $message]);
    
    successResponse('Thank you for your message! We will get back to you soon.', [
        'contact_id' => (int)$db->lastInsertId()
    ]);
    
} catch (PDOException $e) {
    error_log("Error submitting contact: " . $e->getMessage());
    errorResponse('Failed to submit message. Please try again later.', 500);
}
?>
