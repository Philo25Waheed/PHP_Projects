<?php
// Simple notifications helper. Uses PHPMailer if available, otherwise falls back to mail().
require_once __DIR__ . '/db.php';
$config = require __DIR__ . '/config.php';
global $pdo;
// expose config globally for legacy references
$GLOBALS['config'] = $config;

function sendEmail($toEmail, $toName, $subject, $bodyHtml, $bodyText = '') {
    // Try PHPMailer via Composer if available
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com'; // TODO: configure
            $mail->SMTPAuth = true;
            $mail->Username = 'smtp_user';
            $mail->Password = 'smtp_pass';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($GLOBALS['config']['mail']['from_email'], $GLOBALS['config']['mail']['from_name']);
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $bodyHtml;
            $mail->AltBody = $bodyText ?: strip_tags($bodyHtml);
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer error: ' . $e->getMessage());
            // fallback to mail()
        }
    }

    // Fallback
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . $GLOBALS['config']['mail']['from_name'] . ' <' . $GLOBALS['config']['mail']['from_email'] . '>' . "\r\n";
    return mail($toEmail, $subject, $bodyHtml, $headers);
}

function sendAssignmentNotification($userId, $taskId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT email,name FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $u = $stmt->fetch();
    if (!$u) return false;
    $stmt = $pdo->prepare('SELECT title,deadline FROM tasks WHERE id = ?');
    $stmt->execute([$taskId]);
    $t = $stmt->fetch();
    $subject = 'New Task Assigned: ' . ($t['title'] ?? 'Task');
    $body = '<p>Hi ' . htmlspecialchars($u['name']) . ',</p>';
    $body .= '<p>A new task has been assigned to you: <strong>' . htmlspecialchars($t['title'] ?? '') . '</strong></p>';
    if (!empty($t['deadline'])) $body .= '<p>Deadline: ' . htmlspecialchars($t['deadline']) . '</p>';
    $body .= '<p><a href="' . ($GLOBALS['config']['site']['base_url'] ?? '') . '">Open Meister ToDo</a></p>';
    return sendEmail($u['email'], $u['name'], $subject, $body);
}

function sendDeadlineReminder($userEmail, $userName, $taskTitle, $deadline, $taskId) {
    $subject = 'Reminder: Task deadline approaching - ' . $taskTitle;
    $body = '<p>Hi ' . htmlspecialchars($userName) . ',</p>';
    $body .= '<p>This is a reminder that the task <strong>' . htmlspecialchars($taskTitle) . '</strong> is due on <strong>' . htmlspecialchars($deadline) . '</strong>.</p>';
    $body .= '<p><a href="' . ($GLOBALS['config']['site']['base_url'] ?? '') . '">Open Task</a></p>';
    return sendEmail($userEmail, $userName, $subject, $body);
}

function sendWhatsAppAssignmentIfConfigured($userId, $taskId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT phone,name FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $u = $stmt->fetch();
    if (!$u) return false;
    if (empty($u['phone'])) return false;
    // get task title
    $stmt = $pdo->prepare('SELECT title,deadline FROM tasks WHERE id = ?');
    $stmt->execute([$taskId]);
    $t = $stmt->fetch();
    if (!$t) return false;
    $msg = "New task assigned: {$t['title']}";
    if (!empty($t['deadline'])) $msg .= " — due {$t['deadline']}";
    // use Twilio helper if available
    if (file_exists(__DIR__ . '/whatsapp.php')) {
        require_once __DIR__ . '/whatsapp.php';
        return sendWhatsAppMessage($u['phone'], $msg);
    }
    return false;
}

function sendWhatsAppReminderIfConfigured($userId, $taskId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT phone,name,email FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $u = $stmt->fetch();
    if (!$u) return false;
    if (empty($u['phone'])) return false;
    $stmt = $pdo->prepare('SELECT title,deadline FROM tasks WHERE id = ?');
    $stmt->execute([$taskId]);
    $t = $stmt->fetch();
    if (!$t) return false;
    $msg = "Reminder: Task '{$t['title']}' is due {$t['deadline']}";
    if (file_exists(__DIR__ . '/whatsapp.php')) {
        require_once __DIR__ . '/whatsapp.php';
        return sendWhatsAppMessage($u['phone'], $msg);
    }
    return false;
}
