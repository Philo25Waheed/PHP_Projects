<?php
/**
 * =====================================================
 * FitZone API - Check Authentication Status
 * =====================================================
 * GET /api/auth/check.php
 * 
 * Returns current user if logged in, null otherwise
 */

require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

// Handle CORS
handleCORS();

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed. Use GET.', 405);
}

// Check if user is authenticated
if (isAuthenticated()) {
    $user = getCurrentUser();
    
    if ($user) {
        jsonResponse([
            'success' => true,
            'authenticated' => true,
            'user' => $user
        ]);
    }
}

// Not authenticated
jsonResponse([
    'success' => true,
    'authenticated' => false,
    'user' => null
]);
?>
