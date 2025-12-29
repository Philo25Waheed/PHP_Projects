-- =====================================================
-- FitZone Database Schema
-- Database Name: fitzone_database
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS fitzone_database 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE fitzone_database;

-- =====================================================
-- USERS TABLE
-- Stores registered user information
-- =====================================================
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
) ENGINE=InnoDB;

-- =====================================================
-- MEALS TABLE
-- Stores healthy meal options with nutritional info
-- =====================================================
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
) ENGINE=InnoDB;

-- =====================================================
-- EXERCISES TABLE
-- Stores exercise information with video links
-- =====================================================
CREATE TABLE IF NOT EXISTS exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    video_url VARCHAR(500),
    muscle_group VARCHAR(50),
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- TRAINING PROGRAMS TABLE
-- Stores different training split programs
-- =====================================================
CREATE TABLE IF NOT EXISTS training_programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    schedule TEXT,
    suitable_for VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- USER PROGRESS TABLE
-- Tracks user workout progress and weight
-- =====================================================
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
) ENGINE=InnoDB;

-- =====================================================
-- CONTACTS TABLE
-- Stores contact form submissions
-- =====================================================
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- INSERT INITIAL DATA - MEALS
-- =====================================================
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
('Avocado Toast', 'Whole grain toast with mashed avocado and eggs', 270, 12, 22, 16, 'img/meal_10.jpg', 'breakfast');

-- =====================================================
-- INSERT INITIAL DATA - EXERCISES
-- =====================================================
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
('Tricep Dips', 'Bodyweight exercise targeting triceps', 'https://www.youtube.com/embed/0326dy_-CzM', 'Arms', 'beginner');

-- =====================================================
-- INSERT INITIAL DATA - TRAINING PROGRAMS
-- =====================================================
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
 'Advanced, Strength and Size goals');
