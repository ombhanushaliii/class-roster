<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "class_roster";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'signupdetails'");
if ($result->num_rows == 0) {
    die("Table 'signupdetails' does not exist");
}

// Get table structure
$result = $conn->query("DESCRIBE signupdetails");
if ($result->num_rows > 0) {
    echo "Table structure:\n";
    while($row = $result->fetch_assoc()) {
        echo "Column: ".$row['Field']." | Type: ".$row['Type']."\n";
    }
}

// Get sample data (first 5 rows)
$result = $conn->query("SELECT SVVNetID, LENGTH(password) as hash_length, LEFT(password, 20) as hash_prefix FROM signupdetails LIMIT 5");
if ($result->num_rows > 0) {
    echo "\nSample data:\n";
    while($row = $result->fetch_assoc()) {
        echo "SVVNetID: ".$row['SVVNetID']." | Hash length: ".$row['hash_length']." | Hash prefix: ".$row['hash_prefix']."\n";
    }
} else {
    echo "\nNo data found in signupdetails table";
}

$conn->close();
?>
