<?php
// Get patriarch ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0; // Get id from query string
if ($id <= 0) {
    die('معرف البطريرك غير صحيح.'); // Invalid ID
}

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

// Fetch patriarch info
$stmt = $conn->prepare('SELECT * FROM patriarchs WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$patriarch = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patriarch) {
    die('البطريرك غير موجود.'); // Patriarch not found
}

// Fetch heresies faced by this patriarch
$sql = 'SELECT h.name, h.description, h.response FROM heresies h
        JOIN patriarchs_heresies ph ON h.id = ph.heresy_id
        WHERE ph.patriarch_id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$heresies = $stmt->get_result();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($patriarch['name']); ?></title>
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="style.css">
    <!-- Link to Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body>
    <!-- Theme toggle icon button -->
    <button class="theme-toggle" id="themeToggle" title="تبديل الوضع الليلي/النهاري" aria-label="تبديل الوضع الليلي/النهاري">
        <span id="themeIcon">🌙</span>
    </button>
    <div class="container">
        <a href="index.php" class="back-link">&larr; عودة إلى القائمة</a>
        <div class="patriarch-header">
            <img class="animate__animated animate__fadeInUp" src="<?php echo $patriarch['image'] ? htmlspecialchars($patriarch['image']) : 'https://via.placeholder.com/110x110?text=No+Image'; ?>" alt="<?php echo htmlspecialchars($patriarch['name']); ?>">
            <div class="patriarch-info">
                <div class="patriarch-name"><?php echo htmlspecialchars($patriarch['name']); ?></div>
                <div class="patriarch-period"><?php echo htmlspecialchars($patriarch['period']); ?></div>
                <div class="patriarch-bio-title">نبذة عن حياة البطريرك:</div>
                <div class="patriarch-bio"><?php echo nl2br(htmlspecialchars($patriarch['bio'])); ?></div>
                <div class="heresy-section">
                    <div class="heresy-title">البدع والهرطقات التي واجهها:</div>
                    <?php
                    // Loop through heresies
                    if ($heresies && $heresies->num_rows > 0) {
                        while ($h = $heresies->fetch_assoc()) {
                            echo '<div class="heresy-card">';
                            echo '<div class="heresy-name">' . htmlspecialchars($h['name']) . '</div>';
                            echo '<div class="heresy-desc">' . htmlspecialchars($h['description']) . '</div>';
                            echo '<div class="heresy-response">' . htmlspecialchars($h['response']) . '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div>لم يواجه هذا البطريرك أي بدع أو هرطقات مسجلة.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php if (!empty($patriarch['synaxarium'])): ?>
        <div class="synaxarium-section">
            <div class="synaxarium-title">نبذة من السنكسار عن حياة البطريرك:</div>
            <div class="synaxarium-text"><?php echo nl2br(htmlspecialchars($patriarch['synaxarium'])); ?></div>
        </div>
        <?php endif; ?>
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
