<?php
require_once 'config.php'; // Include the config file

$servername = $DB_HOST;
$username = $DB_USER;
$password = $DB_PASS;
$dbname = $DB_NAME; 

$conn = new mysqli($servername, $username, $password, $dbname, $DB_PORT);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SHOW TABLES LIKE 'signupdetails'");
if ($result->num_rows == 0) {
    die("Error: Table 'signupdetails' does not exist. Please create it.");
}

$message = ""; // Variable to store success or error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['SVVNetID']) && isset($_POST['password']) && isset($_POST['confirm_password'])) {
        
        $SVVNetID = trim($_POST['SVVNetID']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $message = "<p class='error-message'>Error: Passwords do not match!</p>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $check_stmt = $conn->prepare("SELECT SVVNetID FROM signupdetails WHERE SVVNetID = ?");
            $check_stmt->bind_param("s", $SVVNetID);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $message = "<p class='error-message'>Error: SVVNetID already exists!</p>";
            } else {
                $stmt = $conn->prepare("INSERT INTO signupdetails (SVVNetID, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $SVVNetID, $hashed_password);

                if ($stmt->execute()) {
                    $message = "<p class='success-message'>Signup successful! Redirecting to login page...</p>";
                    echo "<script>
                            setTimeout(function(){
                                window.location.href = 'login.php';
                            }, 2000);
                          </script>";
                } else {
                    $message = "<p class='error-message'>Error: " . $stmt->error . "</p>";
                }
                $stmt->close();
            }
            $check_stmt->close();
        }
    } else {
        $message = "<p class='error-message'>Error: Form data not submitted properly.</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="signup.css">
    <title>Sign Up</title>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Sign Up</h2>
            </div>
            <form action="signup.php" method="POST">
                <div class="input-group">
                    <input type="text" name="SVVNetID" required>
                    <label>SVVNetID</label>
                </div>
                <div class="input-group">
                    <input type="password" name="password" required>
                    <label>Create Password</label>
                </div>
                <div class="input-group">
                    <input type="password" name="confirm_password" required>
                    <label>Confirm Password</label>
                </div>
                <div class="message-box">
                    <?php echo $message; ?>
                </div>
                <button type="submit" class="btn">Sign Up</button>
                <div class="login-link">
                    Already have an account? <a href="login.php">Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>