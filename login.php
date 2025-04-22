<?php
session_start();
require_once 'config.php'; // Include the config file

$servername = $DB_HOST;
$username = $DB_USER;
$password = $DB_PASS;
$dbname = $DB_NAME;

$conn = new mysqli($servername, $username, $password, $dbname, $DB_PORT);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$error_message = ""; // Initialize error message
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['SVVNetID']) && isset($_POST['password'])) {
    $SVVNetID = trim($_POST['SVVNetID']);
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT SVVNetID, password FROM signupdetails WHERE SVVNetID = ?");
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("s", $SVVNetID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['SVVNetID'] = $SVVNetID;
            
            // Check if user details exist
            $check_details = $conn->prepare("SELECT id FROM user_details WHERE SVVNetID = ?");
            $check_details->bind_param("s", $SVVNetID);
            $check_details->execute();
            $check_details->store_result();
            
            if ($check_details->num_rows > 0) {
                // Get the user type from user_details
                $get_type = $conn->prepare("SELECT user_type FROM user_details WHERE SVVNetID = ?");
                $get_type->bind_param("s", $SVVNetID);
                $get_type->execute();
                $type_result = $get_type->get_result();
                $type_data = $type_result->fetch_assoc();
                
                // Set the user type in session
                $_SESSION['user_type'] = $type_data['user_type'];
                
                // User has completed profile, redirect based on user type
                echo "<p class='success-message'>Login successful! Redirecting...</p>";
                
                if ($_SESSION['user_type'] === 'teacher') {
                    header("refresh:1; url=teacher.php");
                } else {
                    header("refresh:1; url=student.php");
                }
            } else {
                // First login, go to details page
                echo "<p class='success-message'>Login successful! Please complete your profile.</p>";
                header("refresh:1; url=details.php");
            }
            exit();
        } else {
            $error_message = "Invalid password!";
        }
    } else {
        $error_message = "User not found!";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <title>Login</title>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Login</h2>
            </div>
            <form method="POST" action="login.php">
                <div class="input-group">
                    <input type="text" name="SVVNetID" required>
                    <label>SVVNetID</label>
                </div>
                <div class="input-group">
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>
                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <button type="submit" class="btn">Login</button>
                <div class="signup-link">
                    Don't have an account? <a href="signup.php">Register</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<!-- bruh -->