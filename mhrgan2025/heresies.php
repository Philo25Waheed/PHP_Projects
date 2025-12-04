<?php
// Database connection settings (edit as needed)
$host = 'localhost'; // Database host
$user = 'root';     // Database username
$pass = '';         // Database password
$db   = 'coptic_patriarchs'; // Database name

// Connect to MySQL database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Fetch all heresies
$sql = 'SELECT * FROM heresies';
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>البدع والهرطقات</title>
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Theme toggle icon button -->
    <button class="theme-toggle" id="themeToggle" title="تبديل الوضع الليلي/النهاري" aria-label="تبديل الوضع الليلي/النهاري">
        <span id="themeIcon">🌙</span>
    </button>
    <div class="container">
        <a href="index.php" class="back-link">&larr; عودة إلى القائمة الرئيسية</a>
        <h1>البدع والهرطقات في تاريخ الكنيسة القبطية</h1>
        <div class="heresy-section">
            <?php
            // Loop through heresies and display each one
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="heresy-card">';
                    echo '<div class="heresy-name">' . htmlspecialchars($row['name']) . '</div>';
                    echo '<div class="heresy-desc">' . htmlspecialchars($row['description']) . '</div>';
                    echo '<div class="heresy-response">' . htmlspecialchars($row['response']) . '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div>لا يوجد بدع أو هرطقات في قاعدة البيانات.</div>';
            }
            ?>
        </div>
    </div>

    <script>
    // Theme toggle logic with icon
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    function setTheme(theme) {
        document.body.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        themeIcon.textContent = theme === 'dark' ? '☀️' : '🌙';
    }
    // On load, set theme from localStorage or default to light
    (function() {
        const saved = localStorage.getItem('theme');
        if (saved === 'dark') setTheme('dark');
        else setTheme('light');
    })();
    themeToggle.onclick = function() {
        const current = document.body.getAttribute('data-theme');
        setTheme(current === 'dark' ? 'light' : 'dark');
    };
    </script>
    
</body>
</html>
<?php
// Close the database connection
$conn->close();
// End of file
