<?php
/**
 * =====================================================
 * FitZone API - Add User Progress
 * =====================================================
 * POST /api/progress/add.php
 * 
 * Requires authentication
 * Optional fields: date, weight, note, workout_completed
 */

require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

// Handle CORS
handleCORS();

// Require authentication
requireAuth();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed. Use POST.', 405);
}

// Get POST data
$data = getPostData();

// Get current user ID
$userId = getCurrentUserId();

// Get values with defaults
$date = isset($data['date']) ? sanitize($data['date']) : date('Y-m-d');
$weight = isset($data['weight']) ? floatval($data['weight']) : null;
$note = isset($data['note']) ? sanitize($data['note']) : '';
$workoutCompleted = isset($data['workout_completed']) ? (bool)$data['workout_completed'] : true;

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    errorResponse('Invalid date format. Use YYYY-MM-DD');
}

try {
    $db = getDB();
    
    // Insert progress record
    $stmt = $db->prepare("INSERT INTO user_progress (user_id, date, weight, note, workout_completed) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $date, $weight, $note, $workoutCompleted]);
    
    // Update user streak if workout was completed
    $newStreak = 0;
    if ($workoutCompleted) {
        $newStreak = updateUserStreak($userId);
    }
    
    successResponse('Progress logged successfully!', [
        'progress_id' => (int)$db->lastInsertId(),
        'streak' => $newStreak
    ]);
    
} catch (PDOException $e) {
    error_log("Error adding progress: " . $e->getMessage());
    errorResponse('Failed to log progress', 500);
}
?>
