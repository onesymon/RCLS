<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

header('Content-Type: application/json');

$allowedCategories = ['Club Project', 'Club Event', 'Club Operation', 'Club Wallet'];
$category = $_GET['category'] ?? '';

if (!in_array($category, $allowedCategories)) {
    echo json_encode([]);
    exit;
}

switch ($category) {
    case 'Club Project':
        $sql = "SELECT id, title, remaining_funding FROM club_projects WHERE status IN ('Upcoming', 'Ongoing') ORDER BY title ASC";
        break;

    case 'Club Event':
        $sql = "SELECT id, title, remaining_funding FROM club_events WHERE status IN ('Upcoming', 'Ongoing') ORDER BY title ASC";
        break;

    case 'Club Operation':
        $sql = "SELECT id, category AS title FROM club_operations WHERE status = 'Unpaid' ORDER BY category ASC";
        break;

    case 'Club Wallet':
        $sql = "SELECT id, fund_name AS title FROM club_wallet_categories WHERE status = 'Active' ORDER BY fund_name ASC";
        break;
}

$data = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $entry = ['id' => $row['id'], 'title' => $row['title']];
        
        // Include remaining_funding if available
        if (isset($row['remaining_funding'])) {
            $entry['remaining_funding'] = (float) $row['remaining_funding'];
        }

        $data[] = $entry;
    }
    $stmt->close();
}

echo json_encode($data);
