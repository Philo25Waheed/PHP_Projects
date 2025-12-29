<?php
session_start();
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_GET['action'] ?? '';
    if ($action === 'register') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
            jsonResponse(['error' => 'Missing fields'], 400);
        }
        $email = $data['email'];
        $name = $data['name'];
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        global $pdo;
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) jsonResponse(['error' => 'User exists'], 400);

        $ins = $pdo->prepare('INSERT INTO users (name,email,password,role_id) VALUES (?,?,?,?)');
        $ins->execute([$name, $email, $passwordHash, 3]);
        jsonResponse(['success' => true, 'id' => $pdo->lastInsertId()]);
    }

    if ($action === 'login') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['email']) || empty($data['password'])) jsonResponse(['error'=>'Missing credentials'], 400);
        $email = $data['email'];
        $password = $data['password'];
        global $pdo;
        $stmt = $pdo->prepare('SELECT id,name,email,password,role_id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password'])) jsonResponse(['error'=>'Invalid credentials'], 401);
        $_SESSION['user'] = ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role_id'=>$user['role_id']];
        jsonResponse(['success'=>true,'user'=>$_SESSION['user']]);
    }

    if ($action === 'logout') {
        session_unset(); session_destroy();
        jsonResponse(['success'=>true]);
    }
}

// GET current user
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_SESSION['user'])) {
        jsonResponse(['user'=>$_SESSION['user']]);
    }
    jsonResponse(['user'=>null]);
}
