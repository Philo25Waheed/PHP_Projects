<?php
/**
 * =====================================================
 * FitZone API - List User Progress
 * =====================================================
 * GET /api/progress/list.php
 * 
 * Requires authentication
 * Returns user's progress history
 */

require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

// Handle CORS
handleCORS();

// Require authentication
requireAuth();

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed. Use GET.', 405);
}

// Get current user ID
$userId = getCurrentUserId();

// Get optional limit parameter
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 30;
$limit = min(max($limit, 1), 100); // Between 1 and 100

try {
    $db = getDB();
    
    // Get progress records
    $stmt = $db->prepare("SELECT id, date, weight, note, workout_completed, created_at FROM user_progress WHERE user_id = ? ORDER BY date DESC LIMIT ?");
    $stmt->execute([$userId, $limit]);
    $progress = $stmt->fetchAll();
    
    // Get user's current streak
    $stmt = $db->prepare("SELECT streak FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    jsonResponse([
        'success' => true,
        'streak' => (int)$user['streak'],
        'count' => count($progress),
        'progress' => $progress
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching progress: " . $e->getMessage());
    errorResponse('Failed to fetch progress', 500);
}
?>
