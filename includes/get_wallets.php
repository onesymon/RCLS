<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

$wallets = [];
$query = "SELECT id, fund_name FROM club_wallet_categories ORDER BY fund_name ASC";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $wallets[] = $row;
}

header('Content-Type: application/json');
echo json_encode($wallets);
