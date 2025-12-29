<?php
/**
 * =====================================================
 * FitZone API - User Registration
 * =====================================================
 * POST /api/auth/register.php
 * 
 * Required fields: name, email, password
 */

require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

// Handle CORS
handleCORS();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed. Use POST.', 405);
}

// Get POST data
$data = getPostData();

// Validate required fields
$missing = validateRequired(['name', 'email', 'password'], $data);
if (!empty($missing)) {
    errorResponse('Missing required fields: ' . implode(', ', $missing));
}

// Sanitize inputs
$name = sanitize($data['name']);
$email = sanitize($data['email']);
$password = $data['password']; // Don't sanitize password before hashing

// Validate email format
if (!validateEmail($email)) {
    errorResponse('Invalid email format');
}

// Validate password strength
$passwordCheck = validatePassword($password);
if ($passwordCheck !== true) {
    errorResponse($passwordCheck);
}

// Validate name length
if (strlen($name) < 2 || strlen($name) > 100) {
    errorResponse('Name must be between 2 and 100 characters');
}

try {
    $db = getDB();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        errorResponse('Email already registered. Please login or use a different email.');
    }
    
    // Hash password securely
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    // Insert new user
    $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hashedPassword]);
    
    $userId = $db->lastInsertId();
    
    // Auto-login the user after registration
    loginUser($userId, $email, $name);
    
    // Return success response
    successResponse('Registration successful! Welcome to FitZone.', [
        'user' => [
            'id' => (int)$userId,
            'name' => $name,
            'email' => $email
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    errorResponse('Registration failed. Please try again later.', 500);
}
?>
