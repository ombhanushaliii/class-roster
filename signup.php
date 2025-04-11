<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "class_roster"; 

$conn = new mysqli($servername, $username, $password, $dbname);

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
    <style>
        .success-message {
            color: green;
            font-size: 16px;
            text-align: center;
            margin-top: 10px;
        }
        .error-message {
            color: red;
            font-size: 16px;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="signup-box">
        <div class="signup-header">
            <header>Sign Up</header>
        </div>
        <form action="signup.php" method="POST">
            <div class="input-box">
                <input type="text" name="SVVNetID" class="input-field" placeholder="SVVNetID" autocomplete="off" required>
            </div>
            <div class="input-box">
                <input type="password" name="password" class="input-field" placeholder="Create New Password" autocomplete="off" required>
            </div>
            <div class="input-box">
                <input type="password" name="confirm_password" class="input-field" placeholder="Confirm Password" autocomplete="off" required>
            </div>
            <div class="input-submit">
                <button type="submit" class="submit-btn">Sign Up</button>
            </div>
        </form>
    
        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>

        <!-- Success/Error Message Display Here -->
        <div class="message-box">
            <?php echo $message; ?>
        </div>
    </div>
</body>
</html>
