<?php
/**
 * =====================================================
 * FitZone Database Setup Script
 * =====================================================
 * Run this script once to create the database and tables
 * 
 * Access via browser: http://localhost/Meister%20Company/fitzone_semifinal/setup_database.php
 */

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'fitzone_database';

echo "<!DOCTYPE html>
<html>
<head>
    <title>FitZone Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #1a1a2e; color: #fff; }
        h1 { color: #b13bff; }
        .success { color: #4ade80; padding: 10px; background: rgba(74,222,128,0.1); border-radius: 5px; margin: 10px 0; }
        .error { color: #f87171; padding: 10px; background: rgba(248,113,113,0.1); border-radius: 5px; margin: 10px 0; }
        .info { color: #60a5fa; padding: 10px; background: rgba(96,165,250,0.1); border-radius: 5px; margin: 10px 0; }
        pre { background: #0f0f23; padding: 15px; border-radius: 8px; overflow-x: auto; }
        code { color: #b13bff; }
        a { color: #b13bff; }
    </style>
</head>
<body>
    <h1>🏋️ FitZone Database Setup</h1>
";

try {
    // Connect without database first
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<div class='success'>✓ Connected to MySQL server</div>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<div class='success'>✓ Database '$dbname' created or already exists</div>";
    
    // Select database
    $pdo->exec("USE `$dbname`");
    echo "<div class='success'>✓ Selected database '$dbname'</div>";
    
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            avatar VARCHAR(255) DEFAULT NULL,
            weight DECIMAL(5,2) DEFAULT NULL,
            height DECIMAL(5,2) DEFAULT NULL,
            goal ENUM('bulking', 'cutting', 'maintenance') DEFAULT 'maintenance',
            streak INT DEFAULT 0,
            last_workout_date DATE DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email)
        ) ENGINE=InnoDB
    ");
    echo "<div class='success'>✓ Table 'users' created</div>";
    
    // Create meals table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS meals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            calories INT NOT NULL,
            protein INT DEFAULT 0,
            carbs INT DEFAULT 0,
            fat INT DEFAULT 0,
            image VARCHAR(255) DEFAULT NULL,
            category ENUM('breakfast', 'lunch', 'dinner', 'snack') DEFAULT 'lunch',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    echo "<div class='success'>✓ Table 'meals' created</div>";
    
    // Create exercises table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS exercises (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(100) NOT NULL,
            description TEXT,
            video_url VARCHAR(500),
            muscle_group VARCHAR(50),
            difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    echo "<div class='success'>✓ Table 'exercises' created</div>";
    
    // Create training_programs table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS training_programs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            schedule TEXT,
            suitable_for VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    echo "<div class='success'>✓ Table 'training_programs' created</div>";
    
    // Create user_progress table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_progress (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            date DATE NOT NULL,
            weight DECIMAL(5,2),
            note TEXT,
            workout_completed BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_date (user_id, date)
        ) ENGINE=InnoDB
    ");
    echo "<div class='success'>✓ Table 'user_progress' created</div>";
    
    // Create contacts table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    echo "<div class='success'>✓ Table 'contacts' created</div>";
    
    // Insert sample meals data
    $checkMeals = $pdo->query("SELECT COUNT(*) FROM meals")->fetchColumn();
    if ($checkMeals == 0) {
        $pdo->exec("
            INSERT INTO meals (name, description, calories, protein, carbs, fat, image, category) VALUES
            ('Grilled Salmon', 'Fresh grilled salmon with herbs and lemon', 450, 46, 0, 28, 'img/meal_1.jpg', 'dinner'),
            ('Chicken Salad', 'Grilled chicken breast with fresh garden salad', 320, 35, 12, 14, 'img/meal_2.jpg', 'lunch'),
            ('Quinoa Bowl', 'Nutritious quinoa with vegetables and avocado', 380, 14, 52, 12, 'img/meal_3.jpg', 'lunch'),
            ('Tofu Stir-fry', 'Crispy tofu with mixed vegetables in soy sauce', 300, 18, 28, 14, 'img/meal_4.jpg', 'dinner'),
            ('Beef Steak', 'Premium beef steak with roasted vegetables', 600, 52, 8, 40, 'img/meal_5.jpg', 'dinner'),
            ('Veggie Wrap', 'Whole wheat wrap with fresh vegetables and hummus', 280, 10, 38, 10, 'img/meal_6.jpg', 'lunch'),
            ('Protein Pancakes', 'High-protein pancakes with fresh berries', 350, 28, 32, 12, 'img/meal_7.jpg', 'breakfast'),
            ('Turkey Sandwich', 'Lean turkey breast on whole grain bread', 400, 32, 42, 12, 'img/meal_8.jpg', 'lunch'),
            ('Greek Yogurt Parfait', 'Greek yogurt with granola and fresh fruits', 220, 18, 28, 4, 'img/meal_9.jpg', 'breakfast'),
            ('Avocado Toast', 'Whole grain toast with mashed avocado and eggs', 270, 12, 22, 16, 'img/meal_10.jpg', 'breakfast')
        ");
        echo "<div class='success'>✓ Inserted 10 sample meals</div>";
    } else {
        echo "<div class='info'>ℹ Meals table already has data, skipping insert</div>";
    }
    
    // Insert sample exercises data
    $checkExercises = $pdo->query("SELECT COUNT(*) FROM exercises")->fetchColumn();
    if ($checkExercises == 0) {
        $pdo->exec("
            INSERT INTO exercises (title, description, video_url, muscle_group, difficulty) VALUES
            ('Push Ups', 'Classic bodyweight exercise for chest, shoulders, and triceps', 'https://www.youtube.com/embed/_l3ySVKYVJ8', 'Chest', 'beginner'),
            ('Squats', 'Fundamental lower body exercise targeting quads, hamstrings, and glutes', 'https://www.youtube.com/embed/aclHkVaku9U', 'Legs', 'beginner'),
            ('Plank', 'Core strengthening isometric exercise', 'https://www.youtube.com/embed/pSHjTRCQxIw', 'Core', 'beginner'),
            ('Deadlift', 'Compound movement for posterior chain development', 'https://www.youtube.com/embed/op9kVnSso6Q', 'Back', 'intermediate'),
            ('Shoulder Press', 'Overhead pressing movement for shoulder development', 'https://www.youtube.com/embed/qEwKCR5JCog', 'Shoulders', 'intermediate'),
            ('Pull Ups', 'Upper body pulling exercise for back and biceps', 'https://www.youtube.com/embed/eGo4IYlbE5g', 'Back', 'intermediate'),
            ('Lunges', 'Unilateral leg exercise for balance and strength', 'https://www.youtube.com/embed/QOVaHwm-Q6U', 'Legs', 'beginner'),
            ('Bench Press', 'Primary chest building exercise with barbell', 'https://www.youtube.com/embed/rT7DgCr-3pg', 'Chest', 'intermediate'),
            ('Bicep Curls', 'Isolation exercise for bicep development', 'https://www.youtube.com/embed/ykJmrZ5v0Oo', 'Arms', 'beginner'),
            ('Tricep Dips', 'Bodyweight exercise targeting triceps', 'https://www.youtube.com/embed/0326dy_-CzM', 'Arms', 'beginner')
        ");
        echo "<div class='success'>✓ Inserted 10 sample exercises</div>";
    } else {
        echo "<div class='info'>ℹ Exercises table already has data, skipping insert</div>";
    }
    
    // Insert sample training programs
    $checkPrograms = $pdo->query("SELECT COUNT(*) FROM training_programs")->fetchColumn();
    if ($checkPrograms == 0) {
        $pdo->exec("
            INSERT INTO training_programs (name, slug, description, schedule, suitable_for) VALUES
            ('Bro Split', 'bro', 'Classic bodypart split focusing on one muscle group per day', 
             'Saturday: Chest | Sunday: Back | Monday: Shoulders | Tuesday: Legs | Wednesday: Arms | Thursday/Friday: Rest', 
             'Intermediate to Advanced, Bulking phase'),
            ('Full Body', 'full', 'Complete full body workout performed 3-4 times per week', 
             'Day 1: Full Body A | Day 2: Rest | Day 3: Full Body B | Day 4: Rest | Day 5: Full Body A', 
             'Beginners, Limited time'),
            ('Push / Pull', 'pushpull', 'Divides workouts into pushing and pulling movements', 
             'Day 1: Push (Chest, Shoulders, Triceps) | Day 2: Pull (Back, Biceps) | Day 3: Rest | Repeat', 
             'Intermediate, Balanced development'),
            ('Body Part Split', 'bodypart', 'Two-day split focusing on major and minor muscle groups', 
             'Day 1: Major (Chest, Back, Legs) | Day 2: Minor (Shoulders, Arms) | Day 3: Rest', 
             'Intermediate, 3-4 days per week'),
            ('Powerbuilding', 'power', 'Combines powerlifting and bodybuilding principles', 
             'Day 1: Heavy Squat + Accessories | Day 2: Heavy Bench + Accessories | Day 3: Rest | Day 4: Heavy Deadlift + Accessories | Day 5: Volume Day', 
             'Advanced, Strength and Size goals')
        ");
        echo "<div class='success'>✓ Inserted 5 training programs</div>";
    } else {
        echo "<div class='info'>ℹ Training programs table already has data, skipping insert</div>";
    }
    
    echo "
    <h2 style='color: #4ade80; margin-top: 30px;'>✅ Database Setup Complete!</h2>
    <div class='info'>
        <strong>Next Steps:</strong>
        <ol>
            <li>Delete this setup file for security: <code>setup_database.php</code></li>
            <li>Test the website: <a href='index.html'>Go to Homepage</a></li>
            <li>Test login: <a href='login.html'>Go to Login</a></li>
            <li>Test registration: <a href='register.html'>Go to Register</a></li>
        </ol>
    </div>
    
    <h3>API Endpoints Available:</h3>
    <pre><code>
Authentication:
  POST /api/auth/register.php   - Register new user
  POST /api/auth/login.php      - Login user
  POST /api/auth/logout.php     - Logout user
  GET  /api/auth/check.php      - Check auth status

Data:
  GET  /api/meals/list.php      - List all meals
  GET  /api/exercises/list.php  - List all exercises
  GET  /api/training/list.php   - List training programs

User (requires auth):
  GET/POST /api/user/profile.php - Get/update profile
  POST /api/progress/add.php     - Log workout progress
  GET  /api/progress/list.php    - Get progress history

Contact:
  POST /api/contact/submit.php   - Submit contact form
    </code></pre>
    ";
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='info'>
        <strong>Troubleshooting:</strong>
        <ul>
            <li>Make sure XAMPP MySQL is running</li>
            <li>Check if the MySQL credentials are correct in this file</li>
            <li>Try accessing phpMyAdmin to verify MySQL is working</li>
        </ul>
    </div>";
}

echo "</body></html>";
?>
