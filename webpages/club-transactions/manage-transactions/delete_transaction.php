<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/logout.php");
    exit();
}
if ($_SESSION['role'] !== '1' && $_SESSION['role'] !== '4'&& $_SESSION['role'] !== '100' ) {
    header("Location: /rotary/dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $delete_id = $_GET['id'] ?? null;

    if ($delete_id) {
        $deleteQuery = "DELETE FROM club_transactions WHERE id = $delete_id";

        if ($conn->query($deleteQuery) === TRUE) {
            header("Location: ".$_SERVER['HTTP_REFERER']);
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
            exit();
        }
    } else {
        header("Location: ".$_SERVER['HTTP_REFERER']);
        exit();
    }
}
?>
