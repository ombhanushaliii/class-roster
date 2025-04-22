<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
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

// Handle form submission for marking attendance
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $attendance_date = $_POST['attendance_date'];
    $class = $_POST['class'];
    $section = $_POST['section'];
    $present_students = isset($_POST['present']) ? $_POST['present'] : [];
    
    // Get all students in this class
    $students_stmt = $conn->prepare("SELECT SVVNetID FROM user_details WHERE user_type = 'student' AND class = ? AND section = ?");
    $students_stmt->bind_param("ss", $class, $section);
    $students_stmt->execute();
    $students_result = $students_stmt->get_result();
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // First, delete any existing attendance records for this class, section, and date
        $delete_stmt = $conn->prepare("DELETE FROM attendance WHERE class = ? AND section = ? AND attendance_date = ?");
        $delete_stmt->bind_param("sss", $class, $section, $attendance_date);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        // Insert new attendance records
        $insert_stmt = $conn->prepare("INSERT INTO attendance (SVVNetID, class, section, attendance_date, status, marked_by) VALUES (?, ?, ?, ?, ?, ?)");
        
        while ($student = $students_result->fetch_assoc()) {
            $student_id = $student['SVVNetID'];
            $status = in_array($student_id, $present_students) ? 'present' : 'absent';
            
            $insert_stmt->bind_param("ssssss", $student_id, $class, $section, $attendance_date, $status, $SVVNetID);
            $insert_stmt->execute();
        }
        
        $insert_stmt->close();
        $students_stmt->close();
        
        // Commit transaction
        $conn->commit();
        $success_message = "Attendance marked successfully for Class $class - Section $section on " . date('d F Y', strtotime($attendance_date));
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = "Error marking attendance: " . $e->getMessage();
    }
}

// Get selected class and section for viewing/marking attendance
$selected_class = isset($_GET['class']) ? $_GET['class'] : '';
$selected_section = isset($_GET['section']) ? $_GET['section'] : '';
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Get students for the selected class and section
$students = [];
if (!empty($selected_class) && !empty($selected_section)) {
    $students_stmt = $conn->prepare("SELECT * FROM user_details WHERE user_type = 'student' AND class = ? AND section = ? ORDER BY roll_number");
    $students_stmt->bind_param("ss", $selected_class, $selected_section);
    $students_stmt->execute();
    $students_result = $students_stmt->get_result();
    
    while ($student = $students_result->fetch_assoc()) {
        $students[] = $student;
    }
    $students_stmt->close();
    
    // Get attendance for the selected date, class and section
    if (!empty($students)) {
        $attendance_stmt = $conn->prepare("SELECT * FROM attendance WHERE class = ? AND section = ? AND attendance_date = ?");
        $attendance_stmt->bind_param("sss", $selected_class, $selected_section, $selected_date);
        $attendance_stmt->execute();
        $attendance_result = $attendance_stmt->get_result();
        
        $attendance = [];
        while ($record = $attendance_result->fetch_assoc()) {
            $attendance[$record['SVVNetID']] = $record['status'];
        }
        $attendance_stmt->close();
    }
}

// Get attendance statistics for all classes
$class_attendance = [];
foreach ($classes as $class_key => $class_info) {
    $class = $class_info['class'];
    $section = $class_info['section'];
    
    // Get total students in class
    $total_stmt = $conn->prepare("SELECT COUNT(*) as total FROM user_details WHERE user_type = 'student' AND class = ? AND section = ?");
    $total_stmt->bind_param("ss", $class, $section);
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total_students = $total_result->fetch_assoc()['total'];
    $total_stmt->close();
    
    // Get present students for today
    $today = date('Y-m-d');
    $present_stmt = $conn->prepare("SELECT COUNT(*) as present FROM attendance WHERE class = ? AND section = ? AND attendance_date = ? AND status = 'present'");
    $present_stmt->bind_param("sss", $class, $section, $today);
    $present_stmt->execute();
    $present_result = $present_stmt->get_result();
    $present_students = $present_result->fetch_assoc()['present'];
    $present_stmt->close();
    
    $class_attendance[$class_key] = [
        'total' => $total_students,
        'present' => $present_students ?: 0,
        'percentage' => $total_students > 0 ? round(($present_students ?: 0) * 100 / $total_students) : 0
    ];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
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
            width: 240px;
            background: #1e1e1e;
            padding: 20px;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            font-size: 22px;
            font-weight: 600;
            margin-left: 10px;
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-menu {
            flex: 1;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 8px;
            color: #aaa;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(106, 90, 249, 0.1);
            color: #6a5af9;
        }

        .menu-item i {
            margin-right: 10px;
            font-size: 18px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
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
            margin-right: 10px;
        }

        .user-info {
            flex: 1;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .attendance-container {
            background: #1e1e1e;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .attendance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .attendance-header h3 {
            font-size: 18px;
            font-weight: 500;
        }

        .attendance-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .date-picker {
            background: #2a2a2a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 14px;
        }

        .submit-btn {
            background: #6a5af9;
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: #5849e0;
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
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .back-button:hover {
            background: rgba(106, 90, 249, 0.2);
        }

        .back-button i {
            margin-right: 5px;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
        }

        .attendance-table th, .attendance-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .attendance-table th {
            font-weight: 500;
            color: #aaa;
            font-size: 14px;
        }

        .attendance-table tr:last-child td {
            border-bottom: none;
        }

        .attendance-table tr:hover td {
            background: rgba(106, 90, 249, 0.05);
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-checkbox {
            width: 20px;
            height: 20px;
            background: #2a2a2a;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }

        .custom-checkbox::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            width: 10px;
            height: 10px;
            background: #6a5af9;
            border-radius: 2px;
            transition: all 0.2s ease;
        }

        input[type="checkbox"]:checked + .custom-checkbox {
            border-color: #6a5af9;
        }

        input[type="checkbox"]:checked + .custom-checkbox::after {
            transform: translate(-50%, -50%) scale(1);
        }

        input[type="checkbox"] {
            display: none;
        }

        .attendance-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .status-present {
            background: rgba(46, 213, 115, 0.15);
            color: #2ed573;
        }

        .status-absent {
            background: rgba(255, 71, 87, 0.15);
            color: #ff4757;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: rgba(46, 213, 115, 0.1);
            border-color: rgba(46, 213, 115, 0.2);
            color: #2ed573;
        }

        .alert-error {
            background: rgba(255, 71, 87, 0.1);
            border-color: rgba(255, 71, 87, 0.2);
            color: #ff4757;
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

        .select-all-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            cursor: pointer;
        }

        .select-all-wrapper label {
            margin-left: 8px;
            color: #aaa;
            font-size: 14px;
            cursor: pointer;
        }

        .no-students {
            color: #aaa;
            text-align: center;
            padding: 20px;
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
                <a href="teacher.php" class="menu-item">
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
                <a href="attendance.php" class="menu-item active">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Attendance</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
            <div class="user-profile">
                <div class="avatar">
                    <?php echo strtoupper(substr($teacher_data['full_name'], 0, 1)); ?>
                </div>
                <div class="user-info">
                    <h4><?php echo $teacher_data['full_name']; ?></h4>
                    <p><?php echo $teacher_data['department']; ?></p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Attendance Management</h1>
                <div class="date"><?php echo date("l, d F Y"); ?></div>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <h3>Total Classes</h3>
                        <div class="card-icon">
                            <i class="fas fa-chalkboard"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo count($classes); ?></div>
                    <div class="card-label">Classes with attendance</div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3>Today's Attendance</h3>
                        <div class="card-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                    <?php 
                    $total_present = 0;
                    $total_students_overall = 0;
                    foreach ($class_attendance as $data) {
                        $total_present += $data['present'];
                        $total_students_overall += $data['total'];
                    }
                    $overall_percentage = $total_students_overall > 0 ? round(($total_present * 100) / $total_students_overall) : 0;
                    ?>
                    <div class="card-value"><?php echo $overall_percentage; ?>%</div>
                    <div class="card-label">Overall attendance</div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3>Date</h3>
                        <div class="card-icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo date("d"); ?></div>
                    <div class="card-label"><?php echo date("F Y"); ?></div>
                </div>
            </div>

            <?php if (!empty($selected_class) && !empty($selected_section)): ?>
                <!-- Attendance Form for Selected Class -->
                <div class="attendance-container">
                    <div class="attendance-header">
                        <h3>
                            Attendance for Class <?php echo $selected_class . ' - ' . $selected_section; ?>
                        </h3>
                        <div class="attendance-controls">
                            <a href="attendance.php" class="back-button">
                                <i class="fas fa-arrow-left"></i> Back to Classes
                            </a>
                        </div>
                    </div>

                    <?php if (count($students) > 0): ?>
                        <form method="POST" action="attendance.php">
                            <input type="hidden" name="class" value="<?php echo $selected_class; ?>">
                            <input type="hidden" name="section" value="<?php echo $selected_section; ?>">
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <div style="display: flex; align-items: center;">
                                    <label for="attendance_date" style="margin-right: 10px; color: #aaa;">Select Date:</label>
                                    <input type="date" id="attendance_date" name="attendance_date" class="date-picker" value="<?php echo $selected_date; ?>" max="<?php echo date('Y-m-d'); ?>">
                                </div>
                                
                                <div class="select-all-wrapper">
                                    <input type="checkbox" id="select-all" class="hidden-checkbox">
                                    <label class="custom-checkbox" for="select-all"></label>
                                    <label for="select-all">Mark All Present</label>
                                </div>
                            </div>

                            <table class="attendance-table">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 15%;">Roll Number</th>
                                        <th style="width: 35%;">Student Name</th>
                                        <th style="width: 25%;">Email</th>
                                        <th style="width: 20%; text-align: center;">Present</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $index => $student): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo $student['roll_number']; ?></td>
                                            <td><?php echo $student['full_name']; ?></td>
                                            <td><?php echo $student['email']; ?></td>
                                            <td style="text-align: center;">
                                                <div class="checkbox-wrapper">
                                                    <input type="checkbox" 
                                                        id="present_<?php echo $student['SVVNetID']; ?>" 
                                                        name="present[]" 
                                                        value="<?php echo $student['SVVNetID']; ?>"
                                                        class="student-checkbox"
                                                        <?php echo (isset($attendance[$student['SVVNetID']]) && $attendance[$student['SVVNetID']] === 'present') ? 'checked' : ''; ?>>
                                                    <label class="custom-checkbox" for="present_<?php echo $student['SVVNetID']; ?>"></label>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                                <button type="submit" name="mark_attendance" class="submit-btn">
                                    <i class="fas fa-save"></i> Save Attendance
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="no-students">No students found in this class.</div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Classes Attendance Overview -->
                <h2 class="section-header">
                    Class Attendance
                    <span style="font-size: 14px; color: #aaa;">Today, <?php echo date("d F Y"); ?></span>
                </h2>
                <div class="class-grid">
                    <?php if (count($classes) > 0): ?>
                        <?php foreach ($classes as $class_key => $class_info): ?>
                            <a href="attendance.php?class=<?php echo $class_info['class']; ?>&section=<?php echo $class_info['section']; ?>" class="class-card">
                                <div class="class-name">Class <?php echo $class_info['class']; ?> - <?php echo $class_info['section']; ?></div>
                                <div class="attendance-status">
                                    <span><?php echo $class_attendance[$class_key]['present']; ?> / <?php echo $class_attendance[$class_key]['total']; ?> Present</span>
                                    <div class="progress-bar">
                                        <div class="progress" style="width: <?php echo $class_attendance[$class_key]['percentage']; ?>%"></div>
                                    </div>
                                </div>
                                <div class="class-details">
                                    <span>Attendance Rate</span>
                                    <span><?php echo $class_attendance[$class_key]['percentage']; ?>%</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="grid-column: 1/-1; text-align: center; color: #aaa; padding: 20px;">
                            No classes found.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Select all functionality
        const selectAllCheckbox = document.getElementById('select-all');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const studentCheckboxes = document.getElementsByClassName('student-checkbox');
                for (let checkbox of studentCheckboxes) {
                    checkbox.checked = this.checked;
                }
            });
        }

        // Update date via URL
        const datePicker = document.getElementById('attendance_date');
        if (datePicker) {
            datePicker.addEventListener('change', function() {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('date', this.value);
                window.location.href = currentUrl.toString();
            });
        }
    </script>
</body>
</html>