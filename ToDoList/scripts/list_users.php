<?php
require_once __DIR__ . '/../api/config.php';
$cfg = require __DIR__ . '/../api/config.php';
$db = $cfg['db'];
try {
    $pdo = new PDO("mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8mb4", $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) { echo "DB connection failed: " . $e->getMessage() . "\n"; exit(1); }

$stmt = $pdo->query('SELECT id,name,email,role_id,team_id,phone FROM users ORDER BY id ASC LIMIT 100');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) { echo "No users found.\n"; exit(0); }
echo str_pad('id',5) . str_pad('role',8) . str_pad('name',30) . "email\n";
foreach ($rows as $r) {
    $role = $r['role_id'];
    echo str_pad($r['id'],5) . str_pad($role,8) . str_pad(substr($r['name'],0,30),30) . $r['email'] . PHP_EOL;
}
