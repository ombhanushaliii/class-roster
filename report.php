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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Reports - Class Roster</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Copy your existing dark theme styles here */
        .grade-form {
            background: #1e1e1e;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #aaa;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 10px;
            background: #2a2a2a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
        }

        .submit-btn {
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        .sidebar {
            width: 260px;
            background: #1e1e1e;
            padding: 20px;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            margin-bottom: 20px;
        }

        .sidebar-header i {
            color: #6a5af9;
            font-size: 24px;
        }

        .sidebar-header h2 {
            font-size: 20px;
            font-weight: 600;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .menu-item.active {
            background: #6a5af9;
        }

        .menu-item i {
            font-size: 18px;
            width: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-book"></i>
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
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>w

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
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>