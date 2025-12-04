<?php
// setup.php - create database and notes table
require_once __DIR__ . '/config.php';

$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$dbname = DB_NAME;

// Connect without selecting database (it might not exist yet)
$conn = @new mysqli($host, $user, $pass);
if ($conn->connect_errno) {
    echo "<h2>Cannot connect to MySQL</h2>";
    echo "<p>Please ensure MySQL is running and credentials in <code>config.php</code> are correct.</p>";
    echo "<pre>" . htmlspecialchars($conn->connect_error) . "</pre>";
    exit;
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS `" . $conn->real_escape_string($dbname) . "` CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_general_ci";
if (! $conn->query($sql)) {
    echo "<h2>Failed to create database</h2>";
    echo "<pre>" . htmlspecialchars($conn->error) . "</pre>";
    exit;
}

// Select database
$conn->select_db($dbname);

// Create notes table
$create = "CREATE TABLE IF NOT EXISTS notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET;

if (! $conn->query($create)) {
    echo "<h2>Failed to create notes table</h2>";
    echo "<pre>" . htmlspecialchars($conn->error) . "</pre>";
    exit;
}

echo "<h2>Setup complete</h2>";
echo "<p>Database <strong>" . htmlspecialchars($dbname) . "</strong> and table <strong>notes</strong> created (or already existed).</p>";
echo "<p><a href=\"notes.php\">Open Notes App</a></p>";

$conn->close();

?>
