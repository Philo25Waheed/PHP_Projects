<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

$admin_id = $_SESSION['user_id'];

// معالجة إضافة مستخدم جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_user') {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $full_name = $_POST['full_name'];
        $user_type = $_POST['user_type'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        
        // إدخال المستخدم
        $sql = "INSERT INTO users (username, password, full_name, user_type, email, phone) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $username, $password, $full_name, $user_type, $email, $phone);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // إذا كان طالب، أضف بيانات الطالب
            if ($user_type == 'student') {
                $student_number = $_POST['student_number'];
                $class = $_POST['class'];
                $sql = "INSERT INTO students (user_id, student_number, class) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iss", $user_id, $student_number, $class);
                $stmt->execute();
            }
            
            // إذا كان ولي أمر، أضف بيانات ولي الأمر
            if ($user_type == 'parent') {
                $student_id = $_POST['student_id'];
                $relationship = $_POST['relationship'];
                $sql = "INSERT INTO parents (user_id, student_id, relationship) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iis", $user_id, $student_id, $relationship);
                $stmt->execute();
            }
            
            header('Location: users.php?success=1');
            exit();
        } else {
            $error = "خطأ في إضافة المستخدم";
        }
    }
    
    if ($_POST['action'] == 'delete_user') {
        $user_id = $_POST['user_id'];
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        header('Location: users.php?success=2');
        exit();
    }
}

// جلب قائمة الطلاب (لربطهم بأولياء الأمور)
$students_sql = "SELECT s.id, u.full_name, s.student_number FROM students s JOIN users u ON s.user_id = u.id ORDER BY u.full_name";
$students_result = $conn->query($students_sql);

// جلب جميع المستخدمين
$users_sql = "SELECT u.*, s.student_number, s.class, p.relationship, 
              (SELECT COUNT(*) FROM parents WHERE student_id = s.id) as parent_count
              FROM users u 
              LEFT JOIN students s ON u.id = s.user_id 
              LEFT JOIN parents p ON u.id = p.user_id
              ORDER BY u.created_at DESC";
$users_result = $conn->query($users_sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين - نظام إدارة الحضور والغياب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dashboard-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin: 20px 0;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 30px;
        }
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .table-custom {
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="dashboard-container">
            <div class="header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2><i class="fas fa-users"></i> إدارة المستخدمين</h2>
                        <p class="mb-0">مرحباً <?php echo $_SESSION['username']; ?></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="dashboard.php" class="btn btn-light me-2">
                            <i class="fas fa-arrow-left"></i> العودة للوحة الرئيسية
                        </a>
                        <a href="../logout.php" class="btn btn-light">
                            <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="p-4">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <?php 
                        if ($_GET['success'] == 1) echo 'تم إضافة المستخدم بنجاح';
                        elseif ($_GET['success'] == 2) echo 'تم حذف المستخدم بنجاح';
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- إضافة مستخدم جديد -->
                    <div class="col-md-4">
                        <div class="card table-custom">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-user-plus"></i> إضافة مستخدم جديد</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="add_user">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">نوع المستخدم</label>
                                        <select class="form-select" name="user_type" id="user_type" required>
                                            <option value="">اختر نوع المستخدم</option>
                                            <option value="admin">مدير</option>
                                            <option value="student">طالب</option>
                                            <option value="parent">ولي أمر</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">اسم المستخدم</label>
                                        <input type="text" class="form-control" name="username" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">كلمة المرور</label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">الاسم الكامل</label>
                                        <input type="text" class="form-control" name="full_name" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">البريد الإلكتروني</label>
                                        <input type="email" class="form-control" name="email">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">رقم الهاتف</label>
                                        <input type="text" class="form-control" name="phone">
                                    </div>
                                    
                                    <!-- حقول إضافية للطالب -->
                                    <div id="student_fields" style="display: none;">
                                        <div class="mb-3">
                                            <label class="form-label">رقم الطالب</label>
                                            <input type="text" class="form-control" name="student_number">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">الصف</label>
                                            <input type="text" class="form-control" name="class">
                                        </div>
                                    </div>
                                    
                                    <!-- حقول إضافية لولي الأمر -->
                                    <div id="parent_fields" style="display: none;">
                                        <div class="mb-3">
                                            <label class="form-label">الطالب</label>
                                            <select class="form-select" name="student_id">
                                                <option value="">اختر الطالب</option>
                                                <?php while ($student = $students_result->fetch_assoc()): ?>
                                                    <option value="<?php echo $student['id']; ?>">
                                                        <?php echo $student['full_name']; ?> - <?php echo $student['student_number']; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">صلة القرابة</label>
                                            <input type="text" class="form-control" name="relationship" placeholder="أب، أم، أخ، إلخ">
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-custom w-100">
                                        <i class="fas fa-plus"></i> إضافة المستخدم
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- قائمة المستخدمين -->
                    <div class="col-md-8">
                        <div class="card table-custom">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-list"></i> قائمة المستخدمين</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>الاسم</th>
                                                <th>اسم المستخدم</th>
                                                <th>النوع</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>التاريخ</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = $users_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                    <td>
                                                        <?php 
                                                        switch($user['user_type']) {
                                                            case 'admin': echo '<span class="badge bg-danger">مدير</span>'; break;
                                                            case 'student': echo '<span class="badge bg-primary">طالب</span>'; break;
                                                            case 'parent': echo '<span class="badge bg-success">ولي أمر</span>'; break;
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                                    <td>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                                            <input type="hidden" name="action" value="delete_user">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i> حذف
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
            const parentFields = document.getElementById('parent_fields');
            
            studentFields.style.display = 'none';
            parentFields.style.display = 'none';
            
            if (this.value === 'student') {
                studentFields.style.display = 'block';
            } else if (this.value === 'parent') {
                parentFields.style.display = 'block';
            }
        });
    </script>
</body>
</html> 