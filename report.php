<?php
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$SVVNetID = $_SESSION['SVVNetID'];
$success_message = '';
$error_message = '';

// Check if user is a teacher
$check_stmt = $conn->prepare("SELECT * FROM user_details WHERE SVVNetID = ? AND user_type = 'teacher'");
$check_stmt->bind_param("s", $SVVNetID);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: login.php");
    exit;
}

$teacher_data = $result->fetch_assoc();
$check_stmt->close();

// Get all classes taught by this teacher
$classes_stmt = $conn->prepare("SELECT DISTINCT class, section FROM user_details WHERE user_type = 'student' ORDER BY class, section");
$classes_stmt->execute();
$classes_result = $classes_stmt->get_result();
$classes = [];

while ($class = $classes_result->fetch_assoc()) {
    $classes[] = $class;
}
$classes_stmt->close();

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_grades'])) {
    $student_id = $_POST['student_id'];
    $subject_name = $_POST['subject_name'];
    $marks = $_POST['marks'];
    $exam_date = $_POST['exam_date'];

    $stmt = $conn->prepare("INSERT INTO student_grades (student_id, subject_name, marks, exam_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $student_id, $subject_name, $marks, $exam_date);
    
    if ($stmt->execute()) {
        $success_message = "Grades submitted successfully!";
    } else {
        $error_message = "Error submitting grades: " . $conn->error;
    }
    $stmt->close();
}

// Get selected class students
$selected_class = isset($_GET['class']) ? $_GET['class'] : '';
$selected_section = isset($_GET['section']) ? $_GET['section'] : '';
$students = [];

if ($selected_class && $selected_section) {
    $students_stmt = $conn->prepare("SELECT * FROM user_details WHERE user_type = 'student' AND class = ? AND section = ? ORDER BY roll_number");
    $students_stmt->bind_param("ss", $selected_class, $selected_section);
    $students_stmt->execute();
    $students_result = $students_stmt->get_result();
    
    while ($student = $students_result->fetch_assoc()) {
        $students[] = $student;
    }
    $students_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Dashboard</title>
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
            text-decoration: none;
            color: #fff;
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
            overflow: hidden;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            margin-left: 10px;
            display: none;
        }

        .user-info h4 {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .user-info p {
            font-size: 12px;
            color: #aaa;
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

        /* Report specific styles can be added here */

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

        /* Card and grid styles */
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

        /* Grade Form Styles */
        .grade-form {
            margin-top: 30px;
            background: #1e1e1e;
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .grade-form h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #fff;
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

        .form-group select,
        .form-group input {
            background: #2a2a2a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px;
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-group select:focus,
        .form-group input:focus {
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
            margin-top: 20px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 90, 249, 0.4);
        }

        /* Grades Table Styles */
        .grades-list {
            margin-top: 30px;
            background: #1e1e1e;
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .grades-list h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #fff;
        }

        .grades-table {
            width: 100%;
            border-collapse: collapse;
        }

        .grades-table th,
        .grades-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .grades-table th {
            color: #aaa;
            font-weight: 500;
            font-size: 14px;
        }

        .grades-table tr:hover td {
            background: rgba(106, 90, 249, 0.05);
        }

        .grades-table tr:last-child td {
            border-bottom: none;
        }

        /* Alert Styles */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: rgba(77, 255, 136, 0.1);
            border: 1px solid rgba(77, 255, 136, 0.2);
            color: #4dff88;
        }

        .alert-error {
            background: rgba(255, 77, 77, 0.1);
            border: 1px solid rgba(255, 77, 77, 0.2);
            color: #ff4d4d;
        }

        /* Class Grid and Card Styles */
        .class-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
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
            transform: translateY(-5px);
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

        .section-name {
            color: #aaa;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
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
                <a href="students.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                </a>
                <a href="report.php" class="menu-item active">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <a href="attendance.php" class="menu-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Attendance</span>
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
            <a href="profile.php" class="user-profile">
                <div class="avatar">
                    <?php if (!empty($teacher_data['profile_picture'])): ?>
                        <img src="uploads/<?php echo $teacher_data['profile_picture']; ?>" alt="Profile Picture">
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

        <div class="main-content">
            <div class="header">
                <h1>Grade Reports</h1>
                <div class="date"><?php echo date('l, d F Y'); ?></div>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Class Selection -->
            <div class="class-grid">
                <?php foreach ($classes as $class): ?>
                    <a href="?class=<?php echo $class['class']; ?>&section=<?php echo $class['section']; ?>" 
                       class="class-card <?php echo ($selected_class === $class['class'] && $selected_section === $class['section']) ? 'active' : ''; ?>">
                        <div class="class-name">Class <?php echo $class['class']; ?></div>
                        <div class="section-name">Section <?php echo $class['section']; ?></div>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($selected_class && $selected_section && count($students) > 0): ?>
                <div class="grade-form">
                    <h2>Submit Grades - Class <?php echo $selected_class; ?> Section <?php echo $selected_section; ?></h2>
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Student</label>
                                <select name="student_id" required>
                                    <option value="">Select Student</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['SVVNetID']; ?>">
                                            <?php echo $student['roll_number'] . ' - ' . $student['full_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Subject</label>
                                <select name="subject_name" required>
                                    <option value="">Select Subject</option>
                                    <option value="Mathematics">Mathematics</option>
                                    <option value="Physics">Physics</option>
                                    <option value="Chemistry">Chemistry</option>
                                    <option value="English">English</option>
                                    <option value="Computer Science">Computer Science</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Marks</label>
                                <input type="number" name="marks" min="0" max="100" required>
                            </div>

                            <div class="form-group">
                                <label>Exam Date</label>
                                <input type="date" name="exam_date" required>
                            </div>
                        </div>

                        <button type="submit" name="submit_grades" class="submit-btn">
                            <i class="fas fa-save"></i> Submit Grades
                        </button>
                    </form>
                </div>

                <!-- Display existing grades -->
                <div class="grades-list">
                    <h2>Existing Grades</h2>
                    <table class="grades-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Exam Date</th>
                                <th>Added On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $grades_stmt = $conn->prepare("
                                SELECT g.*, u.full_name 
                                FROM student_grades g
                                JOIN user_details u ON g.student_id = u.SVVNetID
                                WHERE u.class = ? AND u.section = ?
                                ORDER BY g.created_at DESC
                            ");
                            $grades_stmt->bind_param("ss", $selected_class, $selected_section);
                            $grades_stmt->execute();
                            $grades_result = $grades_stmt->get_result();
                            
                            while ($grade = $grades_result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $grade['full_name']; ?></td>
                                <td><?php echo $grade['subject_name']; ?></td>
                                <td><?php echo $grade['marks']; ?></td>
                                <td><?php echo date('d M Y', strtotime($grade['exam_date'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($grade['created_at'])); ?></td>
                            </tr>
                            <?php endwhile;
                            $grades_stmt->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; 
            // Close the connection at the very end of the file
            $conn->close();
            ?>
        </div>
    </div>
</body>
</html>