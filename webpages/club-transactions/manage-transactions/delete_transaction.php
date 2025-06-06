<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/logout.php");
    exit();
}
if (!in_array($_SESSION['role'], ['1', '4', '100'])) {
    header("Location: /rotary/dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $delete_id = $_GET['id'] ?? null;

    if ($delete_id) {
        // Step 1: Fetch info before deletion
        $fetchStmt = $conn->prepare("
            SELECT category, activity_id, amount, entry_type 
            FROM club_transactions 
            WHERE id = ?
        ");
        $fetchStmt->bind_param("i", $delete_id);
        $fetchStmt->execute();
        $fetchStmt->bind_result($category, $activity_id, $amount, $entry_type);

        if ($fetchStmt->fetch()) {
            $fetchStmt->close();

            // Step 2: Delete from club_transactions
            $deleteStmt = $conn->prepare("DELETE FROM club_transactions WHERE id = ?");
            $deleteStmt->bind_param("i", $delete_id);
            $deleteStmt->execute();
            $deleteStmt->close();

            // Step 3A: If Club Wallet, remove from wallet_transactions and recalc
            if ($category === 'Club Wallet' || $category === 'Club Fund') {
                // Delete wallet transaction
                $conn->query("DELETE FROM club_wallet_transactions WHERE reference_id = $delete_id");

                // Recalculate balance
                $conn->query("
                    UPDATE club_wallet_categories wc
                    SET wc.current_balance = (
                        SELECT COALESCE(SUM(
                            CASE 
                                WHEN transaction_type = 'deposit' THEN amount
                                WHEN transaction_type = 'withdrawal' THEN -amount
                                ELSE 0
                            END
                        ), 0)
                        FROM club_wallet_transactions
                        WHERE fund_id = wc.id
                    )
                    WHERE wc.id = $activity_id
                ");
            }

            // Step 3B: If Club Project → recalculate current_funding
            if ($category === 'Club Project') {
                $recalc = $conn->prepare("
                    SELECT SUM(amount) as total FROM club_transactions 
                    WHERE category = 'Club Project' AND activity_id = ? AND payment_status = 'Paid'
                ");
                $recalc->bind_param("i", $activity_id);
                $recalc->execute();
                $total = $recalc->get_result()->fetch_assoc()['total'] ?? 0;
                $recalc->close();

                // Get target
                $target = $conn->query("SELECT target_funding FROM club_projects WHERE id = $activity_id")->fetch_assoc()['target_funding'] ?? 0;
                $remaining = max(0, $target - $total);

                $update = $conn->prepare("UPDATE club_projects SET current_funding = ?, remaining_funding = ? WHERE id = ?");
                $update->bind_param("ddi", $total, $remaining, $activity_id);
                $update->execute();
                $update->close();
            }

            // Step 3C: If Club Event → recalculate current_funding
            if ($category === 'Club Event') {
                $recalc = $conn->prepare("
                    SELECT SUM(amount) as total FROM club_transactions 
                    WHERE category = 'Club Event' AND activity_id = ? AND payment_status = 'Paid'
                ");
                $recalc->bind_param("i", $activity_id);
                $recalc->execute();
                $total = $recalc->get_result()->fetch_assoc()['total'] ?? 0;
                $recalc->close();

                // Get target
                $target = $conn->query("SELECT target_funding FROM club_events WHERE id = $activity_id")->fetch_assoc()['target_funding'] ?? 0;
                $remaining = max(0, $target - $total);

                $update = $conn->prepare("UPDATE club_events SET current_funding = ?, remaining_funding = ? WHERE id = ?");
                $update->bind_param("ddi", $total, $remaining, $activity_id);
                $update->execute();
                $update->close();
            }

            // ✅ Done
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            $fetchStmt->close();
            echo "Transaction not found.";
            exit();
        }
    } else {
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}
?>
