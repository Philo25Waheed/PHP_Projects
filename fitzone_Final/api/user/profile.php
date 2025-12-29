<?php
/**
 * =====================================================
 * FitZone API - User Profile
 * =====================================================
 * GET /api/user/profile.php - Get user profile
 * POST /api/user/profile.php - Update user profile
 * 
 * Requires authentication
 */

require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

// Handle CORS
handleCORS();

// Require authentication
requireAuth();

// Get current user ID
$userId = getCurrentUserId();

// Handle GET request - Return user profile
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $db = getDB();
        
        // Get user profile
        $stmt = $db->prepare("SELECT id, name, email, avatar, weight, height, goal, streak, last_workout_date, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            errorResponse('User not found', 404);
        }
        
        // Get recent progress
        $stmt = $db->prepare("SELECT date, weight, note, workout_completed FROM user_progress WHERE user_id = ? ORDER BY date DESC LIMIT 10");
        $stmt->execute([$userId]);
        $recentProgress = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'user' => $user,
            'recent_progress' => $recentProgress
        ]);
        
    } catch (PDOException $e) {
        error_log("Error fetching profile: " . $e->getMessage());
        errorResponse('Failed to fetch profile', 500);
    }
}

// Handle POST request - Update user profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = getPostData();
    
    // Build update query dynamically
    $updates = [];
    $params = [];
    
    // Allowed fields to update
    $allowedFields = ['name', 'weight', 'height', 'goal'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $value = sanitize($data[$field]);
            
            // Validate specific fields
            if ($field === 'name' && (strlen($value) < 2 || strlen($value) > 100)) {
                errorResponse('Name must be between 2 and 100 characters');
            }
            
            if ($field === 'goal' && !in_array($value, ['bulking', 'cutting', 'maintenance'])) {
                errorResponse('Goal must be bulking, cutting, or maintenance');
            }
            
            if ($field === 'weight' || $field === 'height') {
                $value = floatval($value);
                if ($value <= 0) {
                    errorResponse(ucfirst($field) . ' must be a positive number');
                }
            }
            
            $updates[] = "$field = ?";
            $params[] = $value;
        }
    }
    
    if (empty($updates)) {
        errorResponse('No valid fields to update');
    }
    
    // Add user ID to params
    $params[] = $userId;
    
    try {
        $db = getDB();
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        // Get updated user data
        $stmt = $db->prepare("SELECT id, name, email, avatar, weight, height, goal, streak FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        successResponse('Profile updated successfully!', [
            'user' => $user
        ]);
        
    } catch (PDOException $e) {
        error_log("Error updating profile: " . $e->getMessage());
        errorResponse('Failed to update profile', 500);
    }
}

// Method not allowed
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    errorResponse('Method not allowed. Use GET or POST.', 405);
}
?>
