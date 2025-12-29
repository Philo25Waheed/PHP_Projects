<?php
/**
 * =====================================================
 * FitZone API - List Meals
 * =====================================================
 * GET /api/meals/list.php
 * 
 * Returns all healthy meals with nutritional info
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
    
    // Get optional category filter
    $category = isset($_GET['category']) ? sanitize($_GET['category']) : null;
    
    if ($category) {
        $stmt = $db->prepare("SELECT * FROM meals WHERE category = ? ORDER BY name");
        $stmt->execute([$category]);
    } else {
        $stmt = $db->query("SELECT * FROM meals ORDER BY category, name");
    }
    
    $meals = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'count' => count($meals),
        'meals' => $meals
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching meals: " . $e->getMessage());
    errorResponse('Failed to fetch meals', 500);
}
?>
