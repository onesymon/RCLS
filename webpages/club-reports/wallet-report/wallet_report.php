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

$funds = [];
$total_balance = 0;

$fundResult = $conn->query("
    SELECT cf.*, m.fullname AS encoded_by_name 
    FROM club_wallet_categories cf 
    LEFT JOIN members m ON cf.encoded_by = m.id 
    ORDER BY cf.status DESC, cf.fund_name ASC
");

while ($row = $fundResult->fetch_assoc()) {
    $funds[] = $row;
    $total_balance += floatval($row['current_balance'] ?? 0);
}

$summary = [
    'total_funds' => count($funds),
    'active' => count(array_filter($funds, fn($f) => $f['status'] === 'Active')),
    'inactive' => count(array_filter($funds, fn($f) => $f['status'] === 'Inactive')),
    'total_balance' => $total_balance,
];
?>

<?php include('../../../includes/header.php'); ?>
<style>
.wallet-card:hover {
    box-shadow: 0 0 0.4rem rgba(0,0,0,0.15);
    transform: scale(1.01);
    transition: 0.2s ease-in-out;
}
.wallet-card .card-body {
    cursor: pointer;
}
</style>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
<?php include('../../../includes/nav.php'); ?>
<?php include('../../../includes/sidebar.php'); ?>

<div class="content-wrapper">
<?php include('../../../includes/page_title.php'); ?>

<section class="content">
  <div class="container-fluid">

    <!-- Summary -->
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
        <div class="col-md-4 mb-4">
          <div class="card wallet-card border-<?= $fund['status'] === 'Active' ? 'success' : 'secondary' ?>">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
              <h3 class="card-title mb-0 text-truncate">
                <i class="fas fa-piggy-bank mr-2"></i> <?= htmlspecialchars($fund['fund_name']) ?>
              </h3>
              <span class="badge badge-<?= $fund['status'] === 'Active' ? 'success' : 'secondary' ?>">
                <?= $fund['status'] ?>
              </span>
            </div>
            <a href="wallet_details.php?id=<?= $fund['id'] ?>" class="card-body text-dark text-decoration-none">
              <p class="mb-1"><strong>Balance:</strong> ₱<?= number_format($fund['current_balance'], 2) ?></p>
              <p class="mb-1"><strong>Owner:</strong> <?= htmlspecialchars($fund['owner'] ?: 'N/A') ?></p>
              <p class="mb-1"><strong>Encoded By:</strong> <?= htmlspecialchars($fund['encoded_by_name'] ?? 'Unknown') ?></p>
              <p class="mb-0"><strong>Description:</strong><br>
                <span title="<?= htmlspecialchars($fund['description']) ?>">
                  <?= nl2br(strlen($fund['description']) > 100 ? htmlspecialchars(substr($fund['description'], 0, 100)) . '...' : htmlspecialchars($fund['description'])) ?>
                </span>
              </p>
            </a>
            <div class="card-footer bg-white text-right">
              <a href="wallet_details.php?id=<?= $fund['id'] ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-eye"></i> View Details
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>
</div>

<?php include('../../../includes/footer.php'); ?>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
