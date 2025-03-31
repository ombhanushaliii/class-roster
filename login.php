<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "class-roster";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

            echo "<p style='color:green;'>Login successful! Redirecting...</p>";
            header("refresh:2; url=index.php");
            exit();
        } else {
            echo "<p style='color:red;'>Invalid password!</p>";
        }
    } else {
        echo "<p style='color:red;'>User not found!</p>";
    }

    $stmt->close();
} else {
    echo "<p style='color:red;'>Form data not submitted properly.</p>";
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
    <table width="100%" height="100%">
        <tr>
            <td align="center" valign="middle">
                <form method="POST" action="login.php">
                    <div class="login-box">
                        <div class="login-header">
                            <header>Login</header>
                        </div>
                        <div class="input-box">
                            <input type="text" name="SVVNetID" class="input-field" placeholder="SVVNetID" autocomplete="off" required>
                        </div>
                        <div class="input-box">
                            <input type="password" name="password" class="input-field" placeholder="Password" autocomplete="off" required>
                        </div>
                        <div class="input-submit">
                            <button type="submit" class="submit-btn" id="submit">Sign In</button>
                        </div>
                    </div>
                </form>
            </td>
        </tr>
    </table>
</body>
</html>