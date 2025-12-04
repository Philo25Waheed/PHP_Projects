<?php 
// Include database connection
include "db.php";

// Add new task
if (isset($_POST['add'])) {
    $task = trim($_POST['task']); // Get user input
    if ($task !== '') {
        $stmt = $conn->prepare("INSERT INTO tasks (task) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param('s', $task);
            if (!$stmt->execute()) {
                error_log('Insert failed: ' . $stmt->error);
                die('Insert failed: ' . $stmt->error);
            }
            $stmt->close();
            header('Location: index.php'); // Refresh page
            exit;
        } else {
            die('Prepare failed: ' . $conn->error);
        }
    }
}

// Delete a task
if (isset($_GET['delete'])) {
    $id = $_GET['delete']; // Task ID
    $conn->query("DELETE FROM tasks WHERE id=$id");
    header("Location: index.php");
}

// Get all tasks from DB
$tasks = $conn->query("SELECT * FROM tasks");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modern To-Do List</title>

    <style>
        /* General page styling */
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fc;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Add task input */
        .input-box {
            display: flex;
            gap: 10px;
        }

        input {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        button {
            padding: 12px 16px;
            border: none;
            background: #3b82f6;
            color: white;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #2563eb;
        }

        /* Task item style */
        .task {
            background: #eef3ff;
            padding: 12px;
            margin-top: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
        }

        .delete-btn {
            background: red;
            padding: 5px 10px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
        }

        .delete-btn:hover {
            background: darkred;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>To-Do List</h2>

    <!-- Form to add task -->
    <form method="POST">
        <div class="input-box">
            <input type="text" name="task" placeholder="Write a task..." required>
            <button type="submit" name="add">Add</button>
        </div>
    </form>

    <!-- Display tasks -->
    <?php while($row = $tasks->fetch_assoc()): ?>
        <div class="task">
            <span><?php echo $row['task']; ?></span>
            <a class="delete-btn" href="index.php?delete=<?php echo $row['id']; ?>">X</a>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
