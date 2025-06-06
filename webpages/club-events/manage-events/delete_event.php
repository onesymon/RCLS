<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}
if ($_SESSION['role'] !== '1'&&$_SESSION['role']!=='3'&& $_SESSION['role'] !== '100') {
    header("Location: /rotary/dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $delete_id = $_GET['id'] ?? null;

    if ($delete_id) {
        $stmt = $conn->prepare("DELETE FROM club_events WHERE id = ?");
        $stmt->bind_param("i", $delete_id);

        if ($stmt->execute()) {
            header("Location: /rotary/webpages/club-events/manage-events/manage_events.php?msg=Deleted+Successfully");
            exit();
        } else {
            echo "Error deleting event: " . $stmt->error;
            exit();
        }
    } else {
        header("Location: /rotary/webpages/club-events/manage-events/manage_events.php");
        exit();
    }
}
?>
