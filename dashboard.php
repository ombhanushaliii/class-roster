<?php
// Database connection
$servername = 'localhost';
$dbname = 'class_roster';
$username = 'root';
$password = '';

try {
    // Establish PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch lectures data from database
$lectures = [];
try {
    // Prepare and execute SQL query to fetch timetable data
    $stmt = $conn->prepare("SELECT name, time, faculty FROM lectures ORDER BY time");
    $stmt->execute();
    
    // Fetch all results as an associative array
    $lectures = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching timetable: " . $e->getMessage();
}

// Student name - in a real app, you would get this from the session
$studentName = "Neekunj";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassRoster - Student Dashboard</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }
        
        body {
            min-height: 100vh;
            background-color: #f9fafb;
        }
        
        /* Header styles */
        header {
            background-color: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .header-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .logo {
            font-size: 1.25rem;
            font-weight: bold;
            color: #1f2937;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        
        /* Search bar */
        .search-container {
            position: relative;
        }
        
        .search-box {
            display: flex;
            align-items: center;
            background-color: #f3f4f6;
            border-radius: 8px;
            padding: 8px 16px;
        }
        
        .search-box input {
            background-color: transparent;
            border: none;
            margin-left: 8px;
            outline: none;
        }
        
        /* Notification bell */
        .notification-bell {
            position: relative;
            cursor: pointer;
            background: none;
            border: none;
        }
        
        .notification-count {
            position: absolute;
            top: -4px;
            right: -4px;
            background-color: #ef4444;
            color: white;
            font-size: 0.75rem;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Profile dropdown */
        .profile-container {
            position: relative;
        }
        
        .profile-button {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
        }
        
        .profile-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .profile-menu {
            position: absolute;
            right: 0;
            margin-top: 8px;
            width: 192px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 8px 0;
            display: none;
            z-index: 10;
        }
        
        .profile-menu.show {
            display: block;
        }
        
        .profile-menu a {
            display: block;
            padding: 8px 16px;
            text-decoration: none;
            color: #1f2937;
        }
        
        .profile-menu a:hover {
            background-color: #f3f4f6;
        }
        
        /* Main content */
        main {
            max-width: 1280px;
            margin: 0 auto;
            padding: 32px 16px;
        }
        
        .grid-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 32px;
        }
        
        @media (min-width: 768px) {
            .grid-container {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        /* Card styles */
        .card {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 24px;
        }
        
        .photo-card {
            height: 256px;
            border-radius: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        .college-image {
            width: 110%;
            height: 125%;
            object-fit: cover;
            border-radius: 16px;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .welcome-title {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .welcome-subtitle {
            color: #4b5563;
            margin-top: 8px;
        }
        
        /* Lecture list */
        .lecture-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .lecture-icon {
            height: 24px;
            width: 24px;
            position: relative;
            align-items: center;
        }

        .lecture-item {
            display: flex;
            align-items: center;
            gap: 12px; 
            position: relative;
            padding: 8px;
            border-radius: 8px;
        }

        .icon-sm {
            width: 16px;
            height: 16px;
            color: #6b7280; 
        }
        
        .lecture-item:hover {
            background-color: #f3f4f6;
        }
        
        .lecture-name {
            font-weight: 500;
        }
        
        .lecture-details {
            position: absolute;
            left: 0;
            top: 100%;
            margin-top: 8px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 16px;
            z-index: 10;
            width: 100%;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s, visibility 0.2s;
        }
        
        .lecture-item:hover .lecture-details {
            opacity: 1;
            visibility: visible;
        }
        
        .detail-faculty, .detail-time {
            color: #4b5563;
        }
        
        /* Report card section */
        .report-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 160px;
        }
        
        .report-button {
            background-color: #3b82f6;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .report-button:hover {
            background-color: #2563eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transform: translateY(-4px);
        }
        
        /* Icons */
        .icon {
            display: inline-block;
            width: 24px;
            height: 24px;
            stroke-width: 0;
            stroke: currentColor;
            fill: currentColor;
            vertical-align: middle;
        }
        
        .icon-sm {
            width: 16px;
            height: 16px;
        }
    </style>
</head>
<body>
    <!-- SVG Icons -->
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="icon-search" viewBox="0 0 24 24">
            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
        <symbol id="icon-chevron-down" viewBox="0 0 24 24">
            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
        <symbol id="icon-bell" viewBox="0 0 24 24">
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
        <symbol id="icon-user" viewBox="0 0 24 24">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 7a4 4 0 100 8 4 4 0 000-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
    </svg>

    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="logo">ClassRoster</div>
            
            <div class="header-right">
                <!-- Search Bar -->
                <div class="search-container">
                    <div class="search-box">
                        <svg class="icon icon-sm"><use href="#icon-search"></use></svg>
                        <input type="text" placeholder="Search...">
                        <svg class="icon icon-sm"><use href="#icon-chevron-down"></use></svg>
                    </div>
                </div>
                
                <!-- Notification Bell -->
                <button class="notification-bell">
                    <svg class="icon"><use href="#icon-bell"></use></svg>
                    <span class="notification-count">3</span>
                </button>
                
                <!-- Profile -->
                <div class="profile-container">
                    <button class="profile-button" onclick="toggleProfileMenu()">
                        <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&h=100&fit=crop" alt="Profile" class="profile-img">
                        <svg class="icon icon-sm"><use href="#icon-chevron-down"></use></svg>
                    </button>
                    
                    <div class="profile-menu" id="profileMenu">
                        <a href="profile.php">View Profile</a>
                        <a href="settings.php">Settings</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="grid-container">
            <!-- Profile Photo -->
            <div class="card photo-card">
                <img src="./assets/college.png" alt="College" class="college-image">
            </div>

            <!-- Welcome Message -->
            <div class="card">
                <h2 class="welcome-title">Have a Good day, <?php echo htmlspecialchars($studentName); ?>!</h2>
                <p class="welcome-subtitle">Welcome to your academic dashboard</p>
            </div>

            <!-- Timetable -->
            <div class="card">
                <h2 class="card-title">Timetable</h2>
                <div class="lecture-list">
                    <?php if (count($lectures) > 0): ?>
                        <?php foreach ($lectures as $lecture): ?>
                        <div class="lecture-item">
                            <?php 
                            // Choose icon based on whether the lecture name contains "L"
                            $iconPath = strpos($lecture['name'], 'L') !== false ? 
                                "./assets/comps lab.svg" : "./assets/book lecture.svg";
                            ?>
                            <img src="<?php echo $iconPath; ?>" alt="<?php echo strpos($lecture['name'], 'L') !== false ? 'Lab' : 'Lecture'; ?>" class="lecture-icon">
                            <p class="lecture-name"><?php echo htmlspecialchars($lecture['name']); ?></p>
                            
                            <!-- Hover Details -->
                            <div class="lecture-details">
                                <p class="detail-name"><strong><?php echo htmlspecialchars($lecture['name']); ?></strong></p>
                                <p class="detail-time"><?php echo htmlspecialchars($lecture['time']); ?></p>
                                <p class="detail-faculty"><?php echo htmlspecialchars($lecture['faculty']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No lectures found in the timetable.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Report Card -->
            <div class="card">
                <h2 class="card-title">Report Card</h2>
                <div class="report-container">
                    <button class="report-button" onclick="window.location.href='report.php'">
                        View Report Card
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleProfileMenu() {
            const profileMenu = document.getElementById('profileMenu');
            profileMenu.classList.toggle('show');
        }
    </script>
</body>
</html>