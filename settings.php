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

$SVVNetID = $_SESSION['SVVNetID'];
$success_message = '';
$error_message = '';

// Get user details
$stmt = $conn->prepare("SELECT * FROM user_details WHERE SVVNetID = ?");
$stmt->bind_param("s", $SVVNetID);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $email = $_POST['email'];
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        
        // Verify current password
        $check_pwd = $conn->prepare("SELECT password FROM signupdetails WHERE SVVNetID = ?");
        $check_pwd->bind_param("s", $SVVNetID);
        $check_pwd->execute();
        $pwd_result = $check_pwd->get_result();
        $pwd_data = $pwd_result->fetch_assoc();
        
        if (password_verify($current_password, $pwd_data['password'])) {
            // Update email
            $update_email = $conn->prepare("UPDATE user_details SET email = ? WHERE SVVNetID = ?");
            $update_email->bind_param("ss", $email, $SVVNetID);
            $update_email->execute();
            
            // Update password if new one is provided
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pwd = $conn->prepare("UPDATE signupdetails SET password = ? WHERE SVVNetID = ?");
                $update_pwd->bind_param("ss", $hashed_password, $SVVNetID);
                $update_pwd->execute();
            }
            
            $success_message = "Settings updated successfully!";
        } else {
            $error_message = "Current password is incorrect";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Class Roster</title>
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
        }

        /* Copy your existing sidebar styles here */
        .sidebar {
            width: 240px;
            background: #1e1e1e;
            padding: 20px;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            height: 100vh;
            position: fixed;
        }

        .main-content {
            flex: 1;
            margin-left: 240px;
            padding: 30px;
        }

        .settings-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .settings-header {
            margin-bottom: 30px;
        }

        .settings-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .settings-section {
            background: #1e1e1e;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .settings-section h2 {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #6a5af9;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #aaa;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            background: #2a2a2a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
        }

        .form-group input:focus {
            border-color: #6a5af9;
            outline: none;
        }

        .save-btn {
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 90, 249, 0.4);
        }

        .message {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .success {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
            border: 1px solid rgba(76, 175, 80, 0.2);
        }

        .error {
            background: rgba(244, 67, 54, 0.1);
            color: #F44336;
            border: 1px solid rgba(244, 67, 54, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Copy your existing sidebar HTML here -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-book" style="color: #6a5af9; font-size: 24px;"></i>
                <h2>Class Roster</h2>
            </div>
            <div class="sidebar-menu">
                <a href="<?php echo $user_data['user_type'] === 'student' ? 'student.php' : 'teacher.php'; ?>" class="menu-item">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedule</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Attendance</span>
                </a>
                <a href="settings.php" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
            <div class="user-profile">
                <div class="avatar">
                    <?php echo strtoupper(substr($user_data['full_name'], 0, 1)); ?>
                </div>
                <div class="user-info">
                    <h4><?php echo $user_data['full_name']; ?></h4>
                    <p><?php echo ucfirst($user_data['user_type']); ?></p>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="settings-container">
                <div class="settings-header">
                    <h1>Settings</h1>
                    <p style="color: #aaa;">Manage your account settings and preferences</p>
                </div>

                <?php if ($success_message): ?>
                    <div class="message success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="message error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="settings-section">
                    <h2>Account Settings</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label>SVV Net ID</label>
                            <input type="text" value="<?php echo $user_data['SVVNetID']; ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" value="<?php echo $user_data['full_name']; ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" value="<?php echo $user_data['email']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label>New Password (leave blank to keep current)</label>
                            <input type="password" name="new_password">
                        </div>
                        <button type="submit" name="update_profile" class="save-btn">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>