<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/logout.php");
    exit();
}
if ($_SESSION['role'] !== '1'&& $_SESSION['role'] !== '3'&& $_SESSION['role'] !== '100') {
    header("Location: /rotary/dashboard.php"); // or show a 403 error page
    exit();
  }
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $memberId = $_GET['id'];

    $checkRenewQuery = "SELECT * FROM renew WHERE member_id = $memberId";
    $checkRenewResult = $conn->query($checkRenewQuery);

    if ($checkRenewResult->num_rows > 0) {
        $deleteRenewQuery = "DELETE FROM renew WHERE member_id = $memberId";
        if ($conn->query($deleteRenewQuery) === FALSE) {
            echo "Error deleting related renew records: " . $conn->error;
            exit();
        }
    }

    $deleteMemberQuery = "DELETE FROM members WHERE id = $memberId";

    if ($conn->query($deleteMemberQuery) === TRUE) {
        header("Location: manage_members.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    header("Location: manage_members.php");
    exit();
}

$conn->close();
?>