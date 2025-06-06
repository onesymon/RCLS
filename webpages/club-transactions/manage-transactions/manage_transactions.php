<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

if (!in_array($_SESSION['role'], ['1', '4', '5', '100'])) {
    header("Location: /rotary/dashboard.php");
    exit();
}

// Handle update status actions
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'];

    if (!in_array($status, ['Paid', 'Rejected'])) {
        die("Invalid status.");
    }

    $stmt = $conn->prepare("UPDATE club_transactions SET payment_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
       if ($status === 'Paid') {
    $fetchQuery = "SELECT * FROM club_transactions WHERE id = ?";
    $fetchStmt = $conn->prepare($fetchQuery);
    $fetchStmt->bind_param("i", $id);
    $fetchStmt->execute();
    $result = $fetchStmt->get_result();

    if ($result && $result->num_rows > 0) {
        $tx = $result->fetch_assoc();

        if ($tx['category'] === 'Club Fund') {
            $fundWalletId = intval($tx['activity_id']);
            $amount = floatval($tx['amount']);
            $remarks = $tx['remarks'] ?? 'Auto-deducted upon approval';
            $referenceId = intval($tx['id']);
            $encodedBy = intval($tx['encoded_by']);
            $memberId = $tx['member_id'] !== null ? intval($tx['member_id']) : null;

            echo "<pre>";
            echo "Preparing to insert:\n";
            echo "Fund Wallet ID: $fundWalletId\n";
            echo "Amount: $amount\n";
            echo "Remarks: $remarks\n";
            echo "Reference ID: $referenceId\n";
            echo "Encoded By: $encodedBy\n";
            echo "Member ID: " . ($memberId ?? 'NULL') . "\n";
            echo "</pre>";

            // Check if already inserted
            $existsCheck = $conn->prepare("SELECT id FROM club_wallet_transactions WHERE reference_id = ?");
            $existsCheck->bind_param("i", $referenceId);
            $existsCheck->execute();
            $existsResult = $existsCheck->get_result();

            if ($existsResult->num_rows === 0) {
                $stmtInsert = $conn->prepare("
                    INSERT INTO club_wallet_transactions 
                    (fund_id, transaction_type, amount, remarks, member_id, reference_id, encoded_by) 
                    VALUES (?, 'withdrawal', ?, ?, ?, ?, ?)
                ");

                if ($memberId === null) {
                    $stmtInsert->bind_param("dsssii", $fundWalletId, $amount, $remarks, $null = NULL, $referenceId, $encodedBy);
                } else {
                    $stmtInsert->bind_param("dssdii", $fundWalletId, $amount, $remarks, $memberId, $referenceId, $encodedBy);
                }

                if (!$stmtInsert->execute()) {
                    echo "<p style='color:red'>Insert Error: " . $stmtInsert->error . "</p>";
                    error_log("Insert error: " . $stmtInsert->error);
                } else {
                    echo "<p style='color:green'>Insert successful!</p>";
                    error_log("Insert success for RefID $referenceId");
                }

                $stmtInsert->close();
            } else {
                echo "<p style='color:orange'>Already inserted for RefID $referenceId</p>";
                error_log("Already inserted for RefID $referenceId");
            }

            $existsCheck->close();
        } else {
            echo "<p style='color:gray'>Category is not Club Fund. No insert.</p>";
        }
    } else {
        echo "<p style='color:red'>No transaction found for ID $id</p>";
    }

    $fetchStmt->close();
}


        header("Location: manage_transactions.php?msg=Status+updated");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    exit();
}

// Get currency symbol
$currencySymbol = '₱';
$currencyQuery = "SELECT currency FROM settings WHERE id = 1";
$currencyResult = $conn->query($currencyQuery);
if ($currencyResult->num_rows > 0) {
    $currencySymbol = $currencyResult->fetch_assoc()['currency'] ?: '₱';
}

// Handle filtering
$filterStatus = $_GET['filter'] ?? 'All';
$allowedFilters = ['All', 'Pending', 'Paid', 'Rejected'];
if (!in_array($filterStatus, $allowedFilters)) {
    $filterStatus = 'Pending';
}

$query = "
    SELECT ct.*, 
           m.fullname AS member_name, 
           e.fullname AS encoded_by_name,
           pm.method_name AS payment_method_name
    FROM club_transactions ct
    LEFT JOIN members m ON ct.member_id = m.id
    LEFT JOIN members e ON CAST(ct.encoded_by AS UNSIGNED) = e.id
    LEFT JOIN payment_method pm ON ct.payment_method = pm.ID
";

if ($filterStatus !== 'All') {
    $query .= " WHERE ct.payment_status = ? ";
}

$query .= " ORDER BY ct.transaction_date DESC";

$stmt = $conn->prepare($query);

if ($filterStatus !== 'All') {
    $stmt->bind_param("s", $filterStatus);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<?php include('../../../includes/header.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
    <?php include('../../../includes/nav.php'); ?>
    <?php include('../../../includes/sidebar.php'); ?>

    <div class="content-wrapper">
        <?php include('../../../includes/page_title.php'); ?>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                            <h3 class="card-title mb-0">Club Transactions Table</h3>

                            <div class="mx-auto">
                                <form method="GET" class="form-inline">
                                    <label for="filter" class="mr-2 mb-0">Filter by Status:</label>
                                    <select name="filter" id="filter" class="form-control" onchange="this.form.submit()">
                                        <?php foreach ($allowedFilters as $status): ?>
                                            <option value="<?= $status ?>" <?= $filterStatus == $status ? 'selected' : '' ?>><?= $status ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>

                            <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '100'): ?>
                                <a href="/rotary/webpages/club-transactions/add-transaction/add_transaction.php" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Add Transaction
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <table id="transactionsTable" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>From</th>
                                    <th>Amount</th>
                                    <th>Entry Type</th>
                                    <th>Payment Method</th>
                                    <th>Category</th>
                                    <th>Activity</th>
                                    <th>Reference</th>
                                    <th>Remarks</th>
                                    <th>Date</th>
                                    <th>Encoded By</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $counter = 1;
                                while ($row = $result->fetch_assoc()) {
                                    $entryType = $row['entry_type'] ?? 'Income';
                                    $from = $row['member_name'] ?? $row['external_source'] ?? 'N/A';

                                    $activityTitle = '';
                                    switch ($row['category']) {
                                        case 'Club Project':
                                            $res = $conn->query("SELECT title FROM club_projects WHERE id = " . intval($row['activity_id']));
                                            if ($res && $res->num_rows > 0) $activityTitle = $res->fetch_assoc()['title'];
                                            break;
                                        case 'Club Event':
                                            $res = $conn->query("SELECT title FROM club_events WHERE id = " . intval($row['activity_id']));
                                            if ($res && $res->num_rows > 0) $activityTitle = $res->fetch_assoc()['title'];
                                            break;
                                        case 'Club Operation':
                                            $res = $conn->query("SELECT category FROM club_operations WHERE id = " . intval($row['activity_id']));
                                            if ($res && $res->num_rows > 0) $activityTitle = $res->fetch_assoc()['category'];
                                            break;
                                        case 'Club Fund':
                                            $res = $conn->query("SELECT fund_name FROM club_wallet_categories WHERE id = " . intval($row['activity_id']));
                                            if ($res && $res->num_rows > 0) $activityTitle = $res->fetch_assoc()['fund_name'];
                                            break;
                                    }

                                    $badgeColor = match($entryType) {
                                        'Income' => 'badge-success',
                                        'Expense' => 'badge-danger',
                                        'Contribution' => 'badge-primary',
                                        default => 'badge-secondary'
                                    };
                                    $amountColor = match($entryType) {
                                        'Income' => 'text-success',
                                        'Expense' => 'text-danger',
                                        'Contribution' => 'text-primary',
                                        default => 'text-muted'
                                    };
                                    $statusBadge = match($row['payment_status']) {
                                        'Pending' => 'badge-warning',
                                        'Paid' => 'badge-success',
                                        'Rejected' => 'badge-danger',
                                        default => 'badge-secondary'
                                    };

                                    echo "<tr>";
                                    echo "<td>{$counter}</td>";
                                    echo "<td>{$from}</td>";
                                    echo "<td><span class='{$amountColor}'>₱" . number_format($row['amount'], 2) . "</span></td>";
                                    echo "<td><span class='badge {$badgeColor}'>{$entryType}</span></td>";
                                    echo "<td>{$row['payment_method_name']}</td>";
                                    echo "<td>{$row['category']}</td>";
                                    echo "<td>{$activityTitle}</td>";
                                    echo "<td>{$row['reference_number']}</td>";
                                    echo "<td>{$row['remarks']}</td>";
                                    echo "<td>" . date("F j, Y | h:ia", strtotime($row['transaction_date'])) . "</td>";
                                    echo "<td>{$row['encoded_by_name']}</td>";
                                    echo "<td><span class='badge {$statusBadge}'>{$row['payment_status']}</span></td>";

                                    echo '<td>';
                                    if ($row['payment_status'] === 'Paid') {
                                        echo '<a href="view_receipt.php?id=' . $row['id'] . '" class="btn btn-info btn-sm" title="View Receipt"><i class="fas fa-receipt"></i></a> ';
                                    }

                                    if (in_array($_SESSION['role'], ['1', '4', '100']) && $row['payment_status'] === 'Pending') {
                                        echo '<button class="btn btn-success btn-sm btn-status" data-id="' . $row['id'] . '" data-status="Paid" title="Mark as Approve"><i class="fas fa-check"></i></button> ';
                                        echo '<button class="btn btn-warning btn-sm btn-status" data-id="' . $row['id'] . '" data-status="Rejected" title="Reject Payment"><i class="fas fa-times"></i></button> ';
                                    }

                                    if ($row['payment_status'] !== 'Pending') {
                                        echo '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $row['id'] . '" title="Delete"><i class="fas fa-trash"></i></button>';
                                    }

                                    echo '</td>';
                                    echo "</tr>";
                                    $counter++;
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include('../../../includes/footer.php'); ?>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="confirmModalBody">
        Are you sure you want to perform this action?
      </div>    
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" id="confirmActionBtn" class="btn btn-primary">Yes, Proceed</button>
      </div>
    </div>
  </div>
</div>

<script>
    let actionUrl = '';

    $(function () {
        $("#transactionsTable").DataTable({ responsive: true, autoWidth: false });

        $('.card-body').on('click', '.btn-status', function () {
            const id = $(this).data('id');
            const status = $(this).data('status');
            actionUrl = `manage_transactions.php?id=${id}&status=${encodeURIComponent(status)}`;
            $('#confirmModalLabel').text(`Confirm ${status}`);
            $('#confirmModalBody').html(`Are you sure you want to mark this transaction as <strong>${status}</strong>?`);
            $('#confirmModal').modal('show');
        });

        $('.card-body').on('click', '.btn-delete', function () {
            const id = $(this).data('id');
            actionUrl = `delete_transaction.php?id=${id}`;
            $('#confirmModalLabel').text("Delete Transaction");
            $('#confirmModalBody').html("Are you sure you want to <strong>delete</strong> this transaction?");
            $('#confirmModal').modal('show');
        });

        $('#confirmActionBtn').on('click', function () {
            if (actionUrl !== '') {
                window.location.href = actionUrl;
            }
        });
    });
</script>
</body>
</html>
