<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

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
$message = "";
$redirect = false;

// Check if user details already exist
$check_stmt = $conn->prepare("SELECT user_type FROM user_details WHERE SVVNetID = ?");
$check_stmt->bind_param("s", $SVVNetID);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

// If user details exist, redirect to appropriate dashboard
if ($check_result->num_rows > 0) {
    $user_data = $check_result->fetch_assoc();
    $user_type = $user_data['user_type'];
    
    if ($user_type === 'teacher') {
        header("Location: teacher.php");
    } else {
        header("Location: student.php");
    }
    exit;
}
$check_stmt->close();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $user_type = $_POST['user_type'];
    
    // Additional fields based on user type
    if ($user_type === 'student') {
        $roll_number = trim($_POST['roll_number']);
        $class = trim($_POST['class']);
        $section = trim($_POST['section']);
        
        $stmt = $conn->prepare("INSERT INTO user_details (SVVNetID, full_name, email, user_type, roll_number, class, section) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $SVVNetID, $full_name, $email, $user_type, $roll_number, $class, $section);
    } else {
        $department = trim($_POST['department']);
        $employee_id = trim($_POST['employee_id']);
        
        $stmt = $conn->prepare("INSERT INTO user_details (SVVNetID, full_name, email, user_type, department, employee_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $SVVNetID, $full_name, $email, $user_type, $department, $employee_id);
    }
    
    if ($stmt->execute()) {
        $message = "<p class='success-message'>Details saved successfully! Redirecting...</p>";
        $redirect = true;
        echo "<script>
                setTimeout(function(){
                    window.location.href =  '{$user_type}.php';
                }, 1500);
              </script>";
    } else {
        $message = "<p class='error-message'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Create user_details table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS user_details (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    SVVNetID VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    user_type ENUM('student', 'teacher') NOT NULL,
    roll_number VARCHAR(20) NULL,
    class VARCHAR(20) NULL,
    section VARCHAR(10) NULL,
    department VARCHAR(50) NULL,
    employee_id VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #121212;
            overflow-x: hidden;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 20px;
        }

        .card {
            width: 500px;
            background: #1e1e1e;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
            padding: 40px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .card-header h2 {
            color: #fff;
            font-size: 28px;
            font-weight: 600;
        }

        .card-header p {
            color: #aaa;
            margin-top: 10px;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group input, .input-group select {
            width: 100%;
            height: 50px;
            background: #2a2a2a;
            border: none;
            border-radius: 8px;
            padding: 0 20px;
            font-size: 16px;
            color: #fff;
            outline: none;
            transition: 0.3s;
        }

        .input-group select {
            appearance: none;
            cursor: pointer;
        }

        .input-group label {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            color: #aaa;
            pointer-events: none;
            transition: 0.3s;
        }

        .input-group input:focus + label,
        .input-group input:valid + label {
            top: 0;
            left: 15px;
            font-size: 12px;
            padding: 0 5px;
            background: #2a2a2a;
        }

        .role-selection {
            margin-bottom: 25px;
        }

        .role-selection label {
            display: block;
            color: #ddd;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .role-toggle {
            display: flex;
            background: #2a2a2a;
            border-radius: 8px;
            overflow: hidden;
        }

        .role-toggle label {
            flex: 1;
            padding: 12px 0;
            text-align: center;
            color: #aaa;
            cursor: pointer;
            transition: 0.3s;
        }

        .role-toggle input[type="radio"] {
            display: none;
        }

        .role-toggle input[type="radio"]:checked + label {
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            color: #fff;
        }

        .form-section {
            display: none;
            margin-top: 20px;
        }

        .form-section.active {
            display: block;
        }

        .btn {
            width: 100%;
            height: 50px;
            background: linear-gradient(45deg, #6a5af9, #8162fc);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 18px;
            font-weight: 500;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            background: linear-gradient(45deg, #5648d8, #7050f0);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 90, 249, 0.4);
        }

        .error-message {
            color: #ff4d4d;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .success-message {
            color: #4dff88;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }

        /* Add a subtle animated background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #121212, #1e1e1e);
            z-index: -1;
        }

        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(106, 90, 249, 0.15), transparent 60%);
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Complete Your Profile</h2>
                <p>Please provide your details to continue</p>
            </div>
            <?php if (!empty($message)) echo $message; ?>
            <form action="details.php" method="POST">
                <div class="input-group">
                    <input type="text" name="full_name" required>
                    <label>Full Name</label>
                </div>
                <div class="input-group">
                    <input type="email" name="email" required>
                    <label>Email Address</label>
                </div>
                
                <div class="role-selection">
                    <label>Select your role:</label>
                    <div class="role-toggle">
                        <input type="radio" id="student" name="user_type" value="student" checked>
                        <label for="student">Student</label>
                        <input type="radio" id="teacher" name="user_type" value="teacher">
                        <label for="teacher">Teacher</label>
                    </div>
                </div>
                
                <div id="student-section" class="form-section active">
                    <div class="input-group">
                        <input type="text" name="roll_number" id="roll_number" required>
                        <label for="roll_number">Roll Number</label>
                    </div>
                    <div class="input-group">
                        <input type="text" name="class" id="class" required>
                        <label for="class">Class</label>
                    </div>
                    <div class="input-group">
                        <input type="text" name="section" id="section" required>
                        <label for="section">Section</label>
                    </div>
                </div>
                
                <div id="teacher-section" class="form-section">
                    <div class="input-group">
                        <input type="text" name="department" required>
                        <label>Department</label>
                    </div>
                    <div class="input-group">
                        <input type="text" name="employee_id" required>
                        <label>Employee ID</label>
                    </div>
                </div>
                
                <button type="submit" class="btn">Submit</button>
            </form>
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'teacher'): ?>
                <div class="teacher-link" style="margin-top: 20px; text-align: center;">
                    <a href="teacher.php" style="color: #6a5af9; text-decoration: none;">Go to Teacher Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Toggle between student and teacher form sections
        const studentRadio = document.getElementById('student');
        const teacherRadio = document.getElementById('teacher');
        const studentSection = document.getElementById('student-section');
        const teacherSection = document.getElementById('teacher-section');
        
        // Function to toggle required attributes
        function toggleRequired(section, isRequired) {
            const inputs = section.querySelectorAll('input[type="text"], input[type="email"]');
            inputs.forEach(input => {
                input.required = isRequired;
                // Make sure we don't break the floating labels
                if (input.value) {
                    input.classList.add('has-value');
                }
            });
        }
        
        studentRadio.addEventListener('change', function() {
            if (this.checked) {
                studentSection.classList.add('active');
                teacherSection.classList.remove('active');
                
                // Toggle required attributes
                toggleRequired(studentSection, true);
                toggleRequired(teacherSection, false);
            }
        });
        
        teacherRadio.addEventListener('change', function() {
            if (this.checked) {
                teacherSection.classList.add('active');
                studentSection.classList.remove('active');
                
                // Toggle required attributes
                toggleRequired(teacherSection, true);
                toggleRequired(studentSection, false);
            }
        });
        
        // Make sure the correct fields are required initially
        if (studentRadio.checked) {
            toggleRequired(studentSection, true);
            toggleRequired(teacherSection, false);
        } else {
            toggleRequired(teacherSection, true);
            toggleRequired(studentSection, false);
        }
        
        // Handle floating labels for inputs
        const inputs = document.querySelectorAll('.input-group input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value) {
                    this.classList.add('has-value');
                } else {
                    this.classList.remove('has-value');
                }
            });
        });
    </script>
</body>
</html>