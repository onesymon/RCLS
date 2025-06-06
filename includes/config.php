<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rotary";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . ".<br> Please create a database and import the SQL file");
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
