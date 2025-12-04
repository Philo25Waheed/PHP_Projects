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

// Prepare search variables
$name = isset($_GET['name']) ? trim($_GET['name']) : '';
$period = isset($_GET['period']) ? trim($_GET['period']) : '';
$heresy = isset($_GET['heresy']) ? intval($_GET['heresy']) : 0;

// Fetch all heresies for the dropdown
$heresies = $conn->query('SELECT id, name FROM heresies');

// Build search query
$where = [];
$params = [];
$types = '';
if ($name !== '') {
    $where[] = 'p.name LIKE ?';
    $params[] = "%$name%";
    $types .= 's';
}
if ($period !== '') {
    $where[] = 'p.period LIKE ?';
    $params[] = "%$period%";
    $types .= 's';
}
if ($heresy > 0) {
    $where[] = 'EXISTS (SELECT 1 FROM patriarchs_heresies ph WHERE ph.patriarch_id = p.id AND ph.heresy_id = ?)';
    $params[] = $heresy;
    $types .= 'i';
}

$sql = 'SELECT p.* FROM patriarchs p';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

// Prepare and execute search
$results = [];
if ($where) {
    $stmt = $conn->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $results[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بحث متقدم عن البطاركة</title>
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="style.css">
    <!-- Link to Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        /* Extra style for search form */
        .search-form { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0001; padding: 24px; max-width: 700px; margin: 40px auto 24px auto; }
        .search-form label { display: block; margin-bottom: 6px; color: #2c3e50; font-weight: bold; }
        .search-form input, .search-form select { width: 100%; padding: 8px; margin-bottom: 16px; border-radius: 5px; border: 1px solid #ccc; font-size: 1em; }
        .search-form button { background: #2980b9; color: #fff; border: none; padding: 10px 24px; border-radius: 5px; font-size: 1em; cursor: pointer; }
        .search-form button:hover { background: #21618c; }
        .search-results { max-width: 1100px; margin: 0 auto 40px auto; }
    </style>
</head>
<body>
    <!-- Theme toggle icon button -->
    <button class="theme-toggle" id="themeToggle" title="تبديل الوضع الليلي/النهاري" aria-label="تبديل الوضع الليلي/النهاري">
        <span id="themeIcon">🌙</span>
    </button>
    <a href="index.php" class="back-link" style="margin:24px; display:inline-block">&larr; عودة إلى القائمة الرئيسية</a>
    <div class="search-form">
        <form method="get" action="search.php">
            <label for="name">اسم البطريرك:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">

            <label for="period">فترة البطريركية:</label>
            <input type="text" id="period" name="period" value="<?php echo htmlspecialchars($period); ?>">

            <label for="heresy">البدعة/الهرطقة:</label>
            <select id="heresy" name="heresy">
                <option value="0">-- اختر --</option>
                <?php
                if ($heresies && $heresies->num_rows > 0) {
                    while ($h = $heresies->fetch_assoc()) {
                        $selected = ($heresy == $h['id']) ? 'selected' : '';
                        echo '<option value="' . $h['id'] . '" ' . $selected . '>' . htmlspecialchars($h['name']) . '</option>';
                    }
                }
                ?>
            </select>

            <button type="submit">بحث</button>
        </form>
    </div>
    <div class="search-results patriarchs-grid">
        <?php
        // Show search results if any
        if ($where && count($results) > 0) {
            $delay = 0;
            foreach ($results as $row) {
                $img = $row['image'] ? htmlspecialchars($row['image']) : 'https://via.placeholder.com/90x90?text=No+Image';
                echo '<div class="patriarch-card animate__animated animate__fadeInUp" style="animation-delay: ' . $delay . 'ms">';
                echo '<a href="patriarch.php?id=' . $row['id'] . '">';
                echo '<img src="' . $img . '" alt="' . htmlspecialchars($row['name']) . '">';
                echo '</a>';
                echo '<div class="patriarch-name">' . htmlspecialchars($row['name']) . '</div>';
                echo '<div class="patriarch-period">' . htmlspecialchars($row['period']) . '</div>';
                echo '</div>';
                $delay += 120;
            }
        } elseif ($where) {
            echo '<p style="text-align:center">لا توجد نتائج مطابقة.</p>';
        }
        ?>
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