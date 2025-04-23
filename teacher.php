<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create class_schedule table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS class_schedule (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id VARCHAR(50),
    class VARCHAR(20),
    section VARCHAR(10),
    subject VARCHAR(100),
    day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
    start_time TIME,
    duration INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES user_details(SVVNetID)
)");

// Handle schedule form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    $class = $_POST['class'];
    $section = $_POST['section'];
    $subject = $_POST['subject'];
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $duration = $_POST['duration'];
    
    // Check for schedule conflicts
    $conflict_check = $conn->prepare("
        SELECT * FROM class_schedule 
        WHERE day = ? 
        AND teacher_id = ?
        AND (
            (start_time <= ? AND ADDTIME(start_time, SEC_TO_TIME(duration * 60)) > ?) 
            OR (start_time < ADDTIME(?, SEC_TO_TIME(? * 60)) AND start_time >= ?)
        )
    ");
    
    $conflict_check->bind_param("sssssss", $day, $_SESSION['SVVNetID'], $start_time, $start_time, $start_time, $duration, $start_time);
    $conflict_check->execute();
    $conflict_result = $conflict_check->get_result();
    
    if ($conflict_result->num_rows > 0) {
        $error_message = "Schedule conflict detected. Please choose a different time slot.";
    } else {
        // Insert new schedule
        $stmt = $conn->prepare("INSERT INTO class_schedule (teacher_id, class, section, subject, day, start_time, duration) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $_SESSION['SVVNetID'], $class, $section, $subject, $day, $start_time, $duration);
        
        if ($stmt->execute()) {
            $success_message = "Schedule added successfully!";
        } else {
            $error_message = "Error adding schedule: " . $stmt->error;
        }
        $stmt->close();
    }
    $conflict_check->close();
}

// Check if user is a teacher
require_once 'config.php'; // Include the config file

$servername = $DB_HOST;
$username = $DB_USER;
$password = $DB_PASS;
$dbname = $DB_NAME;

$conn = new mysqli($servername, $username, $password, $dbname, $DB_PORT);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$SVVNetID = $_SESSION['SVVNetID'];

// Check if user details exist and is a teacher
$check_stmt = $conn->prepare("SELECT * FROM user_details WHERE SVVNetID = ? AND user_type = 'teacher'");
$check_stmt->bind_param("s", $SVVNetID);
$check_stmt->execute();
$result = $check_stmt->get_result();

// If user doesn't exist or is not a teacher, redirect to details page
if ($result->num_rows === 0) {
    header("Location: details.php");
    exit;
}

$teacher_data = $result->fetch_assoc();
$check_stmt->close();

// Get all classes taught by this teacher
$classes_stmt = $conn->prepare("SELECT DISTINCT class, section FROM user_details WHERE user_type = 'student' ORDER BY class, section");
$classes_stmt->execute();
$classes_result = $classes_stmt->get_result();
$classes = [];

while ($class_row = $classes_result->fetch_assoc()) {
    $class_key = $class_row['class'] . '-' . $class_row['section'];
    $classes[$class_key] = [
        'class' => $class_row['class'],
        'section' => $class_row['section']
    ];
}
$classes_stmt->close();

// Get total students count
$students_count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_details WHERE user_type = 'student'");
$students_count_stmt->execute();
$students_count_result = $students_count_stmt->get_result();
$students_count = $students_count_result->fetch_assoc()['total'];
$students_count_stmt->close();

// Get today's date
$today = date("Y-m-d");

// Check if a specific class is selected for viewing students
$selected_class = isset($_GET['class']) ? $_GET['class'] : '';
$selected_section = isset($_GET['section']) ? $_GET['section'] : '';

// Get students for the selected class and section if any
$students = [];
if (!empty($selected_class) && !empty($selected_section)) {
    $students_stmt = $conn->prepare("SELECT * FROM user_details WHERE user_type = 'student' AND class = ? AND section = ? ORDER BY full_name");
    $students_stmt->bind_param("ss", $selected_class, $selected_section);
    $students_stmt->execute();
    $students_result = $students_stmt->get_result();
    
    while ($student = $students_result->fetch_assoc()) {
        $students[] = $student;
    }
    $students_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #121212;
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 90px;
            background: #1e1e1e;
            padding: 20px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            transition: width 0.3s ease;
        }

        .sidebar:hover {
            width: 240px;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
        }

        .sidebar:hover .sidebar-header {
            justify-content: flex-start;
        }

        .sidebar-header h2 {
            font-size: 22px;
            font-weight: 600;
            margin-left: 10px;
            display: none;
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar:hover .sidebar-header h2 {
            display: block;
        }

        .sidebar-menu {
            flex: 1;
            width: 100%;
        }

        .menu-item {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            border-radius: 12px;
            color: #aaa;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            width: 100%;
            overflow: hidden;
        }

        .sidebar:hover .menu-item {
            justify-content: flex-start;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(106, 90, 249, 0.1);
            color: #6a5af9;
        }

        .menu-item i {
            font-size: 20px;
            min-width: 24px;
        }

        .menu-item span {
            margin-left: 10px;
            display: none;
        }

        .sidebar:hover .menu-item span {
            display: block;
        }

        .user-profile {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
            width: 100%;
        }

        .sidebar:hover .user-profile {
            justify-content: flex-start;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #fff;
            font-weight: 600;
            flex-shrink: 0;
        }

        .user-info {
            margin-left: 10px;
            display: none;
        }

        .sidebar:hover .user-info {
            display: block;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
        }

        .header .date {
            color: #aaa;
            font-size: 14px;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: #1e1e1e;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-header h3 {
            font-size: 16px;
            color: #ddd;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(106, 90, 249, 0.1);
            color: #6a5af9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .card-value {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .card-label {
            font-size: 12px;
            color: #aaa;
        }

        .section-header {
            margin: 30px 0 20px;
            font-size: 20px;
            color: #ddd;
        }

        .class-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }

        .class-card {
            background: #1e1e1e;
            border-radius: 15px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255, 255, 255, 0.05);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #fff;
        }

        .class-card:hover {
            background: rgba(106, 90, 249, 0.1);
            border: 1px solid rgba(106, 90, 249, 0.3);
        }

        .class-card.active {
            background: rgba(106, 90, 249, 0.15);
            border: 1px solid rgba(106, 90, 249, 0.4);
        }

        .class-name {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .class-details {
            color: #aaa;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .attendance-status {
            margin-top: 15px;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 5px;
        }

        .progress {
            height: 100%;
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            border-radius: 3px;
        }

        .student-list h3 {
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .back-button {
            background: rgba(106, 90, 249, 0.1);
            color: #6a5af9;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: rgba(106, 90, 249, 0.2);
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-table th, .student-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .student-table th {
            font-weight: 500;
            color: #aaa;
            font-size: 14px;
        }

        .student-table tr:last-child td {
            border-bottom: none;
        }

        .student-table tr:hover td {
            background: rgba(106, 90, 249, 0.05);
        }

        .logout-btn {
            background: none;
            border: none;
            color: #f44336;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            padding: 0;
        }

        .logout-btn i {
            margin-right: 5px;
        }

        /* Add a subtle animated background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #121212, #1e1e1e);
            z-index: -1;
        }

        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(106, 90, 249, 0.15), transparent 60%);
            z-index: -1;
        }

        .no-students {
            color: #aaa;
            text-align: center;
            padding: 20px;
        }

        /* Schedule Management Styles */
        .schedule-management {
            margin-top: 20px;
        }

        .schedule-form {
            padding: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            color: #aaa;
            font-size: 14px;
        }

        .form-group select {
            background: #2a2a2a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px;
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-group select:focus {
            border-color: #6a5af9;
            box-shadow: 0 0 0 2px rgba(106, 90, 249, 0.1);
        }

        .submit-btn {
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            border: none;
            border-radius: 8px;
            color: #fff;
            padding: 12px 24px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 90, 249, 0.4);
        }

        .timetable-section {
            margin-top: 30px;
            background: #1e1e1e;
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .timetable-container {
            overflow-x: auto;
        }

        .timetable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .timetable th,
        .timetable td {
            padding: 15px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 14px;
        }

        .timetable th {
            background: #2a2a2a;
            font-weight: 500;
            color: #aaa;
        }

        .timetable td {
            background: rgba(42, 42, 42, 0.5);
            color: #ddd;
        }

        .timetable td.active {
            background: rgba(106, 90, 249, 0.1);
            color: #fff;
            font-weight: 500;
        }

        .timetable td.lunch {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-book" style="color: #6a5af9; font-size: 24px;"></i>
                <h2>Class Roster</h2>
            </div>
            <div class="sidebar-menu">
                <a href="teacher.php" class="menu-item active">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Classes</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedule</span>
                </a>
                <a href="attendance.php" class="menu-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Attendance</span>
                </a>
                <a href="report.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
            <a href="profile.php" class="user-profile">
                <div class="avatar">
                    <?php if (!empty($teacher_data['profile_picture'])): ?>
                        <img src="<?php echo $teacher_data['profile_picture']; ?>" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <?php echo strtoupper(substr($teacher_data['full_name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <h4><?php echo $teacher_data['full_name']; ?></h4>
                    <p><?php echo $teacher_data['department']; ?></p>
                </div>
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Teacher Dashboard</h1>
                <div class="date"><?php echo date("l, d F Y"); ?></div>
            </div>

            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <h3>Total Students</h3>
                        <div class="card-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $students_count; ?></div>
                    <div class="card-label">Registered students</div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3>Total Classes</h3>
                        <div class="card-icon">
                            <i class="fas fa-chalkboard"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo count($classes); ?></div>
                    <div class="card-label">Active classes</div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3>Today</h3>
                        <div class="card-icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo date("d"); ?></div>
                    <div class="card-label"><?php echo date("F Y"); ?></div>
                </div>
            </div>

            <?php if (!empty($selected_class) && !empty($selected_section)): ?>
                <!-- Student List for Selected Class -->
                <div class="student-list">
                    <h3>
                        Class <?php echo $selected_class . ' - ' . $selected_section; ?> Students
                        <a href="teacher.php" class="back-button">
                            <i class="fas fa-arrow-left"></i> Back to Classes
                        </a>
                    </h3>
                    <?php if (count($students) > 0): ?>
                        <table class="student-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Roll Number</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Section</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $student): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo $student['roll_number']; ?></td>
                                        <td><?php echo $student['full_name']; ?></td>
                                        <td><?php echo $student['email']; ?></td>
                                        <td><?php echo $student['section']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-students">No students found in this class.</div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Classes Overview -->
                <h2 class="section-header">Your Classes</h2>
                <div class="class-grid">
                    <?php if (count($classes) > 0): ?>
                        <?php foreach ($classes as $class_key => $class_info): ?>
                            <a href="teacher.php?class=<?php echo $class_info['class']; ?>&section=<?php echo $class_info['section']; ?>" class="class-card">
                                <div class="class-name">Class <?php echo $class_info['class']; ?></div>
                                <div class="class-details">
                                    <span><i class="fas fa-users"></i> Section <?php echo $class_info['section']; ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="card" style="grid-column: span 3;">
                            <p style="text-align: center; padding: 20px;">No classes available yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Schedule Management Section -->
                <h2 class="section-header" style="margin-top: 30px;">Schedule Management</h2>
                <div class="schedule-management">
                    <div class="card">
                        <div class="card-header">
                            <h3>Add New Schedule</h3>
                        </div>
                        <form method="POST" class="schedule-form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Class</label>
                                    <select name="class" required>
                                        <option value="">Select Class</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['class']; ?>"><?php echo "Class " . $class['class']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Section</label>
                                    <select name="section" required>
                                        <option value="">Select Section</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['section']; ?>"><?php echo "Section " . $class['section']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Subject</label>
                                    <select name="subject" required>
                                        <option value="">Select Subject</option>
                                        <option value="Mathematics">Mathematics</option>
                                        <option value="Physics">Physics</option>
                                        <option value="Chemistry">Chemistry</option>
                                        <option value="English">English</option>
                                        <option value="Computer Science">Computer Science</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Day</label>
                                    <select name="day" required>
                                        <option value="">Select Day</option>
                                        <option value="Monday">Monday</option>
                                        <option value="Tuesday">Tuesday</option>
                                        <option value="Wednesday">Wednesday</option>
                                        <option value="Thursday">Thursday</option>
                                        <option value="Friday">Friday</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Start Time</label>
                                    <select name="start_time" required>
                                        <option value="">Select Time</option>
                                        <option value="09:00">9:00 AM</option>
                                        <option value="10:00">10:00 AM</option>
                                        <option value="11:00">11:00 AM</option>
                                        <option value="12:00">12:00 PM</option>
                                        <option value="13:00">1:00 PM</option>
                                        <option value="14:00">2:00 PM</option>
                                        <option value="15:00">3:00 PM</option>
                                        <option value="16:00">4:00 PM</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Duration (minutes)</label>
                                    <select name="duration" required>
                                        <option value="60">60 minutes</option>
                                        <option value="90">90 minutes</option>
                                        <option value="120">120 minutes</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" name="add_schedule" class="submit-btn">
                                <i class="fas fa-plus"></i> Add Schedule
                            </button>
                        </form>
                    </div>

                    <!-- Weekly Schedule View -->
                    <div class="timetable-section">
                        <div class="section-header">
                            <h3>Weekly Schedule</h3>
                        </div>
                        <div class="timetable-container">
                            <table class="timetable">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Monday</th>
                                        <th>Tuesday</th>
                                        <th>Wednesday</th>
                                        <th>Thursday</th>
                                        <th>Friday</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $time_slots = array("09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00");
                                    foreach ($time_slots as $time) {
                                        echo "<tr>";
                                        echo "<th>" . date("g:i A", strtotime($time)) . "</th>";
                                        
                                        $days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday");
                                        foreach ($days as $day) {
                                            $schedule_stmt = $conn->prepare("
                                                SELECT class_schedule.*, user_details.full_name 
                                                FROM class_schedule 
                                                LEFT JOIN user_details ON class_schedule.teacher_id = user_details.SVVNetID
                                                WHERE day = ? AND start_time = ? AND teacher_id = ?
                                            ");
                                            $schedule_stmt->bind_param("sss", $day, $time, $SVVNetID);
                                            $schedule_stmt->execute();
                                            $schedule = $schedule_stmt->get_result()->fetch_assoc();
                                            
                                            if ($schedule) {
                                                echo "<td class='active'>";
                                                echo $schedule['subject'] . "<br>";
                                                echo "Class " . $schedule['class'] . " - " . $schedule['section'];
                                                echo "</td>";
                                            } else {
                                                echo "<td></td>";
                                            }
                                        }
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Show notification on page load
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('notification')) {
                // You can implement a notification system here
            }
        });
    </script>
</body>
</html>