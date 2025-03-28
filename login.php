<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "class_roster"; // Fixed space issue in database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['SVVNetID']) && isset($_POST['password'])) {
    $SVVNetID = trim($_POST['SVVNetID']);
    $password = $_POST['password'];

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT SVVNetID, password FROM signupdetails WHERE SVVNetID = ?");
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("s", $SVVNetID);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['SVVNetID'] = $SVVNetID;

            echo "<p style='color:green;'>Login successful! Redirecting...</p>";
            header("refresh:2; url=index.html");
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