<?php
/**
 * =====================================================
 * FitZone Helper Functions
 * =====================================================
 * Utility functions for the application
 */

/**
 * Send JSON response with proper headers
 * @param mixed $data Response data
 * @param int $status HTTP status code
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send success response
 * @param string $message Success message
 * @param array $data Additional data
 */
function successResponse($message, $data = []) {
    jsonResponse(array_merge([
        'success' => true,
        'message' => $message
    ], $data));
}

/**
 * Send error response
 * @param string $message Error message
 * @param int $status HTTP status code
 */
function errorResponse($message, $status = 400) {
    jsonResponse([
        'success' => false,
        'error' => $message
    ], $status);
}

/**
 * Sanitize user input
 * @param string $input Raw input
 * @return string Sanitized input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 * @param string $email Email to validate
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate required fields
 * @param array $fields Array of field names
 * @param array $data Data array to check
 * @return array Array of missing fields
 */
function validateRequired($fields, $data) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $missing[] = $field;
        }
    }
    return $missing;
}

/**
 * Get POST data from JSON body or form data
 * @return array
 */
function getPostData() {
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        return $data ?? [];
    }
    
    return $_POST;
}

/**
 * Handle CORS preflight requests
 */
function handleCORS() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return bool|string True if valid, error message if not
 */
function validatePassword($password) {
    if (strlen($password) < 6) {
        return 'Password must be at least 6 characters long';
    }
    return true;
}

/**
 * Generate secure random token
 * @param int $length Token length
 * @return string
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}
?>
