<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'attendance_system';

// الاتصال بدون قاعدة بيانات أولاً
$conn = new mysqli($host, $username, $password);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// إنشاء قاعدة البيانات إذا لم تكن موجودة
$sql = "CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$conn->query($sql)) {
    die("خطأ في إنشاء قاعدة البيانات: " . $conn->error);
}

// اختيار قاعدة البيانات
$conn->select_db($database);
// تعيين ترميز UTF-8
$conn->set_charset("utf8");

// إنشاء الجداول
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        user_type ENUM('admin', 'student', 'parent') NOT NULL,
        email VARCHAR(100),
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        student_number VARCHAR(20) UNIQUE NOT NULL,
        class VARCHAR(50),
        parent_id INT,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS parents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        student_id INT,
        relationship VARCHAR(50),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        date DATE NOT NULL,
        status ENUM('present', 'absent', 'late') NOT NULL,
        notes TEXT,
        recorded_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (recorded_by) REFERENCES users(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS exams (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        subject VARCHAR(50) NOT NULL,
        exam_date DATE NOT NULL,
        total_marks INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS exam_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        exam_id INT,
        student_id INT,
        marks_obtained DECIMAL(5,2) NOT NULL,
        percentage DECIMAL(5,2),
        grade VARCHAR(2),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        target_type ENUM('all', 'students', 'parents', 'admin') NOT NULL,
        target_id INT,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS instructions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        content TEXT NOT NULL,
        category VARCHAR(50),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

// تنفيذ إنشاء الجداول
foreach ($tables as $sql) {
    if (!$conn->query($sql)) {
        echo "خطأ في إنشاء الجدول: " . $conn->error;
    }
}

// إدخال بيانات تجريبية إذا لم تكن موجودة
$check_admin = "SELECT COUNT(*) as count FROM users WHERE user_type = 'admin'";
$result = $conn->query($check_admin);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // إدخال مدير تجريبي
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password, full_name, user_type, email) 
                  VALUES ('admin', '$admin_password', 'مدير النظام', 'admin', 'admin@school.com')");
    
    // إدخال طالب تجريبي
    $student_password = password_hash('student123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password, full_name, user_type, email) 
                  VALUES ('student1', '$student_password', 'أحمد محمد', 'student', 'ahmed@student.com')");
    
    $student_user_id = $conn->insert_id;
    $conn->query("INSERT INTO students (user_id, student_number, class) 
                  VALUES ($student_user_id, 'ST001', 'الصف الأول')");
    
    // إدخال ولي أمر تجريبي
    $parent_password = password_hash('parent123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password, full_name, user_type, email) 
                  VALUES ('parent1', '$parent_password', 'محمد أحمد', 'parent', 'mohamed@parent.com')");
    
    $parent_user_id = $conn->insert_id;
    $student_id = $conn->query("SELECT id FROM students WHERE user_id = $student_user_id")->fetch_assoc()['id'];
    $conn->query("INSERT INTO parents (user_id, student_id, relationship) 
                  VALUES ($parent_user_id, $student_id, 'أب')");
    
    // إدخال تعليمات تجريبية
    $conn->query("INSERT INTO instructions (title, content, category) VALUES 
                  ('قواعد السلوك العام', 'يجب على جميع الطلاب الالتزام بقواعد السلوك العام في المدرسة', 'قواعد عامة'),
                  ('مواعيد الحضور', 'يجب الحضور قبل بداية الحصة بـ 10 دقائق', 'مواعيد'),
                  ('الزي المدرسي', 'يجب ارتداء الزي المدرسي الرسمي في جميع الأيام', 'زي مدرسي')");
    
    // إدخال امتحان تجريبي
    $conn->query("INSERT INTO exams (title, subject, exam_date, total_marks) VALUES 
                  ('امتحان الرياضيات', 'الرياضيات', '2024-01-15', 100)");
    
    $exam_id = $conn->insert_id;
    $conn->query("INSERT INTO exam_results (exam_id, student_id, marks_obtained, percentage, grade) VALUES 
                  ($exam_id, $student_id, 85, 85.00, 'أ')");
}
?> 