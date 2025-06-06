<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');
session_start();

// Role verification
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== '1' && $_SESSION['role'] !== '100')) {
    header("Location: /rotary/dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $delete_id = isset($_GET['id']) ? intval($_GET['id']) : null;

    if ($delete_id) {
        // Check if the wallet exists first
        $check = $conn->prepare("SELECT id FROM club_wallet_categories WHERE id = ?");
        $check->bind_param("i", $delete_id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            header("Location: /rotary/webpages/club-wallets/manage-wallets/manage_wallets.php?msg=Wallet+Not+Found");
            exit();
        }

        // Proceed with deletion
        $stmt = $conn->prepare("DELETE FROM club_wallet_categories WHERE id = ?");
        $stmt->bind_param("i", $delete_id);

        if ($stmt->execute()) {
            header("Location: /rotary/webpages/club-wallets/manage-wallets/manage_wallets.php?msg=Deleted+Successfully");
        } else {
            header("Location: /rotary/webpages/club-wallets/manage-wallets/manage_wallets.php?msg=Error+Deleting+Wallet");
        }

        exit();
    } else {
        header("Location: /rotary/webpages/club-wallets/manage_wallets.php?msg=Invalid+ID");
        exit();
    }
} else {
    header("Location: /rotary/webpages/club-wallets/manage_wallets.php");
    exit();
}
?>
