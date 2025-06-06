<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['1', '100'])) {
    header("Location: /rotary/dashboard.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_roles.php");
    exit();
}

$roleId = intval($_GET['id']);

// Check if role is used by any member
$checkUsage = $conn->prepare("SELECT COUNT(*) FROM members WHERE role = ?");
$checkUsage->bind_param("i", $roleId);
$checkUsage->execute();
$checkUsage->bind_result($count);
$checkUsage->fetch();
$checkUsage->close();

if ($count > 0) {
    header("Location: manage_roles.php?deleted=0");
    exit();
}

$stmt = $conn->prepare("DELETE FROM club_position WHERE id = ?");
$stmt->bind_param("i", $roleId);
if ($stmt->execute()) {
    header("Location: manage_roles.php?deleted=1");
} else {
    header("Location: manage_roles.php?deleted=0");
}
exit();
