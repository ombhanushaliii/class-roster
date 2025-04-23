<?php
session_start();

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

// Get student's grades
$grades_stmt = $conn->prepare("
    SELECT subject_name, marks, exam_date, created_at
    FROM student_grades
    WHERE student_id = ?
    ORDER BY created_at DESC
");

$grades_stmt->bind_param("s", $SVVNetID);
$grades_stmt->execute();
$grades_result = $grades_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades - Class Roster</title>
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
        }

        .container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

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
            color: #fff;
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

        .grades-card {
            background: #1e1e1e;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        }

        .grade-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 500;
        }

        .grade-high {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }

        .grade-medium {
            background: rgba(255, 193, 7, 0.2);
            color: #FFC107;
        }

        .grade-low {
            background: rgba(244, 67, 54, 0.2);
            color: #F44336;
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
                <a href="student.php" class="menu-item">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <a href="grades.php" class="menu-item active">
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
        </div>

        <div class="main-content">
            <div class="header">
                <h1>My Grades</h1>
                <div class="date"><?php echo date('l, d F Y'); ?></div>
            </div>

            <div class="grades-card">
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Exam Date</th>
                            <th>Added On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($grade = $grades_result->fetch_assoc()): 
                            $grade_class = '';
                            if ($grade['marks'] >= 80) $grade_class = 'grade-high';
                            elseif ($grade['marks'] >= 60) $grade_class = 'grade-medium';
                            else $grade_class = 'grade-low';
                        ?>
                        <tr>
                            <td><?php echo $grade['subject_name']; ?></td>
                            <td>
                                <span class="grade-badge <?php echo $grade_class; ?>">
                                    <?php echo $grade['marks']; ?>%
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($grade['exam_date'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($grade['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>