<?php
/**
 * =====================================================
 * FitZone API - User Logout
 * =====================================================
 * POST /api/auth/logout.php
 * 
 * Destroys user session
 */

require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

// Handle CORS
handleCORS();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed. Use POST.', 405);
}

// Logout user (destroy session)
logoutUser();

// Return success response
successResponse('Logged out successfully');
?>
