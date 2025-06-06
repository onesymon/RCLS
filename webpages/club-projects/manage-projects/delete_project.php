<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/logout.php");
    exit();
}

// Block members from accessing officer-only pages
if ($_SESSION['role'] !== '1'&& $_SESSION['role'] !== '3'&& $_SESSION['role'] !== '100' ) {
    header("Location: /rotary/dashboard.php"); // or show a 403 error page
    exit();
  }
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $delete_id = $_GET['id'] ?? null;

    // Make sure the id is valid
    if ($delete_id) {
        // Modify the delete query to target the club_projects table
        $deleteQuery = "DELETE FROM club_projects WHERE id = $delete_id";

        if ($conn->query($deleteQuery) === TRUE) {
            // Redirect to the previous page if deletion was successful
            header("Location: ".$_SERVER['HTTP_REFERER']);
            exit();
        } else {
            // Show error message if the deletion failed
            echo "Error deleting project: " . $conn->error;
            exit();
        }
    } else {
        // Handle the case where no valid id is provided
        echo "Invalid project ID.";
        exit();
    }
} else {
    // Redirect to the previous page if the request method isn't GET
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}
?>
