<?php
/**
 * =====================================================
 * FitZone API - User Login
 * =====================================================
 * POST /api/auth/login.php
 * 
 * Required fields: email, password
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
$missing = validateRequired(['email', 'password'], $data);
if (!empty($missing)) {
    errorResponse('Missing required fields: ' . implode(', ', $missing));
}

// Sanitize email (not password)
$email = sanitize($data['email']);
$password = $data['password'];

// Validate email format
if (!validateEmail($email)) {
    errorResponse('Invalid email format');
}

try {
    $db = getDB();
    
    // Find user by email
    $stmt = $db->prepare("SELECT id, name, email, password, avatar, weight, height, goal, streak FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Check if user exists and password is correct
    if (!$user || !password_verify($password, $user['password'])) {
        errorResponse('Invalid email or password', 401);
    }
    
    // Login successful - create session
    loginUser($user['id'], $user['email'], $user['name']);
    
    // Remove password from response
    unset($user['password']);
    
    // Return success response with user data
    successResponse('Login successful! Welcome back.', [
        'user' => [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'avatar' => $user['avatar'],
            'weight' => $user['weight'],
            'height' => $user['height'],
            'goal' => $user['goal'],
            'streak' => (int)$user['streak']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    errorResponse('Login failed. Please try again later.', 500);
}
?>
