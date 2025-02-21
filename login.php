<?php
session_start(); // Start the session

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "class roster"; // Fixed space issue in database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form data is set
if (isset($_POST['SVVNetID']) && isset($_POST['password'])) {
    $SVVNetID = trim($_POST['SVVNetID']);
    $password = $_POST['password'];

    // Get user from signupdetails table
    $stmt = $conn->prepare("SELECT SVVNetID, password FROM signupdetails WHERE SVVNetID = ?");
    $stmt->bind_param("s", $SVVNetID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['loggedin'] = true;
            $_SESSION['SVVNetID'] = $SVVNetID;
            
            // Redirect to dashboard/home page
            echo "Login successful! Redirecting...";
            header("refresh:2; url=index.html");
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "User not found!";
    }

    $stmt->close();
} else {
    echo "Form data not submitted properly.";
}

$conn->close();
?>
