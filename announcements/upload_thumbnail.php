<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $type = $_POST['type'] === 'Project' ? 'club_projects' : 'club_events';

    if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK) {
        header("Location: /rotary/announcements/announcements.php?uploaded=0");
        exit();
    }

    $fileTmp = $_FILES['thumbnail']['tmp_name'];
    $fileName = $_FILES['thumbnail']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExts = ['jpg', 'jpeg', 'png'];
    if (!in_array($fileExt, $allowedExts)) {
        header("Location: /rotary/announcements/announcements.php?uploaded=0");
        exit();
    }

    $folder = $type === 'club_projects' ? 'club_projects' : 'club_events';
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/rotary/uploads/$folder/";
    $savePath = $uploadDir . $id . ".jpg"; // Force .jpg extension

    // If the file is PNG, still rename it as JPG for consistency
    if (!move_uploaded_file($fileTmp, $savePath)) {
        header("Location: /rotary/announcements/announcements.php?uploaded=0");
        exit();
    }

    header("Location: /rotary/announcements/announcements.php?uploaded=1");
    exit();
} else {
    header("Location: /rotary/announcements/announcements.php?uploaded=0");
    exit();
}
?>
