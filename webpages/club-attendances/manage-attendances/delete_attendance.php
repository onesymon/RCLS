<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $attendanceId = intval($_GET['id']); // sanitize input

    $deleteAttendanceQuery = "DELETE FROM club_attendances WHERE id = $attendanceId";
    if ($conn->query($deleteAttendanceQuery) === TRUE) {
        header("Location: manage_attendances.php"); // adjust to your actual attendance list page
        exit();
    } else {
        echo "Error deleting attendance record: " . $conn->error;
    }
} else {
    header("Location: manage_attendances.php");
    exit();
}

$conn->close();
?>
