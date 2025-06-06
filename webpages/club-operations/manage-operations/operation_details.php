<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!in_array($_SESSION['role'], ['1', '5', '100'])) {
    header("Location: /rotary/dashboard.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Operation not found.";
    exit();
}

$stmt = $conn->prepare("
    SELECT co.*, m.fullname AS encoded_by_name, s.currency
    FROM club_operations co
    LEFT JOIN members m ON co.encoded_by = m.id
    JOIN settings s ON s.id = 1
    WHERE co.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "Operation not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('../../../includes/header.php'); ?>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">

<?php include('../../../includes/nav.php'); ?>
<?php include('../../../includes/sidebar.php'); ?>

<div class="content-wrapper">
<?php include('../../../includes/page_title.php'); ?>

<section class="content">
<div class="container-fluid">

<!-- Main Card -->
<div class="card shadow-sm border border-primary">
  <div class="card-header text-center bg-primary text-white">
    <h3 class="mb-0 font-weight-bold">Rotary Club Operation Details</h3>
    <small class="text-light">Official Record of Club Operation</small>
  </div>

  <div class="card-body px-4 py-4" id="receipt-content">

    <!-- Top Row -->
    <div class="row mb-4">
      <div class="col-md-6">
        <p><i class="fas fa-hashtag text-primary mr-1"></i><strong>Operation ID:</strong> <?= htmlspecialchars($data['id']) ?></p>
        <p><i class="fas fa-calendar-day text-info mr-1"></i><strong>Payment Date:</strong> <?= date("F j, Y", strtotime($data['payment_date'])) ?></p>
      </div>
      <div class="col-md-6 text-md-right">
        <p><i class="fas fa-user-edit text-secondary mr-1"></i><strong>Encoded By:</strong> <?= htmlspecialchars($data['encoded_by_name']) ?></p>
        <p><i class="fas fa-tags text-warning mr-1"></i><strong>Category:</strong> <?= htmlspecialchars($data['category']) ?></p>
      </div>
    </div>

    <hr>

    <!-- Middle Row -->
    <div class="row mb-3">
      <div class="col-md-6">
        <p><i class="fas fa-money-bill text-success mr-1"></i><strong>Amount:</strong> 
          <span class="badge badge-success px-3 py-2"><?= htmlspecialchars($data['currency']) . ' ' . number_format($data['amount'], 2) ?></span>
        </p>
        <p><i class="fas fa-user-check text-primary mr-1"></i><strong>Paid To:</strong> <?= htmlspecialchars($data['paid_to']) ?></p>
      </div>
      <div class="col-md-6 text-md-right">
        <p>
          <i class="fas fa-info-circle text-dark mr-1"></i> 
          <strong>Status:</strong> 
          <span class="badge badge-<?= $data['status'] === 'Paid' ? 'success' : ($data['status'] === 'Pending' ? 'warning' : 'danger') ?> px-3 py-2">
            <?= htmlspecialchars($data['status']) ?>
          </span>
        </p>
        <p><i class="fas fa-sticky-note text-muted mr-1"></i><strong>Notes:</strong> 
          <?= $data['notes'] ? htmlspecialchars($data['notes']) : '<span class="text-muted">No additional notes.</span>' ?>
        </p>
      </div>
    </div>

  </div>

  <div class="card-footer text-center">
    <button class="btn btn-outline-primary mr-2" onclick="downloadPDF()">
      <i class="fas fa-file-pdf"></i> Export to PDF
    </button>
    <button class="btn btn-outline-success mr-2" onclick="window.print()">
      <i class="fas fa-print"></i> Print
    </button>
    <a href="/rotary/webpages/club-operations/manage-operations/manage_operations.php" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left"></i> Go Back
    </a>
  </div>
</div>

</div>
</section>
</div>

<?php include('../../../includes/footer.php'); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadPDF() {
  const element = document.getElementById('receipt-content');
  const opt = {
    margin: 0.5,
    filename: 'operation_details_<?= htmlspecialchars($data['id']) ?>.pdf',
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
  };
  html2pdf().set(opt).from(element).save();
}
</script>

</body>
</html>
