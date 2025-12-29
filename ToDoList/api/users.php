<?php
session_start();
require_once __DIR__ . '/db.php';

// Simple admin-only checks
function requireAdmin() {
    // If there are no users yet allow bootstrap (first admin creation)
    global $pdo;
    try {
        $c = $pdo->query('SELECT COUNT(*) AS c FROM users')->fetch();
        if ($c && intval($c['c']) === 0) return; // allow bootstrap
    } catch (Exception $e) {
        // ignore — will be handled by normal check below
    }
    if (empty($_SESSION['user']) || $_SESSION['user']['role_id'] != 1) {
        jsonResponse(['error'=>'Admin required'],403);
    }
}

$method = $_SERVER['REQUEST_METHOD'];
global $pdo;

if ($method === 'GET') {
    // list users
    $stmt = $pdo->query('SELECT u.id,u.name,u.email,r.name as role, t.name as team FROM users u LEFT JOIN roles r ON r.id=u.role_id LEFT JOIN teams t ON t.id=u.team_id');
    $users = $stmt->fetchAll();
    jsonResponse(['users'=>$users]);
}

if ($method === 'POST') {
    // require admin unless this is the bootstrap case (first user)
    requireAdmin();
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) jsonResponse(['error' => 'Invalid JSON payload'],400);
    if (empty($data['email']) || empty($data['password']) || empty($data['name'])) jsonResponse(['error'=>'Missing fields (name, email, password required)'],400);
    // basic email validation
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) jsonResponse(['error'=>'Invalid email address'],400);
    try {
        // duplicate email check
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) jsonResponse(['error'=>'A user with that email already exists'],409);

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $role = isset($data['role_id']) ? intval($data['role_id']) : 3;
        $team = isset($data['team_id']) && $data['team_id'] !== '' ? $data['team_id'] : null;
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role_id,team_id) VALUES (?,?,?,?,?)');
        $stmt->execute([$data['name'],$data['email'],$passwordHash,$role,$team]);
        jsonResponse(['success'=>true,'id'=>$pdo->lastInsertId()]);
    } catch (PDOException $e) {
        // log and return safe message
        @file_put_contents(__DIR__ . '/../logs/api.log', date('c') . " users.php POST error: " . $e->getMessage() . "\n", FILE_APPEND);
        jsonResponse(['error'=>'Database error while creating user'],500);
    } catch (Exception $e) {
        @file_put_contents(__DIR__ . '/../logs/api.log', date('c') . " users.php POST error: " . $e->getMessage() . "\n", FILE_APPEND);
        jsonResponse(['error'=>'Unexpected error'],500);
    }
}

// More endpoints (update/delete) can be added similarly
