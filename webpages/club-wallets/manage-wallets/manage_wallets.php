<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

if (!in_array($_SESSION['role'], ['1', '3', '4', '5', '6', '100'])) {
    header("Location: /rotary/dashboard.php");
    exit();
}

// Fetch all fund wallets
$funds = [];

$fundQuery = "
    SELECT cf.*, m.fullname AS encoded_by_name 
    FROM club_wallet_categories cf 
    LEFT JOIN members m ON cf.encoded_by = m.id 
    ORDER BY cf.status DESC, cf.fund_name ASC
";
$fundResult = $conn->query($fundQuery);

while ($row = $fundResult->fetch_assoc()) {
    $funds[] = $row;
}

// Calculate overall balance from transactions
$balanceQuery = "
    SELECT 
        SUM(CASE 
            WHEN transaction_type = 'deposit' THEN amount 
            WHEN transaction_type = 'withdrawal' THEN -amount 
            ELSE 0 
        END) AS total 
    FROM club_wallet_transactions
";
$balanceResult = $conn->query($balanceQuery);
$total_balance = 0;
if ($balanceResult && $balanceRow = $balanceResult->fetch_assoc()) {
    $total_balance = floatval($balanceRow['total']);
}

// Summary calculations
$summary = [
    'total_funds' => count(array_filter($funds, fn($f) => $f['status'] === 'Active')),
    'active' => count(array_filter($funds, fn($f) => $f['status'] === 'Active')),
    'inactive' => count(array_filter($funds, fn($f) => $f['status'] === 'Inactive')),
    'total_balance' => $total_balance,
];

// Fetch transactions
$transactionQuery = "
    SELECT t.*, f.fund_name, 
           m.fullname AS encoded_by_name,
           c.fullname AS contributor_name
    FROM club_wallet_transactions t
    LEFT JOIN club_wallet_categories f ON t.fund_id = f.id
    LEFT JOIN members m ON t.encoded_by = m.id
    LEFT JOIN members c ON t.member_id = c.id
    ORDER BY t.transaction_date DESC
";
$transactions = $conn->query($transactionQuery);
?>

<?php include('../../../includes/header.php'); ?>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
    <?php include('../../../includes/nav.php'); ?>
    <?php include('../../../includes/sidebar.php'); ?>

    <div class="content-wrapper">
        <?php include('../../../includes/page_title.php'); ?>

        <section class="content">
            <div class="container-fluid">

                <!-- Summary Boxes -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="info-box bg-primary">
                            <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Wallets</span>
                                <span class="info-box-number"><?= $summary['total_funds'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Active</span>
                                <span class="info-box-number"><?= $summary['active'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Inactive</span>
                                <span class="info-box-number"><?= $summary['inactive'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Balance</span>
                                <span class="info-box-number">₱<?= number_format($summary['total_balance'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wallet Cards -->
                <div class="row">
                    <?php foreach ($funds as $fund): ?>
                        <div class="col-md-4">
                            <div class="card card-<?= $fund['status'] === 'Active' ? 'success' : 'secondary' ?>">
                                <div class="card-header">
                                    <h3 class="card-title"><?= htmlspecialchars($fund['fund_name']) ?></h3>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $fund_id = $fund['id'];
                                    $fundBalanceQuery = "
                                        SELECT 
                                            SUM(CASE 
                                                WHEN transaction_type = 'deposit' THEN amount 
                                                WHEN transaction_type = 'withdrawal' THEN -amount 
                                                ELSE 0 
                                            END) AS balance 
                                        FROM club_wallet_transactions
                                        WHERE fund_id = $fund_id
                                    ";
                                    $fundBalanceResult = $conn->query($fundBalanceQuery);
                                    $wallet_balance = 0;
                                    if ($fundBalanceResult && $row = $fundBalanceResult->fetch_assoc()) {
                                        $wallet_balance = floatval($row['balance']);
                                    }
                                    ?>
                                    <p><strong>Balance:</strong> ₱<?= number_format($wallet_balance, 2) ?></p>
                                    <p><strong>Status:</strong> <?= htmlspecialchars($fund['status']) ?></p>
                                    <p><strong>Owner:</strong> <?= htmlspecialchars($fund['owner'] ?: 'N/A') ?></p>
                                    <p><strong>Encoded By:</strong> <?= htmlspecialchars($fund['encoded_by_name'] ?? 'Unknown') ?></p>
                                    <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($fund['description'])) ?></p>
                                </div>
                                <div class="card-footer">
                                    <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm" onclick="confirmDeleteWallet(<?= $fund['id'] ?>)">Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Transaction Table -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Fund Transactions</h3>
                    </div>
                    <div class="card-body">
                        <table id="transactionsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fund</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Contributor</th>
                                    <th>Remarks</th>
                                    <th>Encoded By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $counter = 1;
                                while ($tx = $transactions->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td><?= htmlspecialchars($tx['fund_name'] ?? 'Unknown') ?></td>
                                        <td><span class="badge badge-<?= $tx['transaction_type'] === 'deposit' ? 'success' : 'danger' ?>">
                                            <?= ucfirst($tx['transaction_type']) ?>
                                        </span></td>
                                        <td>₱<?= number_format($tx['amount'], 2) ?></td>
                                        <td><?= date('M d, Y h:i A', strtotime($tx['transaction_date'])) ?></td>
                                        <td><?= htmlspecialchars($tx['contributor_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($tx['remarks']) ?></td>
                                        <td><?= htmlspecialchars($tx['encoded_by_name'] ?? 'Unknown') ?></td>
                                        <td>
                                            <a href="/rotary/webpages/club-transactions/manage-transactions/view_receipt.php?id=<?= $tx['id'] ?>" class="btn btn-info btn-sm" title="View Receipt" target="_blank">
                                                <i class="fas fa-receipt"></i> View Receipt
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteWalletModal" tabindex="-1" role="dialog" aria-labelledby="deleteWalletModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteWalletModalLabel"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this club wallet? This action <strong>cannot be undone</strong>.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <a href="#" class="btn btn-danger" id="confirmDeleteWalletBtn">Yes, Delete</a>
      </div>
    </div>
  </div>
</div>

<?php include('../../../includes/footer.php'); ?>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

<script>
    function confirmDeleteWallet(walletId) {
        const deleteUrl = '/rotary/webpages/club-wallets/manage-wallets/delete_wallet.php?id=' + walletId;
        $('#confirmDeleteWalletBtn').attr('href', deleteUrl);
        $('#deleteWalletModal').modal('show');
    }

    $(function () {
        $('#transactionsTable').DataTable({
            responsive: true,
            autoWidth: false,
            searching: true,
            order: [[4, 'desc']]
        });
    });
</script>
</body>
</html>
