<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Check if user type is teacher
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    header("Location: details.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "class_roster";

$conn = new mysqli($servername, $username, $password, $dbname, 3307);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$SVVNetID = $_SESSION['SVVNetID'];

// Get teacher details
$stmt = $conn->prepare("SELECT * FROM user_details WHERE SVVNetID = ?");
$stmt->bind_param("s", $SVVNetID);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$full_name = $user_data['full_name'];
$department = $user_data['department'];
$employee_id = $user_data['employee_id'];

// Get classes taught (simplified for demo)
$classes = [];
// Sample data - in production, you would fetch this from database
$classes[] = ['name' => 'DBMS', 'class' => 'SE COMP A', 'time' => '9:00 AM - 10:00 AM', 'room' => 'L301'];
$classes[] = ['name' => 'OS Lab', 'class' => 'SE COMP B', 'time' => '10:00 AM - 12:00 PM', 'room' => 'Lab 5'];
$classes[] = ['name' => 'Advanced DBMS', 'class' => 'TE COMP A', 'time' => '1:00 PM - 2:00 PM', 'room' => 'L401'];

// Get pending tasks (simplified for demo)
$tasks = [];
$tasks[] = ['title' => 'Grade OS Lab assignments', 'deadline' => 'Today', 'status' => 'urgent'];
$tasks[] = ['title' => 'Prepare question paper for DBMS', 'deadline' => 'Tomorrow', 'status' => 'normal'];
$tasks[] = ['title' => 'Faculty meeting', 'deadline' => '25 Apr 2025', 'status' => 'normal'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard | ClassRoster</title>
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

        /* Layout */
        .container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: #1e1e1e;
            padding: 30px 20px;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            position: fixed;
            height: 100%;
            width: 250px;
            overflow-y: auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo h2 {
            font-size: 24px;
            font-weight: 600;
            color: #fff;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px;
            border-radius: 10px;
            text-decoration: none;
            color: #aaa;
            transition: all 0.3s;
            gap: 15px;
        }

        .nav-link.active, .nav-link:hover {
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            color: #fff;
            box-shadow: 0 5px 15px rgba(106, 90, 249, 0.4);
        }

        .nav-link i {
            font-size: 20px;
        }

        .user-profile {
            position: absolute;
            bottom: 30px;
            width: calc(100% - 40px);
            background: #2a2a2a;
            border-radius: 10px;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 600;
            color: #fff;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-size: 16px;
            font-weight: 500;
            color: #fff;
        }

        .user-role {
            font-size: 12px;
            color: #aaa;
        }

        /* Main Content */
        .main {
            padding: 30px;
            margin-left: 250px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .greeting {
            font-size: 28px;
            font-weight: 600;
        }

        .greeting span {
            color: #6a5af9;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: #2a2a2a;
            border-radius: 10px;
            padding: 10px 20px;
            width: 300px;
        }

        .search-box input {
            background: transparent;
            border: none;
            color: #fff;
            padding: 5px;
            outline: none;
            flex: 1;
        }

        .search-box i {
            color: #aaa;
            margin-right: 10px;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .card {
            background: #1e1e1e;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(106, 90, 249, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6a5af9;
        }

        /* Classes Schedule */
        .class-list {
            margin-top: 15px;
        }

        .class-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #2a2a2a;
            border-radius: 10px;
            margin-bottom: 10px;
            transition: all 0.3s;
            cursor: pointer;
        }

        .class-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .class-info {
            flex: 1;
            margin-left: 15px;
        }

        .class-name {
            font-size: 16px;
            font-weight: 500;
        }

        .class-details {
            font-size: 12px;
            color: #aaa;
            margin-top: 5px;
        }

        /* Tasks */
        .task-list {
            margin-top: 15px;
        }

        .task-item {
            padding: 15px;
            background: #2a2a2a;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid;
            transition: all 0.3s;
        }

        .task-item.urgent {
            border-color: #ff4d4d;
        }

        .task-item.normal {
            border-color: #6a5af9;
        }

        .task-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .task-title {
            font-size: 16px;
            font-size: 16px;
            font-weight: 500;
        }

        .task-deadline {
        font-size: 12px;
        color: #aaa;
        }
    </style>
</head>
<body>
    <div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h2>ClassRoster</h2>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="teacher.php" class="nav-link active"><i class="fas fa-home"></i>Dashboard</a>
            </li>
            <li class="nav-item">
                <a href="attendance.php" class="nav-link"><i class="fas fa-check-circle"></i>Mark Attendance</a>
            </li>
            <li class="nav-item">
                <a href="marks.php" class="nav-link"><i class="fas fa-clipboard"></i>Update Marks</a>
            </li>
            <li class="nav-item">
                <a href="schedule.php" class="nav-link"><i class="fas fa-calendar-alt"></i>Lecture Schedule</a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i>Logout</a>
            </li>
        </ul>
        <div class="user-profile">
            <div class="avatar"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
            <div class="user-info">
                <div class="user-name"><?php echo $full_name; ?></div>
                <div class="user-role">Teacher</div>
            </div>
        </div>
        </div>

        <!-- Main content -->
        <div class="main">
        <div class="header">
            <div class="greeting">Hello, <span><?php echo $full_name; ?></span></div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search..." />
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Classes Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Today's Classes</div>
                    <div class="card-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                </div>
                <div class="class-list">
                    <?php foreach ($classes as $class): ?>
                        <div class="class-item">
                            <div class="class-info">
                                <div class="class-name"><?php echo $class['name']; ?></div>
                                <div class="class-details">
                                    <?php echo $class['class']; ?> | <?php echo $class['time']; ?> | <?php echo $class['room']; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tasks Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Pending Tasks</div>
                    <div class="card-icon"><i class="fas fa-tasks"></i></div>
                </div>
                <div class="task-list">
                    <?php foreach ($tasks as $task): ?>
                        <div class="task-item <?php echo $task['status']; ?>">
                            <div class="task-header">
                                <div class="task-title"><?php echo $task['title']; ?></div>
                                <div class="task-deadline"><?php echo $task['deadline']; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        </div>
        </div>

        <!-- Font Awesome for icons -->
        <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
        </body>
        </html>
