<?php
$servername = 'localhost';
$dbname = 'class_roster';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$lectures = [];
try {
    $stmt = $conn->prepare("SELECT name, time, faculty FROM lectures ORDER BY time");
    $stmt->execute();
    
    $lectures = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching timetable: " . $e->getMessage();
}

$studentName = "Neekunj";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassRoster - Student Dashboard</title>
    <style>
        /*Reset and base styles */
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
            background-color: #f9fafb;
            cursor: pointer;
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
        
        /* Updated hover details styles */
        .lecture-details {
            position: absolute;
            left: 0;
            top: 100%;
            margin-top: 8px;
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            z-index: 10;
            width: 300px; /* Fixed width instead of 100% */
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            transform: translateY(10px);
            left: -20px; /* Offset to make it look better */
            border-left: 4px solid #3b82f6;
        }
        
        .lecture-item:hover .lecture-details {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        /* Style the popup content more attractively */
        .detail-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: #1f2937;
        }
        
        .detail-time {
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            color: #4b5563;
        }
        
        .detail-time::before {
            content: "";
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-right: 8px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'%3E%3C/path%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
        }
        
        .detail-faculty {
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            color: #4b5563;
        }
        
        .detail-faculty::before {
            content: "";
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-right: 8px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'%3E%3C/path%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
        }
        
        /* Add a decorative element at the top */
        .lecture-details::before {
            content: "";
            position: absolute;
            top: -8px;
            left: 30px;
            width: 16px;
            height: 16px;
            background-color: white;
            transform: rotate(45deg);
            box-shadow: -3px -3px 5px rgba(0, 0, 0, 0.04);
        }
        
        /* For click functionality with the JavaScript file */
        .lecture-details.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
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
        
        /* Fullscreen toggle styles */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        /* Animation styles */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .card.animating {
            transition: all 0.5s ease;
            animation: pulse 1s ease;
        }

        .card.fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1000;
            width: 100%;
            height: 100%;
            max-width: none;
            margin: 0;
            border-radius: 0;
            overflow-y: auto;
            transition: all 0.5s ease;
        }

        .card.fullscreen .lecture-list {
            max-height: calc(100vh - 120px);
            overflow-y: auto;
        }

        /* New toggle button styles for the arrows */
        .fullscreen-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }

        .fullscreen-button:hover {
            background-color: #f3f4f6;
            transform: scale(1.2);
        }

        .fullscreen-icon {
            transition: all 0.3s ease;
        }

        /* Updated SVG styles for the arrows */
        #icon-expand path, #icon-collapse path {
            stroke: #4b5563;
            stroke-width: 2;
        }
        
        /* Button styles for the popup */
        .popup-actions {
            margin-top: 15px;
            display: flex;
            gap: 8px;
        }
        
        .btn-primary {
            padding: 6px 12px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
        }
        
        .btn-secondary {
            padding: 6px 12px;
            background-color: #f3f4f6;
            color: #4b5563;
            border: none;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
        }
        
        /* Fullscreen lecture item styles */
        .card.fullscreen .lecture-item {
            padding: 16px;
            margin-bottom: 16px;
            border-radius: 12px;
            border-left: 4px solid #3b82f6;
            transition: all 0.3s ease;
        }

        .lecture-item.fullscreen-item {
            display: block;
            background-color: #f9fafb;
            padding: 16px;
            border-radius: 12px;
        }

        .lecture-item.fullscreen-item .lecture-details {
            position: static !important;
            opacity: 1 !important;
            visibility: visible !important;
            margin-top: 12px;
            padding: 12px;
            width: 100%;
            box-shadow: none;
            border-left: none;
            background-color: white;
            border-radius: 8px;
            transform: none;
            transition: none;
        }

        .lecture-item.fullscreen-item .lecture-details::before {
            display: none;
        }

        /* Arrange lecture header and content in fullscreen mode */
        .lecture-item.fullscreen-item {
            display: flex;
            flex-direction: column;
        }

        .lecture-header {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .lecture-item.fullscreen-item .lecture-header {
            margin-bottom: 12px;
        }

        /* Animation for expanding lectures in fullscreen */
        .card.fullscreen .lecture-item {
            animation: slide-in 0.3s ease forwards;
            opacity: 0;
        }

        @keyframes slide-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Add animation delay for each item */
        .card.fullscreen .lecture-item:nth-child(1) { animation-delay: 0.1s; }
        .card.fullscreen .lecture-item:nth-child(2) { animation-delay: 0.2s; }
        .card.fullscreen .lecture-item:nth-child(3) { animation-delay: 0.3s; }
        .card.fullscreen .lecture-item:nth-child(4) { animation-delay: 0.4s; }
        .card.fullscreen .lecture-item:nth-child(5) { animation-delay: 0.5s; }
        .card.fullscreen .lecture-item:nth-child(6) { animation-delay: 0.6s; }
        .card.fullscreen .lecture-item:nth-child(7) { animation-delay: 0.7s; }
        .card.fullscreen .lecture-item:nth-child(8) { animation-delay: 0.8s; }
    </style>
    <script src="fullscreen.js"></script>
    <script src="lecture-popup.js"></script>
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
        
        <!-- Updated expand/collapse icons with 2 arrows -->
        <symbol id="icon-expand" viewBox="0 0 24 24">
            <path d="M15 3h6v6M9 21H3v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </symbol>
        <symbol id="icon-collapse" viewBox="0 0 24 24">
            <path d="M3 9h6V3M15 21h6v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M3 9l7 7M21 15l-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
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
            <div class="card" id="timetable-card">
                <div class="card-header">
                    <h2 class="card-title">Timetable</h2>
                    <button id="fullscreen-toggle" class="fullscreen-button">
                        <svg class="icon icon-sm fullscreen-icon"><use href="#icon-expand"></use></svg>
                    </button>
                </div>
                <div class="lecture-list">
                    <?php if (count($lectures) > 0): ?>
                        <?php foreach ($lectures as $lecture): ?>
                        <div class="lecture-item">
                            <div class="lecture-header">
                                <?php 
                                // Choose icon based on whether the lecture name contains "L"
                                $iconPath = strpos($lecture['name'], 'L') !== false ? 
                                    "./assets/comps lab.svg" : "./assets/book lecture.svg";
                                ?>
                                <img src="<?php echo $iconPath; ?>" alt="<?php echo strpos($lecture['name'], 'L') !== false ? 'Lab' : 'Lecture'; ?>" class="lecture-icon">
                                <p class="lecture-name"><?php echo htmlspecialchars($lecture['name']); ?></p>
                            </div>
                            
                            <!-- Enhanced Hover Details -->
                            <div class="lecture-details">
                                <p class="detail-name"><?php echo htmlspecialchars($lecture['name']); ?></p>
                                <p class="detail-time"><?php echo htmlspecialchars($lecture['time']); ?></p>
                                <p class="detail-faculty"><?php echo htmlspecialchars($lecture['faculty']); ?></p>
                                
                                <!-- Add buttons for additional actions -->
                                <?php if (strpos($lecture['name'], 'L') !== false): ?>
                                <div class="popup-actions">
                                    <button class="btn-primary">View Materials</button>
                                    <button class="btn-secondary">Details</button>
                                </div>
                                <?php endif; ?>
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