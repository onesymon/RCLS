<?php
// Include the configuration to start the session
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

// Start the session if it isn't started yet
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the session is started and the user is logged in
if (isset($_SESSION['user_id'])) {
    echo "Session started!<br>";
    echo "User is logged in with ID: " . $_SESSION['user_id'] . "<br>";
    echo "Email: " . $_SESSION['email'] . "<br>";
} else {
    echo "No session found! You are not logged in.<br>";
}

// Display a logout link
echo '<br><a href="/rotary/webpages/logout/logout.php">Click here to log out</a>';

?>
