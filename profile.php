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

// Add profile_pic column to user_details table if it doesn't exist
$conn->query("ALTER TABLE user_details ADD COLUMN IF NOT EXISTS profile_pic varchar(255) DEFAULT 'default.png'");

$SVVNetID = $_SESSION['SVVNetID'];
$message = '';
$success = false;

// Get user details
$check_stmt = $conn->prepare("SELECT * FROM user_details WHERE SVVNetID = ?");
$check_stmt->bind_param("s", $SVVNetID);
$check_stmt->execute();
$result = $check_stmt->get_result();

// If user doesn't exist, redirect to details page
if ($result->num_rows === 0) {
    header("Location: details.php");
    exit;
}

$user_data = $result->fetch_assoc();
$check_stmt->close();

// Set default values if keys don't exist to prevent warnings
$user_data['full_name'] = $user_data['full_name'] ?? 'N/A';
$user_data['email'] = $user_data['email'] ?? 'N/A';
$user_data['roll_number'] = $user_data['roll_number'] ?? 'N/A';
$user_data['class'] = $user_data['class'] ?? 'N/A';
$user_data['section'] = $user_data['section'] ?? 'N/A';
$user_data['department'] = $user_data['department'] ?? 'N/A';
$user_data['employee_id'] = $user_data['employee_id'] ?? 'N/A';
$user_data['user_type'] = $user_data['user_type'] ?? 'student';

$user_type = $user_data['user_type'];

// Handle profile picture upload
$profile_pic = "default.png"; // Default image
$upload_error = '';

// Check if uploads directory exists, if not create it
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Process image upload first, before handling form data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["error"] == 0) {
    $target_dir = "uploads/";
    $file_name = basename($_FILES["profile_pic"]["name"]);
    $file_size = $_FILES["profile_pic"]["size"];
    $file_tmp = $_FILES["profile_pic"]["tmp_name"];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_extensions = ["jpg", "jpeg", "png"];

    if (!in_array($file_ext, $allowed_extensions)) {
        $upload_error = "Invalid file type. Only JPG, JPEG, and PNG are allowed.";
    } elseif ($file_size > 5 * 1024 * 1024) { // 5MB limit
        $upload_error = "File is too large. Maximum size is 5MB.";
    } else {
        $new_file_name = "profile_" . $SVVNetID . "." . $file_ext;
        $target_file = $target_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            // Update the database with the new file name
            $pic_stmt = $conn->prepare("UPDATE user_details SET profile_pic = ? WHERE SVVNetID = ?");
            if ($pic_stmt === false) {
                $upload_error = "Error preparing statement: " . $conn->error;
            } else {
                $pic_stmt->bind_param("ss", $new_file_name, $SVVNetID);
                $pic_stmt->execute();
                $pic_stmt->close();
                
                $profile_pic = $new_file_name;
                $success = true;
                $message = "<p class='success-message'>Profile picture updated successfully!</p>";
                
                // Refresh user data after updating profile picture
                $check_stmt = $conn->prepare("SELECT * FROM user_details WHERE SVVNetID = ?");
                if ($check_stmt) {
                    $check_stmt->bind_param("s", $SVVNetID);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    $user_data = $result->fetch_assoc();
                    $check_stmt->close();
                }
            }
        } else {
            $upload_error = "Error uploading file.";
        }
    }
}

// Get profile picture if exists
$pic_stmt = $conn->prepare("SELECT profile_pic FROM user_details WHERE SVVNetID = ?");
if ($pic_stmt === false) {
    $message .= "<p class='error-message'>Error querying profile picture: " . $conn->error . "</p>";
} else {
    $pic_stmt->bind_param("s", $SVVNetID);
    $pic_stmt->execute();
    $pic_result = $pic_stmt->get_result();
    $pic_data = $pic_result->fetch_assoc();
    if ($pic_data && !empty($pic_data['profile_pic'])) {
        $profile_pic = $pic_data['profile_pic'];
    }
    $pic_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
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
            transition: width 0.3s ease;
        }
        
        /* Student sidebar specific styles */
        .sidebar.student-sidebar {
            width: 90px;
            padding: 20px 10px;
            align-items: center;
        }
        
        .sidebar.student-sidebar:hover {
            width: 240px;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Student sidebar header styles */
        .student-sidebar .sidebar-header {
            justify-content: center;
            width: 100%;
        }
        
        .student-sidebar:hover .sidebar-header {
            justify-content: flex-start;
        }

        .sidebar-header h2 {
            font-size: 22px;
            font-weight: 600;
            margin-left: 10px;
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .student-sidebar .sidebar-header h2 {
            display: none;
        }
        
        .student-sidebar:hover .sidebar-header h2 {
            display: block;
        }

        .sidebar-menu {
            flex: 1;
        }
        
        .student-sidebar .sidebar-menu {
            width: 100%;
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
        
        /* Student menu item styles */
        .student-sidebar .menu-item {
            justify-content: center;
            padding: 15px;
            width: 100%;
            overflow: hidden;
            border-radius: 12px;
            margin-bottom: 10px;
        }
        
        .student-sidebar:hover .menu-item {
            justify-content: flex-start;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(106, 90, 249, 0.1);
            color: #6a5af9;
        }

        .menu-item i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .student-sidebar .menu-item i {
            font-size: 20px;
            min-width: 24px;
            margin-right: 0;
        }
        
        .student-sidebar .menu-item span {
            margin-left: 10px;
            display: none;
            white-space: nowrap;
        }
        
        .student-sidebar:hover .menu-item span {
            display: block;
        }
        
        .student-sidebar:hover .menu-item i {
            margin-right: 10px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }
        
        .student-sidebar .user-profile {
            justify-content: center;
            padding: 15px 10px;
            width: 100%;
        }
        
        .student-sidebar:hover .user-profile {
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
            margin-right: 10px;
        }
        
        .student-sidebar .avatar {
            margin-right: 0;
            flex-shrink: 0;
        }
        
        .student-sidebar:hover .avatar {
            margin-right: 10px;
        }

        .user-info {
            flex: 1;
        }
        
        .student-sidebar .user-info {
            margin-left: 10px;
            display: none;
        }
        
        .student-sidebar:hover .user-info {
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

        /* Profile Section */
        .profile-section {
            background: #1e1e1e;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            position: relative;
        }

        .profile-picture img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #6a5af9;
        }

        .profile-picture .edit-icon {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 35px;
            height: 35px;
            background: #6a5af9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .profile-picture .edit-icon:hover {
            background: #574dc9;
        }

        .profile-info {
            flex: 1;
        }

        .profile-info h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .profile-info p {
            color: #aaa;
            margin-bottom: 10px;
        }

        .profile-info .role-badge {
            display: inline-block;
            padding: 5px 15px;
            background: rgba(106, 90, 249, 0.1);
            color: #6a5af9;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        /* Form Styles */
        .form-section {
            margin-top: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #ddd;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            height: 45px;
            background: #2a2a2a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 0 15px;
            color: #fff;
            font-size: 15px;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: rgba(106, 90, 249, 0.5);
        }

        .form-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-outline {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #6a5af9;
            color: #6a5af9;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            background: rgba(106, 90, 249, 0.1);
        }

        .btn-primary {
            padding: 10px 20px;
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            border: none;
            color: #fff;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #5648d8, #7050f0);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 90, 249, 0.4);
        }

        .upload-form {
            display: none;
        }

        .error-message {
            color: #ff4d4d;
            margin-top: 5px;
            font-size: 14px;
        }

        .success-message {
            color: #4dff88;
            margin-top: 5px;
            font-size: 14px;
        }

        .static-field {
            background: #2a2a2a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px 15px;
            color: #fff;
            font-size: 15px;
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

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .profile-info {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar <?php echo ($user_type === 'student') ? 'student-sidebar' : ''; ?>">
            <div class="sidebar-header">
                <i class="fas fa-book" style="color: #6a5af9; font-size: 24px;"></i>
                <h2>Class Roster</h2>
            </div>
            <div class="sidebar-menu">
                <a href="<?php echo $user_type; ?>.php" class="menu-item">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <?php if ($user_type === 'student'): ?>
                <a href="#" class="menu-item">
                    <i class="fas fa-calendar"></i>
                    <span>Timetable</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-book"></i>
                    <span>Courses</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-tasks"></i>
                    <span>Assignments</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Grades</span>
                </a>
                <?php else: ?>
                <a href="#" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Classes</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedule</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Attendance</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <?php endif; ?>
                <a href="profile.php" class="menu-item active">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </div>
            <a href="profile.php" class="user-profile" style="text-decoration: none; color: inherit;">
                <div class="avatar">
                    <?php echo strtoupper(substr($user_data['full_name'], 0, 1)); ?>
                </div>
                <div class="user-info">
                    <h4><?php echo $user_data['full_name']; ?></h4>
                    <?php if ($user_type === 'student'): ?>
                    <p>Class <?php echo $user_data['class'] . ' - ' . $user_data['section']; ?></p>
                    <?php else: ?>
                    <p><?php echo $user_data['department']; ?></p>
                    <?php endif; ?>
                </div>
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>User Profile</h1>
                <div class="date"><?php echo date("l, d F Y"); ?></div>
            </div>

            <!-- Profile Section -->
            <div class="profile-section">
                <div class="profile-header">
                    <div class="profile-picture">
                        <?php if (file_exists('uploads/' . $profile_pic)): ?>
                            <img src="uploads/<?php echo $profile_pic; ?>" alt="Profile Picture">
                        <?php else: ?>
                            <div class="avatar" style="width: 100%; height: 100%; font-size: 48px;">
                                <?php echo strtoupper(substr($user_data['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <label for="profile_pic_input" class="edit-icon">
                            <i class="fas fa-camera"></i>
                        </label>
                        <form id="upload-form" class="upload-form" method="post" enctype="multipart/form-data">
                            <input type="file" id="profile_pic_input" name="profile_pic" accept="image/jpeg, image/png" style="display: none;">
                        </form>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo $user_data['full_name']; ?></h2>
                        <p><?php echo $user_data['email']; ?></p>
                        <div class="role-badge">
                            <?php echo ucfirst($user_type); ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="message-box">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($upload_error)): ?>
                    <div class="error-message">
                        <?php echo $upload_error; ?>
                    </div>
                <?php endif; ?>

                <div class="form-section">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <div class="static-field"><?php echo $user_data['full_name']; ?></div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="static-field"><?php echo $user_data['email']; ?></div>
                        </div>
                        
                        <?php if ($user_type === 'student'): ?>
                            <div class="form-group">
                                <label for="roll_number">Roll Number</label>
                                <div class="static-field"><?php echo $user_data['roll_number']; ?></div>
                            </div>
                            <div class="form-group">
                                <label for="class">Class</label>
                                <div class="static-field"><?php echo $user_data['class']; ?></div>
                            </div>
                            <div class="form-group">
                                <label for="section">Section</label>
                                <div class="static-field"><?php echo $user_data['section']; ?></div>
                            </div>
                        <?php else: ?>
                            <div class="form-group">
                                <label for="department">Department</label>
                                <div class="static-field"><?php echo $user_data['department']; ?></div>
                            </div>
                            <div class="form-group">
                                <label for="employee_id">Employee ID</label>
                                <div class="static-field"><?php echo $user_data['employee_id']; ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="svvnetid">SVVNetID</label>
                            <div class="static-field"><?php echo $user_data['SVVNetID']; ?></div>
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <a href="<?php echo $user_type; ?>.php" class="btn-outline">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Profile picture upload handling
        document.getElementById('profile_pic_input').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                // Automatically submit the form when a file is selected
                document.getElementById('upload-form').submit();
            }
        });

        // Display success message for a limited time
        <?php if ($success): ?>
        setTimeout(function() {
            const messages = document.querySelectorAll('.success-message');
            messages.forEach(function(message) {
                message.style.transition = 'opacity 0.5s ease';
                message.style.opacity = '0';
                setTimeout(function() {
                    message.style.display = 'none';
                }, 500);
            });
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>