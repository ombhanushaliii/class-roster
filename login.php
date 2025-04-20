<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "class_roster";
$conn = new mysqli($servername, $username, $password, $dbname, 3307);
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
            echo "<p class='success-message'>Login successful! Redirecting...</p>";
            header("refresh:1; url=dashboard.php");
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