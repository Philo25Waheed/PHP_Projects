<?php
// Simple PDO wrapper
$config = require __DIR__ . '/config.php';
$dbConf = $config['db'];

try {
    $dsn = "mysql:host={$dbConf['host']};dbname={$dbConf['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConf['user'], $dbConf['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed', 'details' => $e->getMessage()]);
    exit;
}

function jsonResponse($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}
