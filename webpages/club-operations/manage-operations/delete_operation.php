<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if ($_SESSION['role'] !== '1'&& $_SESSION['role'] !== '3' && $_SESSION['role'] !== '100' ) {
    header("Location: /rotary/dashboard.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $delete_id = $_GET['id'] ?? null;

    if ($delete_id) {
        $stmt = $conn->prepare("DELETE FROM club_operations WHERE id = ?");
        $stmt->bind_param("i", $delete_id);

        if ($stmt->execute()) {
            header("Location: /rotary/webpages/club-operations/manage-operations/manage_operations.php?msg=Deleted+Successfully");
            exit();
        } else {
            echo "Error deleting operation: " . $stmt->error;
            exit();
        }
    } else {
        header("Location: /rotary/webpages/club-operations/manage-operations/manage_operations.php");
        exit();
    }
}
?>
