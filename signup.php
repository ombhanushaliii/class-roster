<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "class roster"; // Fixed database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'signupdetails'");
if ($result->num_rows == 0) {
    die("Error: Table 'signupdetails' does not exist. Please create it.");
}

// Check if form data is set
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['SVVNetID']) && isset($_POST['password']) && isset($_POST['confirm_password'])) {
        
        $SVVNetID = trim($_POST['SVVNetID']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Check if passwords match
        if ($password !== $confirm_password) {
            echo "Error: Passwords do not match!";
            exit;
        }

        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if SVVNetID already exists
        $check_stmt = $conn->prepare("SELECT SVVNetID FROM signupdetails WHERE SVVNetID = ?");
        $check_stmt->bind_param("s", $SVVNetID);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            echo "Error: SVVNetID already exists!";
            exit;
        }
        $check_stmt->close();

        // Insert new user into signupdetails table
        $stmt = $conn->prepare("INSERT INTO signupdetails (SVVNetID, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $SVVNetID, $hashed_password);

        if ($stmt->execute()) {
            echo "Signup successful! Redirecting to login page...";
            header("refresh:2; url=login.html"); // Redirect to login page after 2 seconds
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: Form data not submitted properly.";
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
    <link rel="stylesheet" href="./signup.css">
    <title>Sign Up</title>
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
                <input type="password" name="confirm_password" class="input-field" placeholder="Retype New Password" autocomplete="off" required>
            </div>

            <div class="input-submit">
                <button type="submit" class="submit-btn">Sign Up</button>
            </div>
        </form>
    
        <div class="login-link">
            <p>Already have an account? <a href="login.html">Login</a></p>
        </div>
    </div>
</body>
</html>

