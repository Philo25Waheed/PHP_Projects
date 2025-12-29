<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

$admin_id = $_SESSION['user_id'];

// معالجة تسجيل الحضور
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'record_attendance') {
        $student_id = $_POST['student_id'];
        $date = $_POST['date'];
        $status = $_POST['status'];
        $notes = $_POST['notes'] ?? '';
        
        // التحقق من عدم وجود تسجيل سابق لنفس اليوم
        $check_sql = "SELECT id FROM attendance WHERE student_id = ? AND date = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("is", $student_id, $date);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // تحديث التسجيل الموجود
            $update_sql = "UPDATE attendance SET status = ?, notes = ? WHERE student_id = ? AND date = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssis", $status, $notes, $student_id, $date);
            $update_stmt->execute();
        } else {
            // إدخال تسجيل جديد
            $insert_sql = "INSERT INTO attendance (student_id, date, status, notes, recorded_by) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("isssi", $student_id, $date, $status, $notes, $admin_id);
            $insert_stmt->execute();
        }
        
        header('Location: dashboard.php?success=1');
        exit();
    }
    
    if ($_POST['action'] == 'add_notification') {
        $title = $_POST['title'];
        $message = $_POST['message'];
        $target_type = $_POST['target_type'];
        
        $sql = "INSERT INTO notifications (title, message, target_type) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $title, $message, $target_type);
        $stmt->execute();
        
        header('Location: dashboard.php?success=2');
        exit();
    }
}

// جلب قائمة الطلاب
$students_sql = "SELECT s.*, u.full_name FROM students s 
                 JOIN users u ON s.user_id = u.id 
                 ORDER BY u.full_name";
$students_result = $conn->query($students_sql);

// جلب إحصائيات الحضور اليوم
$today = date('Y-m-d');
$today_stats_sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
                    FROM attendance 
                    WHERE date = ?";
$today_stats_stmt = $conn->prepare($today_stats_sql);
$today_stats_stmt->bind_param("s", $today);
$today_stats_stmt->execute();
$today_stats = $today_stats_stmt->get_result()->fetch_assoc();

// جلب التنبيهات
$notifications_sql = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10";
$notifications_result = $conn->query($notifications_sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المدير - نظام إدارة الحضور والغياب</title>
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
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin: 10px 0;
            text-align: center;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
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
                        <h2><i class="fas fa-user-shield"></i> لوحة تحكم المدير</h2>
                        <p class="mb-0">مرحباً <?php echo $_SESSION['username']; ?></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="users.php" class="btn btn-light me-2">
                            <i class="fas fa-users"></i> إدارة المستخدمين
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
                        if ($_GET['success'] == 1) echo 'تم تسجيل الحضور بنجاح';
                        elseif ($_GET['success'] == 2) echo 'تم إضافة التنبيه بنجاح';
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- إحصائيات اليوم -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $today_stats['total'] ?? 0; ?></div>
                            <div>إجمالي الطلاب</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                            <div class="stats-number"><?php echo $today_stats['present'] ?? 0; ?></div>
                            <div>حاضر</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                            <div class="stats-number"><?php echo $today_stats['absent'] ?? 0; ?></div>
                            <div>غائب</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                            <div class="stats-number"><?php echo $today_stats['late'] ?? 0; ?></div>
                            <div>متأخر</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- تسجيل الحضور -->
                    <div class="col-md-8">
                        <div class="card table-custom">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> تسجيل الحضور والغياب</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="record_attendance">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">التاريخ</label>
                                            <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">الطالب</label>
                                            <select class="form-select" name="student_id" required>
                                                <option value="">اختر الطالب</option>
                                                <?php while ($student = $students_result->fetch_assoc()): ?>
                                                    <option value="<?php echo $student['id']; ?>">
                                                        <?php echo $student['full_name']; ?> - <?php echo $student['student_number']; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">الحالة</label>
                                            <select class="form-select" name="status" required>
                                                <option value="">اختر الحالة</option>
                                                <option value="present">حاضر</option>
                                                <option value="absent">غائب</option>
                                                <option value="late">متأخر</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">ملاحظات</label>
                                        <textarea class="form-control" name="notes" rows="2"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-custom">
                                        <i class="fas fa-save"></i> تسجيل الحضور
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- إضافة تنبيه -->
                    <div class="col-md-4">
                        <div class="card table-custom">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-bell"></i> إضافة تنبيه جديد</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="add_notification">
                                    <div class="mb-3">
                                        <label class="form-label">عنوان التنبيه</label>
                                        <input type="text" class="form-control" name="title" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">نص التنبيه</label>
                                        <textarea class="form-control" name="message" rows="3" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">المستهدف</label>
                                        <select class="form-select" name="target_type" required>
                                            <option value="all">الجميع</option>
                                            <option value="students">الطلاب فقط</option>
                                            <option value="parents">أولياء الأمور فقط</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-custom">
                                        <i class="fas fa-plus"></i> إضافة التنبيه
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- التنبيهات الأخيرة -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card table-custom">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-bell"></i> التنبيهات الأخيرة</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>العنوان</th>
                                                <th>الرسالة</th>
                                                <th>المستهدف</th>
                                                <th>التاريخ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($notification = $notifications_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($notification['title']); ?></td>
                                                    <td><?php echo htmlspecialchars(substr($notification['message'], 0, 50)) . '...'; ?></td>
                                                    <td>
                                                        <?php 
                                                        switch($notification['target_type']) {
                                                            case 'all': echo 'الجميع'; break;
                                                            case 'students': echo 'الطلاب'; break;
                                                            case 'parents': echo 'أولياء الأمور'; break;
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?php echo date('Y-m-d H:i', strtotime($notification['created_at'])); ?></td>
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
</body>
</html> 