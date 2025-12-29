<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل الدخول
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'student':
            header('Location: student/dashboard.php');
            break;
        case 'parent':
            header('Location: parent/dashboard.php');
            break;
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'register') {
        // تسجيل حساب جديد
        $username = $_POST['username'];
        $password = $_POST['password'];
        $full_name = $_POST['full_name'];
        $user_type = $_POST['user_type'];
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        // التحقق من عدم وجود اسم المستخدم
        $check_sql = "SELECT id FROM users WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = 'اسم المستخدم موجود بالفعل';
        } else {
            // إدخال المستخدم الجديد
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, full_name, user_type, email, phone) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $username, $hashed_password, $full_name, $user_type, $email, $phone);
            
            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                
                // إضافة بيانات إضافية حسب نوع المستخدم
                if ($user_type == 'student') {
                    $student_number = $_POST['student_number'] ?? 'ST' . $user_id;
                    $class = $_POST['class'] ?? 'غير محدد';
                    $sql = "INSERT INTO students (user_id, student_number, class) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iss", $user_id, $student_number, $class);
                    $stmt->execute();
                }
                
                $success = 'تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول';
            } else {
                $error = 'خطأ في إنشاء الحساب';
            }
        }
    } else {
        // تسجيل الدخول
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['username'] = $user['username'];
                
                switch ($user['user_type']) {
                    case 'admin':
                        header('Location: admin/dashboard.php');
                        break;
                    case 'student':
                        header('Location: student/dashboard.php');
                        break;
                    case 'parent':
                        header('Location: parent/dashboard.php');
                        break;
                }
                exit();
            } else {
                $error = 'كلمة المرور غير صحيحة';
            }
        } else {
            $error = 'اسم المستخدم غير موجود';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الحضور والغياب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 30px;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-register {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .btn-register:hover {
            background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
        }
        .user-type-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .nav-tabs {
            border: none;
        }
        .nav-tabs .nav-link {
            border: none;
            border-radius: 10px;
            margin: 0 5px;
            color: #667eea;
            font-weight: 600;
        }
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="login-container">
                    <div class="login-header text-center">
                        <i class="fas fa-graduation-cap user-type-icon"></i>
                        <h3 class="mb-0">نظام إدارة الحضور والغياب</h3>
                        <p class="mb-0 mt-2">مرحباً بك في النظام</p>
                    </div>
                    
                    <div class="p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- تبويبات تسجيل الدخول والتسجيل -->
                        <ul class="nav nav-tabs mb-4" id="authTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">
                                    <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">
                                    <i class="fas fa-user-plus"></i> حساب جديد
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="authTabsContent">
                            <!-- تسجيل الدخول -->
                            <div class="tab-pane fade show active" id="login" role="tabpanel">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="login_username" class="form-label">اسم المستخدم</label>
                                        <input type="text" class="form-control" id="login_username" name="username" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="login_password" class="form-label">كلمة المرور</label>
                                        <input type="password" class="form-control" id="login_password" name="password" required>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-custom w-100">
                                        <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                                    </button>
                                </form>
                            </div>
                            
                            <!-- تسجيل حساب جديد -->
                            <div class="tab-pane fade" id="register" role="tabpanel">
                                <form method="POST">
                                    <input type="hidden" name="action" value="register">
                                    
                                    <div class="mb-3">
                                        <label for="register_username" class="form-label">اسم المستخدم</label>
                                        <input type="text" class="form-control" id="register_username" name="username" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="register_password" class="form-label">كلمة المرور</label>
                                        <input type="password" class="form-control" id="register_password" name="password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">الاسم الكامل</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="user_type" class="form-label">نوع الحساب</label>
                                        <select class="form-select" id="user_type" name="user_type" required>
                                            <option value="">اختر نوع الحساب</option>
                                            <option value="admin">مدير (خادم)</option>
                                            <option value="student">طالب (مخدوم)</option>
                                            <option value="parent">ولي أمر</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">البريد الإلكتروني (اختياري)</label>
                                        <input type="email" class="form-control" id="email" name="email">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">رقم الهاتف (اختياري)</label>
                                        <input type="text" class="form-control" id="phone" name="phone">
                                    </div>
                                    
                                    <!-- حقول إضافية للطالب -->
                                    <div id="student_fields" style="display: none;">
                                        <div class="mb-3">
                                            <label for="student_number" class="form-label">رقم الطالب</label>
                                            <input type="text" class="form-control" id="student_number" name="student_number" placeholder="سيتم إنشاؤه تلقائياً">
                                        </div>
                                        <div class="mb-3">
                                            <label for="class" class="form-label">الصف</label>
                                            <input type="text" class="form-control" id="class" name="class" placeholder="مثال: الصف الأول">
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-custom btn-register w-100">
                                        <i class="fas fa-user-plus"></i> إنشاء الحساب
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                نظام إدارة الحضور والغياب - جميع الحقوق محفوظة
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // إظهار/إخفاء الحقول حسب نوع المستخدم
        document.getElementById('user_type').addEventListener('change', function() {
            const studentFields = document.getElementById('student_fields');
            studentFields.style.display = this.value === 'student' ? 'block' : 'none';
        });
    </script>
</body>
</html> 