<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Database connection
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

// Check if user details exist and is a student
$check_stmt = $conn->prepare("SELECT * FROM user_details WHERE SVVNetID = ? AND user_type = 'student'");
$check_stmt->bind_param("s", $SVVNetID);
$check_stmt->execute();
$result = $check_stmt->get_result();

// If user doesn't exist or is not a student, redirect to details page
if ($result->num_rows === 0) {
    header("Location: details.php");
    exit;
}

$student_data = $result->fetch_assoc();
$check_stmt->close();

// Get student's class and section
$class = $student_data['class'];
$section = $student_data['section'];

// Get student's upcoming schedule/classes
$schedule_stmt = $conn->prepare("SELECT * FROM class_schedule WHERE class = ? AND section = ? AND schedule_date >= CURDATE() ORDER BY schedule_date ASC, start_time ASC LIMIT 5");
$schedule_stmt->bind_param("ss", $class, $section);
$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result();
$schedule_items = [];

while ($schedule = $schedule_result->fetch_assoc()) {
    $schedule_items[] = $schedule;
}
$schedule_stmt->close();

// Update the attendance query (around line 50-60)
$attendance_stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_classes,
        COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
        COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count
    FROM attendance 
    WHERE SVVNetID = ? 
    AND class = ? 
    AND section = ?
    AND attendance_date <= CURRENT_DATE");

$attendance_stmt->bind_param("sss", $SVVNetID, $class, $section);
$attendance_stmt->execute();
$attendance_result = $attendance_stmt->get_result();
$attendance_data = $attendance_result->fetch_assoc();

// Set default values if no attendance records found
if (!$attendance_data['total_classes']) {
    $attendance_data = [
        'total_classes' => 0,
        'present_count' => 0,
        'absent_count' => 0
    ];
}

// Calculate attendance percentage
$attendance_percentage = $attendance_data['total_classes'] > 0 
    ? round(($attendance_data['present_count'] / $attendance_data['total_classes']) * 100) 
    : 0;

$attendance_stmt->close();

// Get classmates in the same class and section
$classmates_stmt = $conn->prepare("SELECT * FROM user_details WHERE user_type = 'student' AND class = ? AND section = ? AND SVVNetID != ? ORDER BY full_name ASC LIMIT 5");

if ($classmates_stmt === false) {
    // Handle error - table might not exist
    $classmates = [];
} else {
    $classmates_stmt->bind_param("sss", $class, $section, $SVVNetID);
    $classmates_stmt->execute();
    $classmates_result = $classmates_stmt->get_result();
    $classmates = [];

    while ($classmate = $classmates_result->fetch_assoc()) {
        $classmates[] = $classmate;
    }
    $classmates_stmt->close();
}

// Get teachers for the student's class - Add similar error handling
$teachers_stmt = $conn->prepare("SELECT t.* FROM teacher_classes tc JOIN user_details t ON tc.teacher_id = t.SVVNetID WHERE tc.class = ? AND tc.section = ? AND t.user_type = 'teacher' ORDER BY t.full_name ASC");

if ($teachers_stmt === false) {
    // Handle error - table might not exist
    $teachers = [];
} else {
    $teachers_stmt->bind_param("ss", $class, $section);
    $teachers_stmt->execute();
    $teachers_result = $teachers_stmt->get_result();
    $teachers = [];

    while ($teacher = $teachers_result->fetch_assoc()) {
        $teachers[] = $teacher;
    }
    $teachers_stmt->close();
}

// Get student's assignments - Add error handling
$assignments_stmt = $conn->prepare("SELECT a.*, 
    CASE WHEN sa.submission_date IS NOT NULL THEN 'completed' ELSE 'pending' END as status
    FROM assignments a
    LEFT JOIN student_assignments sa ON a.assignment_id = sa.assignment_id AND sa.student_id = ?
    WHERE a.class = ? AND a.section = ? AND a.due_date >= CURDATE()
    ORDER BY a.due_date ASC
    LIMIT 3");

if ($assignments_stmt === false) {
    // Handle error - table might not exist
    $assignments = [];
} else {
    $assignments_stmt->bind_param("sss", $SVVNetID, $class, $section);
    $assignments_stmt->execute();
    $assignments_result = $assignments_stmt->get_result();
    $assignments = [];

    while ($assignment = $assignments_result->fetch_assoc()) {
        $assignments[] = $assignment;
    }
    $assignments_stmt->close();
}

// Get student's grades - Add error handling
$grades_stmt = $conn->prepare("SELECT subject_name, AVG(marks) as average_marks FROM student_grades WHERE student_id = ? GROUP BY subject_name ORDER BY subject_name");

if ($grades_stmt === false) {
    // Handle error - table might not exist
    $grades = [];
} else {
    $grades_stmt->bind_param("s", $SVVNetID);
    $grades_stmt->execute();
    $grades_result = $grades_stmt->get_result();
    $grades = [];

    while ($grade = $grades_result->fetch_assoc()) {
        $grades[] = $grade;
    }
    $grades_stmt->close();
}

// Get any announcements or notifications - Add error handling
$announcements_stmt = $conn->prepare("SELECT * FROM announcements WHERE (class = ? AND section = ?) OR (class = 'all' AND section = 'all') ORDER BY created_at DESC LIMIT 3");

if ($announcements_stmt === false) {
    // Handle error - table might not exist
    $announcements = [];
} else {
    $announcements_stmt->bind_param("ss", $class, $section);
    $announcements_stmt->execute();
    $announcements_result = $announcements_stmt->get_result();
    $announcements = [];

    while ($announcement = $announcements_result->fetch_assoc()) {
        $announcements[] = $announcement;
    }
    $announcements_stmt->close();
}

// Get today's date
$today = date("Y-m-d");

// Note: Don't close the connection here as it's needed for the timetable
// $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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
            white-space: nowrap;
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

        .user-info h4 {
            font-size: 14px;
            font-weight: 500;
        }

        .user-info p {
            font-size: 12px;
            color: #aaa;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .search-bar {
            position: relative;
            margin-left: 20px;
        }

        .search-bar input {
            background: #1e1e1e;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 10px 20px 10px 40px;
            width: 250px;
            color: #fff;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-bar input:focus {
            border-color: rgba(106, 90, 249, 0.5);
            width: 300px;
        }

        .search-bar i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        .header-right {
            display: flex;
            align-items: center;
        }

        .notification {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #1e1e1e;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            cursor: pointer;
            position: relative;
        }

        .notification i {
            color: #aaa;
            font-size: 18px;
        }

        .notification .badge {
            position: absolute;
            top: 0;
            right: 0;
            width: 16px;
            height: 16px;
            background: #6a5af9;
            border-radius: 50%;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }

        /* Welcome Section */
        .welcome-section {
            background: #1e1e1e;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
            background-image: linear-gradient(to right, rgba(106, 90, 249, 0.1), transparent);
        }

        .welcome-text {
            flex: 1;
        }

        .welcome-text h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .welcome-text p {
            color: #aaa;
            font-size: 16px;
            max-width: 500px;
        }

        .welcome-image {
            width: 180px;
            height: 180px;
            background-image: url('/api/placeholder/180/180');
            background-size: cover;
            border-radius: 20px;
            margin-left: 20px;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        /* Schedule Section */
        .schedule-section {
            background: #1e1e1e;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h2 {
            font-size: 18px;
            font-weight: 500;
        }

        .date-selector {
            display: flex;
            align-items: center;
        }

        .date-selector button {
            background: #2a2a2a;
            border: none;
            color: #aaa;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .date-selector button:hover {
            background: rgba(106, 90, 249, 0.2);
            color: #6a5af9;
        }

        .date-selector span {
            margin: 0 15px;
            font-size: 14px;
        }

        .calendar-view {
            display: flex;
            margin-bottom: 20px;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .calendar-view::-webkit-scrollbar {
            height: 5px;
        }

        .calendar-view::-webkit-scrollbar-track {
            background: #2a2a2a;
            border-radius: 5px;
        }

        .calendar-view::-webkit-scrollbar-thumb {
            background: #6a5af9;
            border-radius: 5px;
        }

        .day-item {
            min-width: 80px;
            text-align: center;
            padding: 15px 10px;
            border-radius: 12px;
            margin-right: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .day-item.active {
            background: rgba(106, 90, 249, 0.2);
        }

        .day-item .day-name {
            font-size: 14px;
            color: #aaa;
            margin-bottom: 8px;
        }

        .day-item .day-number {
            font-size: 24px;
            font-weight: 600;
        }

        .day-item.active .day-name, 
        .day-item.active .day-number {
            color: #6a5af9;
        }

        .schedule-list {
            display: flex;
            flex-direction: column;
        }

        .schedule-item {
            display: flex;
            padding: 15px;
            border-radius: 12px;
            background: #2a2a2a;
            margin-bottom: 15px;
            align-items: center;
            border-left: 4px solid #6a5af9;
        }

        .schedule-time {
            width: 90px;
            text-align: center;
            padding-right: 15px;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .schedule-time .time {
            font-size: 16px;
            font-weight: 500;
        }

        .schedule-time .period {
            font-size: 12px;
            color: #aaa;
            text-transform: uppercase;
        }

        .schedule-info {
            flex: 1;
            padding: 0 15px;
        }

        .schedule-info .class-name {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .schedule-info .teacher-name {
            font-size: 14px;
            color: #aaa;
        }

        .schedule-action {
            display: flex;
            align-items: center;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(106, 90, 249, 0.1);
            color: #6a5af9;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: rgba(106, 90, 249, 0.2);
        }

        /* Announcements Section */
        .announcements-section {
            background: #1e1e1e;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .announcement-item {
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .announcement-item:last-child {
            border-bottom: none;
        }

        .announcement-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .announcement-header .avatar {
            width: 35px;
            height: 35px;
            font-size: 14px;
        }

        .announcement-meta {
            margin-left: 10px;
        }

        .announcement-meta .name {
            font-size: 14px;
            font-weight: 500;
        }

        .announcement-meta .time {
            font-size: 12px;
            color: #aaa;
        }

        .announcement-content {
            font-size: 14px;
            line-height: 1.5;
            color: #ddd;
            margin-left: 45px;
        }

        /* Second Row Grid */
        .second-row-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        /* Assignments Section */
        .assignments-section {
            background: #1e1e1e;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .assignment-list {
            display: flex;
            flex-direction: column;
        }

        .assignment-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .assignment-item:last-child {
            border-bottom: none;
        }

        .assignment-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: rgba(106, 90, 249, 0.1);
            color: #6a5af9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 15px;
        }

        .assignment-info {
            flex: 1;
        }

        .assignment-info .title {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .assignment-info .meta {
            font-size: 12px;
            color: #aaa;
            display: flex;
            align-items: center;
        }

        .assignment-info .meta span {
            margin-right: 15px;
            display: flex;
            align-items: center;
        }

        .assignment-info .meta i {
            margin-right: 5px;
            font-size: 12px;
        }

        .assignment-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #FFC107;
        }

        .status-completed {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }

        /* Classmates Section */
        .classmates-section {
            background: #1e1e1e;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .classmate-list {
            display: flex;
            flex-direction: column;
        }

        .classmate-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .classmate-item:last-child {
            border-bottom: none;
        }

        .classmate-avatar {
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
            margin-right: 15px;
        }

        .classmate-info {
            flex: 1;
        }

        .classmate-info .name {
            font-size: 14px;
            font-weight: 500;
        }

        .classmate-info .roll {
            font-size: 12px;
            color: #aaa;
        }

        .classmate-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-left: 10px;
        }

        .status-online {
            background: #4CAF50;
        }

        .status-offline {
            background: #aaa;
        }

        /* Quick Actions */
        .quick-actions {
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .action-card {
            width: calc(20% - 15px);
            background: #1e1e1e;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .action-card:hover {
            transform: translateY(-5px);
            border-color: rgba(106, 90, 249, 0.3);
        }

        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            background: rgba(106, 90, 249, 0.1);
            color: #6a5af9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 15px;
        }

        .action-title {
            font-size: 14px;
            font-weight: 500;
        }

        /* Attendance Section */
        .attendance-section {
            background: #1e1e1e;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 25px;
        }

        .attendance-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .attendance-stat {
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #aaa;
        }

        .progress-container {
            width: 100%;
            height: 10px;
            background: #2a2a2a;
            border-radius: 10px;
            margin-bottom: 10px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            border-radius: 10px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #aaa;
        }

        /* Report Card Section */
        .report-card-section {
            background: #1e1e1e;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 25px;
        }

        .report-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .download-btn {
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            border: none;
            color: #fff;
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .download-btn i {
            margin-right: 8px;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 90, 249, 0.4);
        }

        .grades-list {
            display: flex;
            flex-direction: column;
        }

        .grade-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .grade-item:last-child {
            border-bottom: none;
        }

        .subject-name {
            font-size: 14px;
            font-weight: 500;
        }

        .grade-value {
            font-size: 14px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 15px;
        }

        .grade-excellent {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }

        .grade-good {
            background: rgba(33, 150, 243, 0.2);
            color: #2196F3;
        }

        .grade-average {
            background: rgba(255, 193, 7, 0.2);
            color: #FFC107;
        }

        .grade-poor {
            background: rgba(244, 67, 54, 0.2);
            color: #F44336;
        }

        /* Timetable Section */
        .timetable-section {
            background: #1e1e1e;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 25px;
        }

        .timetable-container {
            overflow-x: auto;
        }

        .timetable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .timetable th, .timetable td {
            padding: 12px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .timetable th {
            background: #2a2a2a;
            font-weight: 500;
            color: #aaa;
            font-size: 14px;
        }

        .timetable td {
            background: rgba(42, 42, 42, 0.5);
            font-size: 13px;
            color: #ddd;
        }

        .timetable td.active {
            background: rgba(106, 90, 249, 0.1);
            color: #6a5af9;
        }

        .timetable td.lunch {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }

        /* Add these styles to your existing CSS */
        .schedule-cell {
            padding: 5px;
        }

        .schedule-cell .subject {
            font-weight: 500;
            margin-bottom: 3px;
        }

        .schedule-cell .teacher {
            font-size: 12px;
            color: #aaa;
        }

        .timetable td.active {
            background: rgba(106, 90, 249, 0.1);
        }

        .timetable td.active:hover {
            background: rgba(106, 90, 249, 0.15);
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
                <a href="student.php" class="menu-item active">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <a href="grades.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Grades</span>
                </a>
                <a href="profile.php" class="menu-item">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
            <a href="profile.php" class="user-profile">
                <div class="avatar">
                    <?php if (!empty($student_data['profile_picture'])): ?>
                        <img src="<?php echo $student_data['profile_picture']; ?>" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <?php echo strtoupper(substr($student_data['full_name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <h4><?php echo $student_data['full_name']; ?></h4>
                    <p>Roll No: <?php echo $student_data['roll_number']; ?></p>
                </div>
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="header-left">
                    <h1>Welcome, <?php echo explode(' ', $student_data['full_name'])[0]; ?>!</h1>
                </div>
                <div class="header-right">
                    <div class="notification">
                        <i class="fas fa-bell"></i>
                        <?php if (count($announcements) > 0): ?>
                            <div class="badge"><?php echo count($announcements); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($student_data['full_name'], 0, 1)); ?>
                    </div>
                </div>
            </div>

            <!-- Attendance Section -->
            <div class="attendance-section">
                <div class="section-header">
                    <h2>Attendance Overview</h2>
                </div>
                <div class="attendance-stats">
                    <div class="attendance-stat">
                        <div class="stat-value"><?php echo $attendance_data['total_classes']; ?></div>
                        <div class="stat-label">Total Classes</div>
                    </div>
                    <div class="attendance-stat">
                        <div class="stat-value"><?php echo $attendance_data['present_count']; ?></div>
                        <div class="stat-label">Present</div>
                    </div>
                    <div class="attendance-stat">
                        <div class="stat-value"><?php echo $attendance_data['absent_count']; ?></div>
                        <div class="stat-label">Absent</div>
                    </div>
                    <div class="attendance-stat">
                        <div class="stat-value"><?php echo $attendance_percentage; ?>%</div>
                        <div class="stat-label">Attendance Rate</div>
                    </div>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: <?php echo $attendance_percentage; ?>%"></div>
                </div>
                <div class="progress-label">
                    <span>0%</span>
                    <span>Attendance Rate</span>
                    <span>100%</span>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Schedule Section -->
                <div class="schedule-section">
                    <div class="section-header">
                        <h2>Today's Schedule</h2>
                        <div class="date-selector">
                            <button><i class="fas fa-chevron-left"></i></button>
                            <span><?php echo date('d M Y'); ?></span>
                            <button><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                    <div class="schedule-list">
                        <?php if (count($schedule_items) > 0): ?>
                            <?php foreach ($schedule_items as $schedule): ?>
                                <div class="schedule-item">
                                    <div class="schedule-time">
                                        <div class="time"><?php echo date('h:i', strtotime($schedule['start_time'])); ?></div>
                                        <div class="period"><?php echo date('A', strtotime($schedule['start_time'])); ?></div>
                                    </div>
                                    <div class="schedule-info">
                                        <div class="class-name"><?php echo $schedule['subject']; ?></div>
                                        <div class="teacher-name"><?php echo $schedule['teacher_name']; ?></div>
                                    </div>
                                    <div class="schedule-action">
                                        <div class="action-btn">
                                            <i class="fas fa-chevron-right"></i>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="text-align: center; color: #aaa;">No classes scheduled for today</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Announcements Section -->
                <div class="announcements-section">
                    <div class="section-header">
                        <h2>Announcements</h2>
                    </div>
                    <?php if (count($announcements) > 0): ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="announcement-item">
                                <div class="announcement-header">
                                    <div class="avatar">
                                        <?php echo strtoupper(substr($announcement['posted_by'], 0, 1)); ?>
                                    </div>
                                    <div class="announcement-meta">
                                        <div class="name"><?php echo $announcement['posted_by']; ?></div>
                                        <div class="time"><?php echo date('d M Y', strtotime($announcement['created_at'])); ?></div>
                                    </div>
                                </div>
                                <div class="announcement-content">
                                    <?php echo $announcement['content']; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #aaa;">No announcements</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Report Card Section -->
            <div class="report-card-section">
                <div class="report-card-header">
                    <h2>Academic Performance</h2>
                    <button class="download-btn">
                        <i class="fas fa-download"></i>
                        Download Report Card
                    </button>
                </div>
                <div class="grades-list">
                    <?php if (count($grades) > 0): ?>
                        <?php foreach ($grades as $grade): ?>
                            <div class="grade-item">
                                <div class="subject-name"><?php echo $grade['subject_name']; ?></div>
                                <?php
                                    $grade_class = '';
                                    $avg_marks = round($grade['average_marks']);
                                    if ($avg_marks >= 90) $grade_class = 'grade-excellent';
                                    elseif ($avg_marks >= 80) $grade_class = 'grade-good';
                                    elseif ($avg_marks >= 70) $grade_class = 'grade-average';
                                    else $grade_class = 'grade-poor';
                                ?>
                                <div class="grade-value <?php echo $grade_class; ?>">
                                    <?php echo $avg_marks; ?>%
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #aaa;">No grades available</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Timetable Section -->
            <div class="timetable-section">
                <div class="section-header">
                    <h2>Weekly Timetable</h2>
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
                                        SELECT cs.*, ud.full_name as teacher_name 
                                        FROM class_schedule cs
                                        LEFT JOIN user_details ud ON cs.teacher_id = ud.SVVNetID
                                        WHERE cs.day = ? 
                                        AND cs.start_time = ? 
                                        AND cs.class = ?
                                        AND cs.section = ?
                                    ");
                                    $schedule_stmt->bind_param("ssss", $day, $time, $student_data['class'], $student_data['section']);
                                    $schedule_stmt->execute();
                                    $schedule = $schedule_stmt->get_result()->fetch_assoc();
                                    $schedule_stmt->close();
                                    
                                    if ($schedule) {
                                        echo "<td class='active'>";
                                        echo "<div class='schedule-cell'>";
                                        echo "<div class='subject'>" . $schedule['subject'] . "</div>";
                                        echo "<div class='teacher'>" . $schedule['teacher_name'] . "</div>";
                                        echo "</div>";
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
            <?php $conn->close(); // Move connection close to after all database operations ?>
        </div>
    </div>

    <script>
        // Add this script at the end of body, before closing body tag
        document.addEventListener('DOMContentLoaded', function() {
            // Add the window.jsPDF definition
            window.jsPDF = window.jspdf.jsPDF;

            const downloadBtn = document.querySelector('.download-btn');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function() {
                    const reportSection = document.querySelector('.report-card-section');
                    
                    // Create PDF document
                    const doc = new jsPDF();
                    
                    // Add title
                    doc.setFontSize(20);
                    doc.setTextColor(0, 0, 0);
                    doc.text('Student Report Card', 105, 20, { align: 'center' });
                    
                    // Add student details
                    doc.setFontSize(12);
                    doc.text('Student Details:', 20, 40);
                    doc.text('Name: <?php echo $student_data['full_name']; ?>', 20, 50);
                    doc.text('Roll Number: <?php echo $student_data['roll_number']; ?>', 20, 60);
                    doc.text('Class: <?php echo $student_data['class']; ?>', 20, 70);
                    doc.text('Section: <?php echo $student_data['section']; ?>', 20, 80);
                    
                    // Add grades table header
                    doc.setFillColor(230, 230, 230);
                    doc.rect(20, 100, 170, 10, 'F');
                    doc.text('Subject', 30, 107);
                    doc.text('Marks', 150, 107);
                    
                    // Add grades
                    let yPos = 120;
                    <?php foreach ($grades as $index => $grade): ?>
                    doc.text('<?php echo $grade['subject_name']; ?>', 30, yPos);
                    doc.text('<?php echo round($grade['average_marks'], 2); ?>%', 150, yPos);
                    yPos += 10;
                    <?php endforeach; ?>
                    
                    // Add attendance
                    doc.text('Attendance Overview:', 20, yPos + 20);
                    doc.text('Total Classes: <?php echo $attendance_data['total_classes']; ?>', 30, yPos + 30);
                    doc.text('Classes Attended: <?php echo $attendance_data['present_count']; ?>', 30, yPos + 40);
                    doc.text('Attendance Rate: <?php echo $attendance_percentage; ?>%', 30, yPos + 50);
                    
                    // Add footer
                    doc.setFontSize(10);
                    doc.text('Generated on: ' + new Date().toLocaleDateString(), 20, 280);
                    doc.text('K.J. Somaiya Institute of Technology', 105, 280, { align: 'center' });
                    
                    // Save PDF
                    doc.save('Student_Report_Card.pdf');
                });
            }
        });
    </script>
</body>
</html>