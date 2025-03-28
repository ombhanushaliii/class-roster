<?php
session_start();
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "class_roster"; // Fixed space issue in database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch student ID from session
$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
    die("Unauthorized access");
}

// Check if a file was uploaded
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_pic"])) {
    $target_dir = "uploads/";
    $file_name = basename($_FILES["profile_pic"]["name"]);
    $file_size = $_FILES["profile_pic"]["size"];
    $file_tmp = $_FILES["profile_pic"]["tmp_name"];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_extensions = ["jpg", "jpeg", "png"];

    if (!in_array($file_ext, $allowed_extensions)) {
        echo "<p style='color:red;'>Invalid file type. Only JPG, JPEG, and PNG are allowed.</p>";
    } elseif ($file_size > 5 * 1024 * 1024) { // 5MB limit
        echo "<p style='color:red;'>File is too large. Maximum size is 5MB.</p>";
    } else {
        $new_file_name = "profile_" . $student_id . "." . $file_ext;
        $target_file = $target_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            // Update the database with the new file name
            $query = "UPDATE students SET profile_pic = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $new_file_name, $student_id);
            $stmt->execute();

            echo "<p style='color:green;'>Profile picture uploaded successfully!</p>";
        } else {
            echo "<p style='color:red;'>Error uploading file.</p>";
        }
    }
}

// Fetch existing profile picture from the database
$query = "SELECT profile_pic FROM students WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$profile_pic = $row['profile_pic'] ?? "default.png"; // Default image if none is uploaded
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h2>My Profile</h2>
    <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" width="150" height="150" style="border-radius: 50%;" alt="Profile Picture">
    
    <form action="" method="post" enctype="multipart/form-data">
        <label for="profile_pic">Add Your Photo (Max 5MB)</label>
        <input type="file" name="profile_pic" id="profile_pic" accept="image/png, image/jpeg">
        <button type="submit">Upload</button>
    </form>

</body>
</html>