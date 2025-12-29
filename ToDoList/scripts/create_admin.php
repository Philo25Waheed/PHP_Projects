<?php
// Usage: php create_admin.php "Admin Name" admin@example.com password123
require_once __DIR__ . '/../api/config.php';
$cfg = require __DIR__ . '/../api/config.php';
$db = $cfg['db'];
try {
    $pdo = new PDO("mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8mb4", $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) { echo "DB connection failed: " . $e->getMessage(); exit(1); }

$name = $argv[1] ?? null; $email = $argv[2] ?? null; $pass = $argv[3] ?? null;
if (!$name || !$email || !$pass) { echo "Usage: php create_admin.php \"Admin Name\" admin@example.com password\n"; exit(1); }

$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO users (name,email,password,role_id) VALUES (?,?,?,1)');
$stmt->execute([$name,$email,$hash]);
echo "Created admin with id " . $pdo->lastInsertId() . "\n";
