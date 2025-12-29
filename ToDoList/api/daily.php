<?php
session_start();
require_once __DIR__ . '/db.php';
global $pdo;

function requireAuth() {
    if (empty($_SESSION['user'])) jsonResponse(['error'=>'Authentication required'],401);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // list daily todos; admins can view all with ?user_id= or date=
    $userId = $_GET['user_id'] ?? null;
    $date = $_GET['date'] ?? date('Y-m-d');
    if (!$userId) {
        if (empty($_SESSION['user'])) jsonResponse(['error'=>'Authentication required'],401);
        $userId = $_SESSION['user']['id'];
    }
    $stmt = $pdo->prepare('SELECT * FROM daily_todos WHERE user_id = ? AND date = ? ORDER BY id DESC');
    $stmt->execute([$userId,$date]);
    $rows = $stmt->fetchAll();
    jsonResponse(['todos'=>$rows]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);
    $title = $data['title'] ?? null; $date = $data['date'] ?? date('Y-m-d');
    if (!$title) jsonResponse(['error'=>'Title required'],400);
    $userId = $_SESSION['user']['id'];
    $stmt = $pdo->prepare('INSERT INTO daily_todos (user_id,date,title) VALUES (?,?,?)');
    $stmt->execute([$userId,$date,$title]);
    jsonResponse(['success'=>true,'id'=>$pdo->lastInsertId()]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    requireAuth();
    parse_str(file_get_contents('php://input'), $data);
    $id = $data['id'] ?? null; if (!$id) jsonResponse(['error'=>'id required'],400);
    $fields = [];$params=[];
    if (isset($data['title'])) { $fields[] = 'title = ?'; $params[] = $data['title']; }
    if (isset($data['is_done'])) { $fields[] = 'is_done = ?'; $params[] = $data['is_done']; }
    if (!$fields) jsonResponse(['error'=>'No fields'],400);
    $params[] = $id;
    $stmt = $pdo->prepare('UPDATE daily_todos SET ' . implode(', ', $fields) . ' WHERE id = ?');
    $stmt->execute($params);
    jsonResponse(['success'=>true]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    requireAuth();
    parse_str(file_get_contents('php://input'), $data);
    $id = $data['id'] ?? null; if (!$id) jsonResponse(['error'=>'id required'],400);
    $stmt = $pdo->prepare('DELETE FROM daily_todos WHERE id = ?');
    $stmt->execute([$id]);
    jsonResponse(['success'=>true]);
}
