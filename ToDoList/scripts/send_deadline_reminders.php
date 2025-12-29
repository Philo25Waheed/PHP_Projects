<?php
// Run this script daily (via Task Scheduler) to send reminders for upcoming deadlines.
require_once __DIR__ . '/../api/config.php';
$cfg = require __DIR__ . '/../api/config.php';
$db = $cfg['db'];
try {
    $pdo = new PDO("mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8mb4", $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) { echo "DB connection failed: " . $e->getMessage(); exit(1); }

require_once __DIR__ . '/../api/notifications.php';

$daysAhead = 1; // send reminders for tasks due within next X days
$now = new DateTime();
$end = (new DateTime())->modify("+{$daysAhead} days");

$stmt = $pdo->prepare('SELECT t.id,t.title,t.deadline,t.assigned_to,u.email,u.name FROM tasks t JOIN users u ON u.id = t.assigned_to WHERE t.deadline IS NOT NULL AND t.status != "Completed" AND t.deadline BETWEEN ? AND ?');
$stmt->execute([$now->format('Y-m-d 00:00:00'), $end->format('Y-m-d 23:59:59')]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($tasks as $t) {
    try {
        sendDeadlineReminder($t['email'], $t['name'], $t['title'], $t['deadline'], $t['id']);
        // send WhatsApp reminder if user has phone and Twilio configured
        try { sendWhatsAppReminderIfConfigured($t['assigned_to'] ?? $t['id'], $t['id']); } catch (Exception $e) { /* ignore */ }
        echo "Sent reminder for task {$t['id']} to {$t['email']}\n";
    } catch (Exception $e) {
        echo "Failed reminder for task {$t['id']}: " . $e->getMessage() . "\n";
    }
}

echo "Done. Sent " . count($tasks) . " reminders.\n";
