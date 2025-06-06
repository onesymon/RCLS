<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

if ($_SESSION['role'] !== '1' && $_SESSION['role'] !== '3' && $_SESSION['role'] !== '100') {
    header("Location: /rotary/dashboard.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Project not found.";
    exit();
}

$query = "SELECT cp.*, m.fullname AS encoded_by_name FROM club_projects cp LEFT JOIN members m ON cp.encoded_by = m.id WHERE cp.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
if (!$data) {
    echo "Project not found.";
    exit();
}

$currencySymbol = '₱';
$currencyResult = $conn->query("SELECT currency FROM settings WHERE id = 1");
if ($currencyResult->num_rows > 0) {
    $currencySymbol = $currencyResult->fetch_assoc()['currency'];
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

<div class="card shadow-sm border border-primary mb-4">
  <div class="card-header text-center bg-primary text-white">
    <h3 class="mb-0"><?= htmlspecialchars($data['title']) ?></h3>
    <small class="text-light">Project Overview</small>
  </div>

  <div class="card-body p-4" id="project-content">

    <!-- Info Summary -->
    <div class="row mb-4">
      <div class="col-md-6">
        <p><i class="fas fa-project-diagram text-primary mr-2"></i><strong>Type:</strong> <?= ucfirst($data['type']) ?></p>
        <p><i class="fas fa-map-marker-alt text-danger mr-2"></i><strong>Location:</strong> <?= htmlspecialchars($data['location']) ?></p>
        <p><i class="fas fa-tasks text-info mr-2"></i><strong>Status:</strong> <?= ucfirst($data['status']) ?></p>
      </div>
      <div class="col-md-6 text-md-right">
        <p><i class="fas fa-calendar-plus text-success mr-2"></i><strong>Start Date:</strong> <?= date("F j, Y", strtotime($data['start_date'])) ?></p>
        <p><i class="fas fa-calendar-check text-warning mr-2"></i><strong>End Date:</strong> <?= $data['end_date'] ? date("F j, Y", strtotime($data['end_date'])) : 'Ongoing' ?></p>
        <p><i class="fas fa-user-edit text-secondary mr-2"></i><strong>Encoded By:</strong> <?= htmlspecialchars($data['encoded_by_name']) ?></p>
      </div>
    </div>

    <!-- Description -->
    <div class="mb-4">
      <p><i class="fas fa-align-left text-dark mr-2"></i><strong>Description:</strong></p>
      <p class="text-muted"><?= nl2br(htmlspecialchars($data['description'])) ?></p>
    </div>

    <!-- Budget Summary -->
    <div class="row text-center bg-light py-3 rounded mb-4">
      <div class="col-md-4">
        <h6 class="text-muted mb-1">Target</h6>
        <h5><?= $currencySymbol . number_format($data['target_funding'], 2) ?></h5>
      </div>
      <div class="col-md-4">
        <h6 class="text-muted mb-1">Current</h6>
        <h5><?= $currencySymbol . number_format($data['current_funding'], 2) ?></h5>
      </div>
      <div class="col-md-4">
        <h6 class="text-muted mb-1">Remaining</h6>
        <h5><?= $currencySymbol . number_format($data['remaining_funding'], 2) ?></h5>
      </div>
    </div>

    <!-- Contributors, Sources, Attendance, Financials -->
    <div class="row">

    <!-- Contributions -->
<div class="col-lg-6 mb-4">
  <div class="card h-100 border-info">
    <div class="card-header bg-info text-white">
      <strong><i class="fas fa-hand-holding-heart mr-1"></i>Contributions</strong>
    </div>
    <div class="card-body p-2 scrollable-section">
      <?php
      $stmt = $conn->prepare(" SELECT 
          ct.amount, 
          ct.transaction_date, 
          ct.remarks,
          m.fullname, 
          ct.external_source,
          cw.fund_name
        FROM club_transactions ct
        LEFT JOIN members m ON ct.member_id = m.id
        LEFT JOIN club_wallet_categories cw ON ct.category = 'Club Fund' 
        WHERE ct.activity_id = ? 
          AND ct.entry_type = 'Contribution'
          AND ct.payment_status = 'Paid'
        ORDER BY ct.transaction_date DESC
      ");
      if (!$stmt) {
          echo "<div class='alert alert-danger'>Prepare failed: " . $conn->error . "</div>";
      } else {
          $stmt->bind_param("i", $id);
          if (!$stmt->execute()) {
              echo "<div class='alert alert-danger'>Execute failed: " . $stmt->error . "</div>";
          } else {
              $res = $stmt->get_result();
              

              if ($res->num_rows > 0):
              ?>
                <ul class="list-group list-group-flush">
                <?php while ($r = $res->fetch_assoc()): ?>
                  <?php
                  $name = $r['fullname'] ?? null;
                  if (!$name) {
                      if (!empty($r['external_source'])) {
                          $name = 'External: ' . $r['external_source'];
                      } elseif (!empty($r['fund_name'])) {
                          $name = 'Fund: ' . $r['fund_name'];
                      } else {
                          $name = 'Unknown';
                      }
                  }
                  ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><?= htmlspecialchars($name) ?></span>
                    <span><?= $currencySymbol . number_format($r['amount'], 2) ?></span>
                  </li>
                <?php endwhile; ?>
                </ul>
              <?php else: ?>
                <p class="text-muted mb-0">No contributions recorded.</p>
              <?php endif;
          }
      }
      ?>
    </div>
  </div>
</div>


      <!-- Source of Funds -->
      <div class="col-lg-6 mb-4">
        <div class="card h-100 border-warning">
          <div class="card-header bg-warning text-dark">
            <strong><i class="fas fa-donate mr-1"></i>Source of Funds</strong>
          </div>
          <div class="card-body p-2 scrollable-section">
            <?php
            $stmt = $conn->prepare("SELECT DISTINCT IFNULL(cw.fund_name, CONCAT('External: ', ct.external_source)) AS source, ct.amount 
                                    FROM club_transactions ct 
                                    LEFT JOIN club_wallet_categories cw ON ct.activity_id = cw.id AND ct.category = 'Club Fund' 
                                    WHERE ct.project_id = ? AND ct.entry_type = 'Income'");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0):
            ?>
              <ul class="list-group list-group-flush">
              <?php while ($r = $res->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span><?= htmlspecialchars($r['source']) ?></span>
                  <span><?= $currencySymbol . number_format($r['amount'], 2) ?></span>
                </li>
              <?php endwhile; ?>
              </ul>
            <?php else: ?>
              <p class="text-muted mb-0">No sources found.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Attendance -->
      <div class="col-lg-6 mb-4">
        <div class="card h-100 border-success">
          <div class="card-header bg-success text-white">
            <strong><i class="fas fa-users mr-1"></i>Project Attendance</strong>
          </div>
          <div class="card-body p-2 scrollable-section">
            <?php
            $stmt = $conn->prepare("SELECT m.fullname, ca.attendance_date, ca.status 
                                    FROM club_attendances ca 
                                    JOIN members m ON ca.member_id = m.id 
                                    WHERE ca.category = 'Club Project' AND ca.activity_id = ?
                                    ORDER BY ca.attendance_date DESC");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0):
            ?>
              <ul class="list-group list-group-flush">
              <?php while ($r = $res->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span><?= htmlspecialchars($r['fullname']) ?></span>
                  <span class="text-muted"><?= $r['status'] ?></span>
                </li>
              <?php endwhile; ?>
              </ul>
            <?php else: ?>
              <p class="text-muted mb-0">No attendees recorded.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Financial Summary -->
      <div class="col-lg-6 mb-4">
        <div class="card h-100 border-dark">
          <div class="card-header bg-dark text-white">
            <strong><i class="fas fa-chart-line mr-1"></i>Financial Summary</strong>
          </div>
          <div class="card-body p-2">
            <?php
            $stmt = $conn->prepare("SELECT entry_type, SUM(amount) AS total FROM club_transactions WHERE project_id = ? GROUP BY entry_type");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $income = 0; $expense = 0; $contrib = 0;
            while ($row = $res->fetch_assoc()) {
                if ($row['entry_type'] === 'Income') $income = $row['total'];
                elseif ($row['entry_type'] === 'Expense') $expense = $row['total'];
                elseif ($row['entry_type'] === 'Contribution') $contrib = $row['total'];
            }
            $net = ($income + $contrib) - $expense;
            ?>
            <ul class="list-group">
              <li class="list-group-item d-flex justify-content-between"><span>Total Income</span><strong class="text-success"><?= $currencySymbol . number_format($income, 2) ?></strong></li>
              <li class="list-group-item d-flex justify-content-between"><span>Total Contributions</span><strong class="text-info"><?= $currencySymbol . number_format($contrib, 2) ?></strong></li>
              <li class="list-group-item d-flex justify-content-between"><span>Total Expenses</span><strong class="text-danger"><?= $currencySymbol . number_format($expense, 2) ?></strong></li>
              <li class="list-group-item d-flex justify-content-between bg-light"><strong>Net Balance</strong><strong><?= $currencySymbol . number_format($net, 2) ?></strong></li>
            </ul>
          </div>
        </div>
      </div>

    </div>

    <!-- Export / Back Buttons -->
    <div class="text-center mt-4">
      <button class="btn btn-outline-primary mr-2" onclick="downloadPDF()"><i class="fas fa-file-pdf"></i> Export PDF</button>
      <button class="btn btn-outline-success mr-2" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
      <a href="manage_projects.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

  </div>
</div>

</div>
</section>
</div>

<?php include('../../../includes/footer.php'); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadPDF() {
  const element = document.getElementById('project-content');
  const opt = {
    margin: 0.5,
    filename: 'project_details_<?= $data['id'] ?>.pdf',
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
  };
  html2pdf().set(opt).from(element).save();
}
</script>

<style>
.scrollable-section {
  max-height: 260px;
  overflow-y: auto;
}
</style>

</body>
</html>
