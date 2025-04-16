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

            echo "<p style='color:green; text-align:center;'>Login successful! Redirecting...</p>";
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
    <style>
        .error-message {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
    </style>
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
                        <?php if (!empty($error_message)): ?>
                            <div class="error-message"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                    </div>
                </form>
            </td>
        </tr>
    </table>
</body>
</html>
