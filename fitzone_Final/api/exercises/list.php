<?php
/**
 * =====================================================
 * FitZone API - List Exercises
 * =====================================================
 * GET /api/exercises/list.php
 * 
 * Returns all exercises with video URLs
 */

require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/db.php';

// Handle CORS
handleCORS();

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed. Use GET.', 405);
}

try {
    $db = getDB();
    
    // Get optional muscle group filter
    $muscleGroup = isset($_GET['muscle_group']) ? sanitize($_GET['muscle_group']) : null;
    $difficulty = isset($_GET['difficulty']) ? sanitize($_GET['difficulty']) : null;
    
    $sql = "SELECT * FROM exercises WHERE 1=1";
    $params = [];
    
    if ($muscleGroup) {
        $sql .= " AND muscle_group = ?";
        $params[] = $muscleGroup;
    }
    
    if ($difficulty) {
        $sql .= " AND difficulty = ?";
        $params[] = $difficulty;
    }
    
    $sql .= " ORDER BY muscle_group, title";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    $exercises = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'count' => count($exercises),
        'exercises' => $exercises
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching exercises: " . $e->getMessage());
    errorResponse('Failed to fetch exercises', 500);
}
?>
