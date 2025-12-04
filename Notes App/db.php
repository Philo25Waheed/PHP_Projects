<?php
// Database connection using `config.php` constants and friendly error output.
require_once __DIR__ . '/config.php';

// Create mysqli connection to the configured database.
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_errno) {
    // Log technical error for developer debugging
    error_log('DB connect error: ' . $conn->connect_error);

    if (defined('SHOW_DB_ERRORS') && SHOW_DB_ERRORS) {
        $msg = htmlspecialchars($conn->connect_error, ENT_QUOTES, 'UTF-8');
    } else {
        $msg = 'Unable to connect to the database.';
    }

    echo "<!doctype html><html><head><meta charset=\"utf-8\"><title>Database error</title>";
    echo "<style>body{font-family:Arial;margin:30px}pre{background:#f4f4f4;padding:10px;border-radius:6px}</style>";
    echo "<h2>Cannot connect to the database</h2>";
    echo "<p>The application could not connect to MySQL. Common causes:</p>";
    echo "<ul>";
    echo "<li>MySQL server is not running. Start it in the XAMPP Control Panel.</li>";
    echo "<li>The database name, username, or password in <code>config.php</code> is incorrect.</li>";
    echo "<li>MySQL is running on a non-standard port or blocked by firewall.</li>";
    echo "</ul>";
    echo "<p>Error (for debugging):</p>";
    echo "<pre>" . $msg . "</pre>";
    echo "<p>To create the database and table, open <a href=\"setup.php\">setup.php</a>.</p>";
    echo "</html>";

    exit;
}

// Ensure UTF-8 charset
if (! $conn->set_charset(DB_CHARSET)) {
    error_log('Unable to set DB charset to ' . DB_CHARSET . ': ' . $conn->error);
}

// Backwards-compat variable name used elsewhere
$mysqli = $conn;

?>
