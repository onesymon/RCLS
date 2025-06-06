<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'officer') {
    header("Location: /rotary/dashboard.php");
    exit();
}

$attendanceId = $_GET['id'] ?? null;
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $activity_id = $_POST['activity_id'];
    $attendance_date = $_POST['attendance_date'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    $updateQuery = "UPDATE club_attendances SET 
        category = ?, activity_id = ?, attendance_date = ?, status = ?, remarks = ? 
        WHERE id = ?";

    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sisssi", $category, $activity_id, $attendance_date, $status, $remarks, $attendanceId);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Attendance updated successfully.";
        header("Location: manage_attendance.php");
        exit();
    } else {
        $response['message'] = "Update failed: " . $conn->error;
    }
}

// Fetch data to pre-fill form
$attendanceData = [];
if ($attendanceId) {
    $result = $conn->query("SELECT * FROM club_attendances WHERE id = $attendanceId");
    if ($result->num_rows > 0) {
        $attendanceData = $result->fetch_assoc();
    } else {
        header("Location: manage_attendance.php");
        exit();
    }
}
?>
<!-- Your form HTML goes here to edit the attendance record -->
