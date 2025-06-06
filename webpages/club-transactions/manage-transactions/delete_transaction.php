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

            // Step 3: If it's a Club Wallet, delete related wallet transaction and update balance
            if ($category === 'Club Wallet') {
                // Delete the related club_wallet_transaction
                $walletDeleteStmt = $conn->prepare("DELETE FROM club_wallet_transactions WHERE transaction_id = ?");
                $walletDeleteStmt->bind_param("i", $delete_id);
                $walletDeleteStmt->execute();
                $walletDeleteStmt->close();

                // Get fund_id to recalculate balance
                $fundIdStmt = $conn->prepare("
                    SELECT fund_id FROM club_wallet_transactions WHERE transaction_id = ?
                ");
                $fundIdStmt->bind_param("i", $delete_id);
                $fundIdStmt->execute();
                $fundIdStmt->bind_result($fund_id);
                
                if ($fundIdStmt->fetch()) {
                    $fundIdStmt->close();

                    // Recalculate current_balance
                    $recalcStmt = $conn->prepare("
                        UPDATE club_wallet_categories
                        SET current_balance = (
                            SELECT COALESCE(SUM(
                                CASE 
                                    WHEN transaction_type = 'deposit' THEN amount 
                                    WHEN transaction_type = 'withdrawal' THEN -amount 
                                    ELSE 0 END
                            ), 0)
                            FROM club_wallet_transactions
                            WHERE fund_id = ?
                        )
                        WHERE id = ?
                    ");
                    $recalcStmt->bind_param("ii", $fund_id, $fund_id);
                    $recalcStmt->execute();
                    $recalcStmt->close();
                } else {
                    $fundIdStmt->close(); // still close if nothing found
                }
            }

            // Final redirect
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
