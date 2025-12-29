<?php
session_start();
require_once __DIR__ . '/db.php';
global $pdo;

function requireAdmin(){ if (empty($_SESSION['user']) || $_SESSION['user']['role_id'] != 1) jsonResponse(['error'=>'Admin required'],403); }

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('SELECT * FROM teams ORDER BY name ASC');
    jsonResponse(['teams' => $stmt->fetchAll()]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? null; $desc = $data['description'] ?? null;
    if (!$name) jsonResponse(['error'=>'Name required'],400);
    $stmt = $pdo->prepare('INSERT INTO teams (name,description) VALUES (?,?)');
    $stmt->execute([$name,$desc]);
    jsonResponse(['success'=>true,'id'=>$pdo->lastInsertId()]);
}
