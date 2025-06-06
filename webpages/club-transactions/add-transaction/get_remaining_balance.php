<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');
header('Content-Type: application/json');

$category = $_GET['category'] ?? null;
$id = intval($_GET['id'] ?? 0);

if (!$category || !$id) {
  echo json_encode(['success' => false, 'message' => 'Invalid request.']);
  exit;
}

$table = $category === 'Club Project' ? 'club_projects' : ($category === 'Club Event' ? 'club_events' : null);

if (!$table) {
  echo json_encode(['success' => false, 'message' => 'Invalid category.']);
  exit;
}

$query = "SELECT remaining_funding FROM $table WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  echo json_encode(['success' => true, 'remaining_funding' => number_format($row['remaining_funding'], 2, '.', '')]);
} else {
  echo json_encode(['success' => false, 'message' => 'No matching record.']);
}
