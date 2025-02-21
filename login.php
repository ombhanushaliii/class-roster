<?php
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
    $SVVNetID = $_POST['SVVNetID'];
    $password = $_POST['password']; 

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO logindetails (`SVVNetID`, `password`) VALUES (?, ?)");
    $stmt->bind_param("ss", $SVVNetID, $password);

    if ($stmt->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Form data not submitted properly.";
}

$conn->close();
?>
