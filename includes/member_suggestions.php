<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (isset($_GET['query'])) {
    $search = strtolower(trim($_GET['query']));
    $stmt = $conn->prepare("SELECT id, fullname, membership_number FROM members WHERE LOWER(fullname) LIKE ? OR LOWER(membership_number) LIKE ? LIMIT 10");
    $like = "%$search%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $suggestions = [];

    while ($row = $result->fetch_assoc()) {
        $suggestions[] = [
            'id' => $row['id'],
            'fullname' => $row['fullname'],
            'membership_number' => $row['membership_number']
        ];
    }

    echo json_encode($suggestions);
}
?>
