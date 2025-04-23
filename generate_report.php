<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$SVVNetID = $_SESSION['SVVNetID'];

// Database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    // Get student details
    $stmt = $conn->prepare("SELECT * FROM user_details WHERE SVVNetID = ? AND user_type = 'student'");
    if (!$stmt) {
        throw new Exception("Failed to prepare student details query");
    }
    $stmt->bind_param("s", $SVVNetID);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$student) {
        throw new Exception("Student not found");
    }

    // Get student's grades
    $grades_stmt = $conn->prepare("SELECT subject_name, AVG(marks) as average_marks FROM student_grades WHERE student_id = ? GROUP BY subject_name ORDER BY subject_name");
    if (!$grades_stmt) {
        throw new Exception("Failed to prepare grades query");
    }
    $grades_stmt->bind_param("s", $SVVNetID);
    $grades_stmt->execute();
    $grades_result = $grades_stmt->get_result();
    $grades = [];
    while ($grade = $grades_result->fetch_assoc()) {
        $grades[] = $grade;
    }
    $grades_stmt->close();

    // Get attendance data
    $attendance_stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_classes,
            COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count
        FROM attendance 
        WHERE SVVNetID = ?");
    
    if (!$attendance_stmt) {
        throw new Exception("Failed to prepare attendance query");
    }
    
    $attendance_stmt->bind_param("s", $SVVNetID);
    $attendance_stmt->execute();
    $attendance_data = $attendance_stmt->get_result()->fetch_assoc();
    $attendance_stmt->close();

    // Set default values if no attendance records found
    if (!$attendance_data['total_classes']) {
        $attendance_data = [
            'total_classes' => 0,
            'present_count' => 0
        ];
    }

    $conn->close();

    // Calculate attendance percentage
    $attendance_percentage = $attendance_data['total_classes'] > 0 
        ? round(($attendance_data['present_count'] / $attendance_data['total_classes']) * 100) 
        : 0;

    // Calculate overall average
    $total_marks = 0;
    $subject_count = count($grades);
    foreach($grades as $grade) {
        $total_marks += $grade['average_marks'];
    }
    $overall_average = $subject_count > 0 ? round($total_marks / $subject_count, 2) : 0;

    // Generate HTML content
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { text-align: center; margin-bottom: 20px; }
            .logo { width: 100px; }
            .title { font-size: 24px; font-weight: bold; margin: 20px 0; }
            .section { margin: 20px 0; }
            .section-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
            table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
            th { background-color: #f5f5f5; }
            .signatures { margin-top: 50px; }
            .signature-line { width: 200px; border-top: 1px solid #000; display: inline-block; margin: 0 20px; text-align: center; }
            .date { text-align: right; margin-top: 30px; }
        </style>
    </head>
    <body>
        <div class="header">
            <img src="data:image/png;base64,' . base64_encode(file_get_contents('assets/Somaiya logo.png')) . '" class="logo">
            <div class="title">Student Report Card</div>
        </div>

        <div class="section">
            <div class="section-title">Student Information</div>
            <table>
                <tr><td><strong>Name:</strong></td><td>' . htmlspecialchars($student['full_name']) . '</td></tr>
                <tr><td><strong>Roll Number:</strong></td><td>' . htmlspecialchars($student['roll_number']) . '</td></tr>
                <tr><td><strong>Class:</strong></td><td>' . htmlspecialchars($student['class']) . '</td></tr>
                <tr><td><strong>Section:</strong></td><td>' . htmlspecialchars($student['section']) . '</td></tr>
                <tr><td><strong>Email:</strong></td><td>' . htmlspecialchars($student['email']) . '</td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Attendance Overview</div>
            <table>
                <tr><td><strong>Total Classes:</strong></td><td>' . $attendance_data['total_classes'] . '</td></tr>
                <tr><td><strong>Classes Attended:</strong></td><td>' . $attendance_data['present_count'] . '</td></tr>
                <tr><td><strong>Attendance Percentage:</strong></td><td>' . $attendance_percentage . '%</td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Academic Performance</div>
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Average Marks (%)</th>
                    </tr>
                </thead>
                <tbody>';
                foreach($grades as $grade) {
                    $html .= '<tr>
                        <td>' . htmlspecialchars($grade['subject_name']) . '</td>
                        <td>' . round($grade['average_marks'], 2) . '%</td>
                    </tr>';
                }
                $html .= '<tr>
                    <td><strong>Overall Average</strong></td>
                    <td><strong>' . $overall_average . '%</strong></td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="signatures">
            <div class="signature-line">Class Teacher\'s Signature</div>
            <div class="signature-line">Principal\'s Signature</div>
        </div>

        <div class="date">
            Generated on: ' . date('d-m-Y') . '
        </div>
    </body>
    </html>';

    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Student_Report_Card.pdf"');

    // Create PDF using html2pdf.js
    echo $html;

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
?>