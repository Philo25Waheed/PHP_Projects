<?php
session_start();
require_once __DIR__ . '/db.php';

global $pdo;

// Auth helper
function requireAuth() {
    if (empty($_SESSION['user'])) jsonResponse(['error'=>'Authentication required'],401);
}

// Simple router by action param
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'list') {
        // filters
        $assigned = $_GET['assigned_to'] ?? null;
        $status = $_GET['status'] ?? null;
        $team = $_GET['team_id'] ?? null;
        $q = 'SELECT t.*, u.name as assigned_name FROM tasks t LEFT JOIN users u ON u.id=t.assigned_to WHERE 1=1';
        $params = [];
        if ($assigned) { $q .= ' AND assigned_to = ?'; $params[] = $assigned; }
        if ($status) { $q .= ' AND status = ?'; $params[] = $status; }
        if ($team) { $q .= ' AND team_id = ?'; $params[] = $team; }
        $q .= ' ORDER BY FIELD(priority,"High","Medium","Low"), deadline IS NULL, deadline ASC, created_at DESC';
        $stmt = $pdo->prepare($q);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll();
        jsonResponse(['tasks'=>$tasks]);
    }

    if ($action === 'view' && !empty($_GET['id'])) {
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
        $stmt->execute([$_GET['id']]);
        $task = $stmt->fetch();
        if (!$task) jsonResponse(['error'=>'Not found'],404);
        // subtasks
        $stmt = $pdo->prepare('SELECT * FROM subtasks WHERE task_id = ?'); $stmt->execute([$_GET['id']]); $subs = $stmt->fetchAll();
        // comments
        $stmt = $pdo->prepare('SELECT c.*, u.name FROM comments c JOIN users u ON u.id=c.user_id WHERE c.task_id = ? ORDER BY c.created_at ASC'); $stmt->execute([$_GET['id']]); $comments = $stmt->fetchAll();
        jsonResponse(['task'=>$task,'subtasks'=>$subs,'comments'=>$comments]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAuth();
    // create task
    $data = json_decode(file_get_contents('php://input'), true);
    if ($action === 'create') {
        $title = $data['title'] ?? null;
        if (!$title) jsonResponse(['error'=>'Title required'],400);
        $desc = $data['description'] ?? null;
        $priority = $data['priority'] ?? 'Medium';
        $deadline = $data['deadline'] ?? null;
        $assigned_to = $data['assigned_to'] ?? null;
        $team_id = $data['team_id'] ?? null;
        $created_by = $_SESSION['user']['id'];
        $stmt = $pdo->prepare('INSERT INTO tasks (title,description,priority,assigned_to,created_by,team_id,deadline) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$title,$desc,$priority,$assigned_to,$created_by,$team_id,$deadline]);
        $taskId = $pdo->lastInsertId();
        // Send assignment notification if assigned
        if (!empty($assigned_to)) {
            // notifications helper
            try {
                require_once __DIR__ . '/notifications.php';
                sendAssignmentNotification($assigned_to, $taskId);
                // also send WhatsApp if phone exists and Twilio configured
                try { sendWhatsAppAssignmentIfConfigured($assigned_to, $taskId); } catch (Exception $e) { error_log('WhatsApp notify error: ' . $e->getMessage()); }
            } catch (Exception $e) {
                error_log('Notification error: ' . $e->getMessage());
            }
        }
        // subtasks
        if (!empty($data['subtasks']) && is_array($data['subtasks'])) {
            $ins = $pdo->prepare('INSERT INTO subtasks (task_id,title) VALUES (?,?)');
            foreach ($data['subtasks'] as $s) { $ins->execute([$taskId,$s]); }
        }
        jsonResponse(['success'=>true,'id'=>$taskId]);
    }

    if ($action === 'update') {
        $id = $data['id'] ?? null; if (!$id) jsonResponse(['error'=>'id required'],400);
        $fields = [];
        $params = [];
        foreach (['title','description','priority','status','blocker_reason','progress','assigned_to','deadline'] as $f) {
            if (isset($data[$f])) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
        }
        if (!$fields) jsonResponse(['error'=>'No fields to update'],400);
        $params[] = $id;
        $stmt = $pdo->prepare('UPDATE tasks SET ' . implode(',', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
        jsonResponse(['success'=>true]);
    }

    if ($action === 'comment') {
        $task_id = $data['task_id'] ?? null; $body = $data['body'] ?? null;
        if (!$task_id || !$body) jsonResponse(['error'=>'Missing fields'],400);
        $stmt = $pdo->prepare('INSERT INTO comments (task_id,user_id,body) VALUES (?,?,?)');
        $stmt->execute([$task_id,$_SESSION['user']['id'],$body]);
        jsonResponse(['success'=>true,'id'=>$pdo->lastInsertId()]);
    }

    // file upload endpoint: form-data POST to tasks.php?action=upload&id=123
    if ($action === 'upload') {
        $id = $_GET['id'] ?? null; if (!$id) jsonResponse(['error'=>'id required'],400);
        if (empty($_FILES['file'])) jsonResponse(['error'=>'file missing'],400);
        $f = $_FILES['file'];
        if ($f['error'] !== UPLOAD_ERR_OK) jsonResponse(['error'=>'Upload error'],400);
        $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
        $safe = uniqid() . '.' . $ext;
        $destDir = __DIR__ . '/../uploads'; if (!is_dir($destDir)) mkdir($destDir,0755,true);
        $dest = $destDir . '/' . $safe;
        move_uploaded_file($f['tmp_name'],$dest);
        $stmt = $pdo->prepare('INSERT INTO attachments (task_id,filename,filepath,uploaded_by) VALUES (?,?,?,?)');
        $stmt->execute([$id,$f['name'],$safe,$_SESSION['user']['id']]);
        jsonResponse(['success'=>true,'file'=>$safe]);
    }
}
