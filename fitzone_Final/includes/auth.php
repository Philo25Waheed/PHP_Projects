<?php
/**
 * =====================================================
 * FitZone Authentication Middleware
 * =====================================================
 * Session management and authentication functions
 */

// Start session with secure settings
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Secure session settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Lax');
        
        session_start();
    }
}

// Initialize session on include
initSession();

/**
 * Check if user is authenticated
 * @return bool
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require authentication - redirect or return error if not logged in
 * @param bool $returnJson If true, return JSON error instead of redirect
 */
function requireAuth($returnJson = true) {
    if (!isAuthenticated()) {
        if ($returnJson) {
            require_once __DIR__ . '/helpers.php';
            errorResponse('Authentication required. Please login.', 401);
        } else {
            header('Location: /login.html');
            exit;
        }
    }
}

/**
 * Get current logged-in user data
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    require_once __DIR__ . '/../config/db.php';
    
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, email, avatar, weight, height, goal, streak, last_workout_date FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        return $user ?: null;
    } catch (PDOException $e) {
        error_log("Error getting current user: " . $e->getMessage());
        return null;
    }
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Login user - create session
 * @param int $userId User ID
 * @param string $email User email
 * @param string $name User name
 */
function loginUser($userId, $email, $name) {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $name;
    $_SESSION['login_time'] = time();
}

/**
 * Logout user - destroy session
 */
function logoutUser() {
    // Clear session data
    $_SESSION = [];
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Update user streak based on workout logging
 * @param int $userId User ID
 * @return int New streak value
 */
function updateUserStreak($userId) {
    require_once __DIR__ . '/../config/db.php';
    
    try {
        $db = getDB();
        
        // Get user's last workout date and current streak
        $stmt = $db->prepare("SELECT last_workout_date, streak FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        $today = date('Y-m-d');
        $lastWorkout = $user['last_workout_date'];
        $streak = (int)$user['streak'];
        
        if (!$lastWorkout) {
            // First workout ever
            $streak = 1;
        } else {
            $lastDate = new DateTime($lastWorkout);
            $todayDate = new DateTime($today);
            $diff = $lastDate->diff($todayDate)->days;
            
            if ($diff === 0) {
                // Already logged today, don't increment
                return $streak;
            } elseif ($diff === 1) {
                // Consecutive day, increment streak
                $streak++;
            } else {
                // Streak broken, start over
                $streak = 1;
            }
        }
        
        // Update user record
        $stmt = $db->prepare("UPDATE users SET streak = ?, last_workout_date = ? WHERE id = ?");
        $stmt->execute([$streak, $today, $userId]);
        
        return $streak;
        
    } catch (PDOException $e) {
        error_log("Error updating streak: " . $e->getMessage());
        return 0;
    }
}
?>
