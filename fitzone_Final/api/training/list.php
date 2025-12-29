<?php
/**
 * =====================================================
 * FitZone API - List Training Programs
 * =====================================================
 * GET /api/training/list.php
 * 
 * Returns all training programs
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
    
    // Get optional slug filter
    $slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : null;
    
    if ($slug) {
        $stmt = $db->prepare("SELECT * FROM training_programs WHERE slug = ?");
        $stmt->execute([$slug]);
        $program = $stmt->fetch();
        
        if (!$program) {
            errorResponse('Training program not found', 404);
        }
        
        jsonResponse([
            'success' => true,
            'program' => $program
        ]);
    } else {
        $stmt = $db->query("SELECT * FROM training_programs ORDER BY name");
        $programs = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'count' => count($programs),
            'programs' => $programs
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Error fetching training programs: " . $e->getMessage());
    errorResponse('Failed to fetch training programs', 500);
}
?>
