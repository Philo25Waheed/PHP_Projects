<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'parent') {
    header('Location: ../index.php');
    exit();
}

$parent_user_id = $_SESSION['user_id'];
// جلب بيانات ولي الأمر
$parent_sql = "SELECT p.*, u.full_name FROM parents p JOIN users u ON p.user_id = u.id WHERE p.user_id = ?";
$stmt = $conn->prepare($parent_sql);
$stmt->bind_param("i", $parent_user_id);
$stmt->execute();
$parent = $stmt->get_result()->fetch_assoc();

// جلب بيانات الطالب المرتبط
$student_sql = "SELECT s.*, u.full_name, u.email, u.phone FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?";
$student_stmt = $conn->prepare($student_sql);
$student_stmt->bind_param("i", $parent['student_id']);
$student_stmt->execute();
$student = $student_stmt->get_result()->fetch_assoc();

// جلب حضور الطالب
$attendance_sql = "SELECT date, status, notes FROM attendance WHERE student_id = ? ORDER BY date DESC LIMIT 10";
$attendance_stmt = $conn->prepare($attendance_sql);
$attendance_stmt->bind_param("i", $student['id']);
$attendance_stmt->execute();
$attendance = $attendance_stmt->get_result();

// جلب الدرجات
$grades_sql = "SELECT e.title, e.subject, e.exam_date, r.marks_obtained, r.percentage, r.grade FROM exam_results r JOIN exams e ON r.exam_id = e.id WHERE r.student_id = ? ORDER BY e.exam_date DESC";
$grades_stmt = $conn->prepare($grades_sql);
$grades_stmt->bind_param("i", $student['id']);
$grades_stmt->execute();
$grades = $grades_stmt->get_result();

// جلب أحدث التنبيهات
$notifications_sql = "SELECT * FROM notifications WHERE target_type IN ('all', 'parents') ORDER BY created_at DESC LIMIT 5";
$notifications = $conn->query($notifications_sql);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة ولي الأمر - نظام الحضور والغياب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #ffb347 0%, #ffcc33 100%);
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
            background: linear-gradient(135deg, #ffb347 0%, #ffcc33 100%);
            color: #333;
            border-radius: 20px 20px 0 0;
            padding: 30px;
        }
        .section-title {
            color: #b8860b;
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
            background: #fffbe6;
            border-right: 5px solid #ffcc33;
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
        .status-present { color: #28a745; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
        .status-late { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="dashboard-container">
            <div class="header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2><i class="fas fa-user-friends"></i> لوحة ولي الأمر</h2>
                        <p class="mb-0">مرحباً <?php echo $_SESSION['username']; ?> (<?php echo $parent['full_name']; ?>)</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="../logout.php" class="btn btn-light">
                            <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                        </a>
                    </div>
                </div>
            </div>
            <div class="p-4">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="section-title"><i class="fas fa-child"></i> بيانات الطالب</h5>
                        <div class="card card-custom p-3">
                            <strong>الاسم:</strong> <?php echo $student['full_name']; ?><br>
                            <strong>الصف:</strong> <?php echo $student['class']; ?><br>
                            <strong>البريد الإلكتروني:</strong> <?php echo $student['email']; ?><br>
                            <strong>رقم الهاتف:</strong> <?php echo $student['phone']; ?><br>
                        </div>
                    </div>
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
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <div class="card card-custom table-custom">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> سجل الحضور الأخير</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>التاريخ</th>
                                                <th>الحالة</th>
                                                <th>ملاحظات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($a = $attendance->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($a['date']); ?></td>
                                                    <td class="status-<?php echo $a['status']; ?>">
                                                        <?php 
                                                        switch($a['status']) {
                                                            case 'present': echo 'حاضر'; break;
                                                            case 'absent': echo 'غائب'; break;
                                                            case 'late': echo 'متأخر'; break;
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($a['notes']); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-custom table-custom">
                            <div class="card-header bg-success text-white">
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