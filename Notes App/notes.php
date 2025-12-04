<?php
// Include database connection
require_once __DIR__ . '/db.php';

// Save note (use prepared statement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title !== '' && $content !== '') {
        $stmt = $conn->prepare("INSERT INTO notes (title, content) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param('ss', $title, $content);
            $stmt->execute();
            $stmt->close();
        } else {
            error_log('Prepare failed for INSERT: ' . $conn->error);
        }
    }

    header('Location: notes.php');
    exit;
}

// Delete note (use prepared statement)
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM notes WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        } else {
            error_log('Prepare failed for DELETE: ' . $conn->error);
        }
    }

    header('Location: notes.php');
    exit;
}

// Load notes
$notes = $conn->query("SELECT * FROM notes ORDER BY id DESC");
if (! $notes) {
    // Table might not exist yet — show a friendly link to setup
    $notes = [];
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Modern Notes App</title>

    <style>
        body {
            background: #fafafa;
            font-family: Arial;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 700px;
            background: white;
            margin: auto;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }

        h2 {
            text-align: center;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        textarea {
            height: 100px;
        }

        button {
            padding: 12px 16px;
            margin-top: 10px;
            border: none;
            background: #10b981;
            color: white;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #059669;
        }

        /* Note box */
        .note {
            background: #e0ffee;
            padding: 15px;
            border-radius: 10px;
            margin-top: 12px;
            position: relative;
        }

        .delete {
            position: absolute;
            right: 15px;
            top: 15px;
            padding: 5px 10px;
            background: red;
            color: white;
            border-radius: 6px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Notes App</h2>

    <!-- Add note form -->
    <form method="POST">
        <input type="text" name="title" placeholder="Note title..." required>
        <textarea name="content" placeholder="Write something..." required></textarea>
        <button name="save">Save Note</button>
    </form>

    <!-- Display saved notes -->
    <?php while($n = $notes->fetch_assoc()): ?>
        <div class="note">
            <h3><?php echo htmlspecialchars($n['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($n['content'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
            <a class="delete" href="notes.php?delete=<?php echo (int) ($n['id'] ?? 0); ?>">Delete</a>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
