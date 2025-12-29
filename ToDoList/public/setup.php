<?php
// Simple local setup & health page for the ToDoList project
// WARNING: Intended for local development only. Do not expose in production.
session_start();

require_once __DIR__ . '/../api/config.php';
require_once __DIR__ . '/../api/db.php';

$cfg = require __DIR__ . '/../api/config.php';
$dbConf = $cfg['db'];
// Protect setup page if setup_key is set in config
$setupKey = $cfg['setup_key'] ?? '';
if ($setupKey) {
    $provided = $_GET['key'] ?? $_POST['key'] ?? '';
    if ($provided !== $setupKey) {
        http_response_code(403);
        echo "<h2>Setup Protected</h2><p>Provide valid setup key.</p>";
        exit;
    }
}

function tableExists($pdo, $table) {
    global $dbConf;
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = ? AND table_name = ?");
    $stmt->execute([$dbConf['dbname'], $table]);
    $r = $stmt->fetch();
    return ($r && $r['c'] > 0);
}

function columnExists($pdo, $table, $column) {
    global $dbConf;
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?");
    $stmt->execute([$dbConf['dbname'], $table, $column]);
    $r = $stmt->fetch();
    return ($r && $r['c'] > 0);
}

$messages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'import_schema') {
        $sql = @file_get_contents(__DIR__ . '/../migrations/schema.sql');
        if ($sql === false) { $messages[] = 'Could not read migrations/schema.sql'; }
        else {
            // naive split on semicolon
            $parts = array_filter(array_map('trim', explode(';', $sql)));
            $count = 0;
            foreach ($parts as $p) {
                // skip USE/CREATE DATABASE statements
                if (preg_match('/^\s*(USE|CREATE DATABASE)\b/i', $p)) continue;
                try { $pdo->exec($p); $count++; } catch (Exception $e) { $messages[] = 'Error executing statement: ' . $e->getMessage(); }
            }
            $messages[] = "Executed {$count} statements from schema.sql";
        }
    } elseif ($action === 'import_sample') {
        $sql = @file_get_contents(__DIR__ . '/../migrations/sample_data.sql');
        if ($sql === false) { $messages[] = 'Could not read migrations/sample_data.sql'; }
        else {
            $parts = array_filter(array_map('trim', explode(';', $sql)));
            $count = 0;
            foreach ($parts as $p) {
                if ($p === '') continue;
                try { $pdo->exec($p); $count++; } catch (Exception $e) { $messages[] = 'Error: ' . $e->getMessage(); }
            }
            $messages[] = "Inserted sample data ({$count} statements)";
        }
    } elseif ($action === 'add_phone') {
        $alter = @file_get_contents(__DIR__ . '/../migrations/alter_add_phone.sql');
        if ($alter === false) { $messages[] = 'Could not read migrations/alter_add_phone.sql'; }
        else {
            try { $pdo->exec($alter); $messages[] = 'Applied alter_add_phone.sql'; } catch (Exception $e) { $messages[] = 'Error: ' . $e->getMessage(); }
        }
    } elseif ($action === 'create_admin') {
        $name = $_POST['admin_name'] ?? '';
        $email = $_POST['admin_email'] ?? '';
        $pass = $_POST['admin_pass'] ?? '';
        if (!$name || !$email || !$pass) { $messages[] = 'Provide name, email and password to create admin.'; }
        else {
            try {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role_id) VALUES (?,?,?,1)');
                $stmt->execute([$name,$email,$hash]);
                $messages[] = 'Created admin with id ' . $pdo->lastInsertId();
            } catch (Exception $e) { $messages[] = 'Error creating admin: ' . $e->getMessage(); }
        }
    }
}

// Status checks
$requiredTables = ['users','tasks','teams','daily_todos'];
$tableStatus = [];
foreach ($requiredTables as $t) { $tableStatus[$t] = tableExists($pdo, $t); }

$composerAutoload = file_exists(__DIR__ . '/../vendor/autoload.php');
$phPMailerAvailable = false;
if ($composerAutoload) {
    $autoload = require __DIR__ . '/../vendor/autoload.php';
    $phPMailerAvailable = class_exists('\PHPMailer\PHPMailer\PHPMailer');
}

$phoneColumn = columnExists($pdo, 'users', 'phone');

?><!doctype html>
<html><head><meta charset="utf-8"><title>Setup & Health - Meister ToDo</title>
<style>body{font-family:Arial,Helvetica,sans-serif;margin:20px}table{border-collapse:collapse}td,th{padding:6px;border:1px solid #ddd}</style>
</head><body>
<h1>Setup & Health - Meister ToDo</h1>
<?php foreach ($messages as $m) { echo '<div style="padding:8px;background:#eef;border:1px solid #99c;margin-bottom:8px">' . htmlentities($m) . '</div>'; } ?>

<h2>Configuration</h2>
<table>
<tr><th>Item</th><th>Value</th></tr>
<tr><td>Database host</td><td><?php echo htmlentities($dbConf['host']); ?></td></tr>
<tr><td>Database name</td><td><?php echo htmlentities($dbConf['dbname']); ?></td></tr>
<tr><td>DB connection</td><td><?php echo ($pdo ? 'OK' : 'Failed'); ?></td></tr>
<tr><td>Composer autoload</td><td><?php echo $composerAutoload ? 'vendor/autoload.php found' : 'Not installed'; ?></td></tr>
<tr><td>PHPMailer</td><td><?php echo $phPMailerAvailable ? 'Available' : 'Not detected'; ?></td></tr>
<tr><td>WhatsApp phone column</td><td><?php echo $phoneColumn ? 'users.phone exists' : 'Not present'; ?></td></tr>
</table>

<h2>Tables</h2>
<table>
<tr><th>Table</th><th>Exists</th></tr>
<?php foreach ($tableStatus as $t=>$ok) { echo '<tr><td>' . htmlentities($t) . '</td><td>' . ($ok ? 'Yes' : 'No') . '</td></tr>'; } ?>
</table>

<h2>Actions</h2>
<form method="post" style="margin-bottom:12px">
    <input type="hidden" name="action" value="import_schema">
    <button type="submit">Import `migrations/schema.sql`</button>
    <span style="color:#666;margin-left:8px">Creates database schema (safe for local dev)</span>
</form>

<form method="post" style="margin-bottom:12px">
    <input type="hidden" name="action" value="import_sample">
    <button type="submit">Import `migrations/sample_data.sql`</button>
</form>

<form method="post" style="margin-bottom:12px">
    <input type="hidden" name="action" value="add_phone">
    <button type="submit">Apply `migrations/alter_add_phone.sql` (add phone column)</button>
    <span style="color:#666;margin-left:8px">Adds `phone` column to `users` table for WhatsApp</span>
</form>

<h3>Create Admin</h3>
<form method="post">
    <input type="hidden" name="action" value="create_admin">
    <label>Name: <input name="admin_name"></label><br>
    <label>Email: <input name="admin_email"></label><br>
    <label>Password: <input name="admin_pass" type="password"></label><br>
    <button type="submit">Create Admin</button>
    <span style="color:#666;margin-left:8px">Inserts user with role_id=1</span>
</form>

<p style="margin-top:18px;color:#777">Note: This page runs SQL directly and is intended for local development only. Remove or protect it before publishing.</p>
</body></html>
