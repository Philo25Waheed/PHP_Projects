<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header('Location: ../index.php');
    exit();
}

$student_user_id = $_SESSION['user_id'];
// جلب بيانات الطالب
$student_sql = "SELECT s.*, u.full_name, u.email, u.phone FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?";
$stmt = $conn->prepare($student_sql);
$stmt->bind_param("i", $student_user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// جلب التنبيهات
$notifications_sql = "SELECT * FROM notifications WHERE target_type IN ('all', 'students') ORDER BY created_at DESC LIMIT 5";
$notifications = $conn->query($notifications_sql);

// جلب التعليمات
$instructions_sql = "SELECT * FROM instructions WHERE is_active = 1 ORDER BY created_at DESC LIMIT 5";
$instructions = $conn->query($instructions_sql);

// جلب الدرجات
$grades_sql = "SELECT e.title, e.subject, e.exam_date, r.marks_obtained, r.percentage, r.grade FROM exam_results r JOIN exams e ON r.exam_id = e.id WHERE r.student_id = ? ORDER BY e.exam_date DESC";
$grades_stmt = $conn->prepare($grades_sql);
$grades_stmt->bind_param("i", $student['id']);
$grades_stmt->execute();
$grades = $grades_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة الطالب - نظام الحضور والغياب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dashboard-container {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            margin: 20px 0;
        }
        .header {
            background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 30px;
        }
        .section-title {
            color: #185a9d;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .card-custom {
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .table-custom {
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }
        .notification {
            background: #e3fcec;
            border-right: 5px solid #43cea2;
            border-radius: 10px;
            padding: 10px 15px;
            margin-bottom: 10px;
        }
        .instruction {
            background: #f1f8ff;
            border-right: 5px solid #185a9d;
            border-radius: 10px;
            padding: 10px 15px;
            margin-bottom: 10px;
        }
        .grade-badge {
            font-size: 1.1rem;
            padding: 5px 12px;
            border-radius: 8px;
            color: #fff;
        }
        .grade-A { background: #28a745; }
        .grade-B { background: #17a2b8; }
        .grade-C { background: #ffc107; color: #333; }
        .grade-D { background: #fd7e14; }
        .grade-F { background: #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="dashboard-container">
            <div class="header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2><i class="fas fa-user-graduate"></i> لوحة الطالب</h2>
                        <p class="mb-0">مرحباً <?php echo $_SESSION['username']; ?> (<?php echo $student['full_name']; ?>)</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="../logout.php" class="btn btn-light">
                            <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                        </a>
                    </div>
                </div>
            </div>
            <div class="p-4">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="section-title"><i class="fas fa-bell"></i> أحدث التنبيهات</h5>
                        <?php while ($n = $notifications->fetch_assoc()): ?>
                            <div class="notification">
                                <strong><?php echo htmlspecialchars($n['title']); ?></strong><br>
                                <span><?php echo htmlspecialchars($n['message']); ?></span>
                                <div class="text-muted small mt-1"><i class="fas fa-clock"></i> <?php echo date('Y-m-d H:i', strtotime($n['created_at'])); ?></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="col-md-6">
                        <h5 class="section-title"><i class="fas fa-info-circle"></i> التعليمات</h5>
                        <?php while ($ins = $instructions->fetch_assoc()): ?>
                            <div class="instruction">
                                <strong><?php echo htmlspecialchars($ins['title']); ?></strong><br>
                                <span><?php echo htmlspecialchars($ins['content']); ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card card-custom table-custom">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> الدرجات والامتحانات</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>الامتحان</th>
                                                <th>المادة</th>
                                                <th>التاريخ</th>
                                                <th>الدرجة</th>
                                                <th>النسبة</th>
                                                <th>التقدير</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($g = $grades->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($g['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($g['subject']); ?></td>
                                                    <td><?php echo htmlspecialchars($g['exam_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($g['marks_obtained']); ?></td>
                                                    <td><?php echo htmlspecialchars($g['percentage']); ?>%</td>
                                                    <td><span class="grade-badge grade-<?php echo $g['grade']; ?>"><?php echo $g['grade']; ?></span></td>
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