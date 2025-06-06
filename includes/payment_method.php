<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');
header('Content-Type: application/json');

$name = $_GET['name'] ?? '';
$stmt = $conn->prepare("SELECT id FROM payment_method WHERE method_name = ?");
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['id' => $row['id']]);
} else {
    echo json_encode(['id' => null]);
}
?>
