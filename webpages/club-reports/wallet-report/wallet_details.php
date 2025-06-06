<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

$wallet_id = $_GET['id'] ?? null;
if (!$wallet_id) {
    echo "Invalid wallet ID.";
    exit();
}

// Fetch wallet details
$stmt = $conn->prepare("
    SELECT w.*, m.fullname AS encoded_by_name 
    FROM club_wallet_categories w 
    LEFT JOIN members m ON w.encoded_by = m.id 
    WHERE w.id = ?
");
$stmt->bind_param("i", $wallet_id);
$stmt->execute();
$wallet = $stmt->get_result()->fetch_assoc();

if (!$wallet) {
    echo "Wallet not found.";
    exit();
}

// Determine section title
$fund_name = strtolower($wallet['fund_name']);
if (str_contains($fund_name, 'membership')) {
    $title = 'Membership Status Overview';
} elseif (str_contains($fund_name, 'donation')) {
    $title = 'Donation Status Overview';
} elseif (str_contains($fund_name, 'sponsorship')) {
    $title = 'Sponsorship Report';
} else {
    $title = 'Contributor Status Overview';
}

// Fetch members
$memberQuery = $conn->query("SELECT m.*, p.position_name FROM members m LEFT JOIN club_position p ON m.role = p.id ORDER BY m.fullname ASC");

$members = [];
$index = 0;
while ($row = $memberQuery->fetch_assoc()) {
    // Every third member is Delinquent with 0 contribution
    if ($index % 3 == 0) {
        $row['status'] = 'Delinquent';
        $row['sample_amount'] = 0;
    } else {
        $row['status'] = 'Active';
        $row['sample_amount'] = rand(5000, 20000);
    }
    $members[] = $row;
    $index++;
}
?>

<?php include('../../../includes/header.php'); ?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
<?php include('../../../includes/nav.php'); ?>
<?php include('../../../includes/sidebar.php'); ?>

<div class="content-wrapper">
<section class="content-header">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <h1>Wallet Report: <?= htmlspecialchars($wallet['fund_name']) ?></h1>
    <a href="wallet_report.php" class="btn btn-sm btn-secondary">Back to Wallets</a>
  </div>
</section>

<section class="content">
  <div class="container-fluid">

    <!-- Wallet Info -->
    <div class="card mb-4">
      <div class="card-header bg-info text-white"><h3 class="card-title">Wallet Information</h3></div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <p><strong>Name:</strong> <?= htmlspecialchars($wallet['fund_name']) ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($wallet['description']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($wallet['status']) ?></p>
            <?php if (str_contains(strtolower($wallet['fund_name']), 'membership')): ?>
              <p><strong>Required Dues:</strong> ₱20,000</p>
            <?php endif; ?>
          </div>
          <div class="col-md-6">
            <p><strong>Currency:</strong> <?= htmlspecialchars($wallet['currency']) ?></p>
            <p><strong>Current Balance:</strong> ₱<?= number_format($wallet['current_balance'], 2) ?></p>
            <p><strong>Encoded By:</strong> <?= htmlspecialchars($wallet['encoded_by_name'] ?? 'Unknown') ?></p>
            <p><strong>Encoded At:</strong> <?= date('M d, Y | h:ia', strtotime($wallet['encoded_at'])) ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Member Overview -->
    <div class="card">
      <div class="card-header bg-warning">
        <h3 class="card-title"><?= $title ?></h3>
      </div>
      <div class="card-body">
        <table id="statusTable" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Full Name</th>
              <th>Role</th>
              <th>Contact</th>
              <th>Email</th>
              <th>Amount Contributed</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($members as $i => $m): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($m['fullname']) ?></td>
                <td><?= htmlspecialchars($m['position_name'] ?? 'Member') ?></td>
                <td><?= htmlspecialchars($m['contact_number']) ?></td>
                <td><?= htmlspecialchars($m['email']) ?></td>
                <td>₱<?= number_format($m['sample_amount'], 2) ?></td>
                <td>
                  <span class="badge badge-<?= $m['status'] === 'Active' ? 'success' : 'danger' ?>">
                    <?= $m['status'] ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</section>
</div>

<?php include('../../../includes/footer.php'); ?>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
  $(function () {
    $('#statusTable').DataTable({
      responsive: true,
      autoWidth: false,
      order: [[1, 'asc']]
    });
  });
</script>
</body>
</html>
