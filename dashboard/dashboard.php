<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
  header("Location: /rotary/webpages/logout/logout.php");
  exit();
}

// COUNTERS
function getTotalMembersCount() {
  global $conn;
  $result = $conn->query("SELECT COUNT(*) AS total FROM members");
  return $result->fetch_assoc()['total'] ?? 0;
}

function getPendingTransactionsCount() {
  global $conn;
  $result = $conn->query("SELECT COUNT(*) AS total FROM club_transactions WHERE payment_status = 'Pending'");
  return $result->fetch_assoc()['total'] ?? 0;
}

function getExpiringSoonCount() {
  global $conn;
  $result = $conn->query("SELECT COUNT(*) AS total FROM members WHERE expiry_date BETWEEN CURDATE() AND CURDATE() + INTERVAL 7 DAY");
  return $result->fetch_assoc()['total'] ?? 0;
}

function getTotalRevenueWithCurrency() {
  global $conn;
  $currency = '₱';
  $res = $conn->query("SELECT currency FROM settings LIMIT 1");
  if ($res->num_rows > 0) $currency = $res->fetch_assoc()['currency'];
  $total = $conn->query("SELECT SUM(amount) AS total FROM club_transactions WHERE payment_status = 'Paid'")->fetch_assoc()['total'] ?? 0;
  return $currency . number_format($total, 2);
}

// CHART DATA
$monthlyData = [];
for ($i = 1; $i <= 6; $i++) {
  $m = date("M", mktime(0, 0, 0, $i, 10));
  $income = $conn->query("SELECT SUM(amount) AS total FROM club_transactions WHERE entry_type='Income' AND MONTH(transaction_date)=$i AND payment_status='Paid'")->fetch_assoc()['total'] ?? 0;
  $expense = $conn->query("SELECT SUM(amount) AS total FROM club_transactions WHERE entry_type='Expense' AND MONTH(transaction_date)=$i AND payment_status='Paid'")->fetch_assoc()['total'] ?? 0;
  $monthlyData[] = ['month' => $m, 'income' => (float)$income, 'expense' => (float)$expense];
}

$categoryLabels = []; $categoryData = [];
$res = $conn->query("SELECT category, SUM(amount) AS total FROM club_transactions WHERE payment_status='Paid' GROUP BY category");
while ($row = $res->fetch_assoc()) {
  $categoryLabels[] = $row['category'];
  $categoryData[] = (float)$row['total'];
}

$methodLabels = []; $methodCounts = [];
$res = $conn->query("SELECT pm.method_name, COUNT(*) AS total FROM club_transactions ct JOIN payment_method pm ON ct.payment_method = pm.id WHERE payment_status='Paid' GROUP BY method_name");
while ($row = $res->fetch_assoc()) {
  $methodLabels[] = $row['method_name'];
  $methodCounts[] = (int)$row['total'];
}

$memberLabels = []; $memberAmounts = [];
$res = $conn->query("SELECT m.fullname, SUM(ct.amount) AS total FROM club_transactions ct JOIN members m ON ct.member_id = m.id WHERE ct.entry_type='Contribution' AND ct.payment_status='Paid' GROUP BY ct.member_id ORDER BY total DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
  $memberLabels[] = $row['fullname'];
  $memberAmounts[] = (float)$row['total'];
}

$walletLabels = []; $walletAmounts = [];
$res = $conn->query("SELECT fund_name, current_balance FROM club_wallet_categories WHERE status='Active'");
while ($row = $res->fetch_assoc()) {
  $walletLabels[] = $row['fund_name'];
  $walletAmounts[] = (float)$row['current_balance'];
}

$projectLabels = []; $projectProgress = [];
$res = $conn->query("SELECT title, target_funding, current_funding FROM club_projects WHERE target_funding > 0");
while ($row = $res->fetch_assoc()) {
  $projectLabels[] = $row['title'];
  $progress = ($row['current_funding'] / $row['target_funding']) * 100;
  $projectProgress[] = round($progress, 2);
}

$attendMonths = []; $attendCounts = [];
for ($i = 5; $i >= 0; $i--) {
  $label = date("M", strtotime("-$i month"));
  $attendMonths[] = $label;
  $month = date("m", strtotime("-$i month"));
  $year = date("Y", strtotime("-$i month"));
  $count = $conn->query("SELECT COUNT(*) AS total FROM club_attendances WHERE status='Present' AND MONTH(attendance_date)=$month AND YEAR(attendance_date)=$year")->fetch_assoc()['total'] ?? 0;
  $attendCounts[] = (int)$count;
}

$fetchLogoResult = $conn->query("SELECT logo FROM settings WHERE id = 1");
$logoPath = ($fetchLogoResult->num_rows > 0) ? $fetchLogoResult->fetch_assoc()['logo'] : '/rotary/dist/img/default-logo.png';
?>

<?php include($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/header.php'); ?>
<link rel="stylesheet" href="/rotary/dashboard/style.css?v=<?php echo time(); ?>">

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/nav.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/sidebar.php'); ?>

  <div class="content-wrapper">
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/page_title.php'); ?>

    <section class="content">
      <div class="container-fluid">
        <!-- Dashboard Widgets -->
        <div class="row g-3">
          <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="small-box bg-primary text-white">
              <div class="inner">
                <h4><?php echo getTotalMembersCount(); ?></h4>
                <p>Total Members</p>
              </div>
              <div class="icon"><i class="fas fa-users"></i></div>
            </div>
          </div>

          <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="small-box bg-warning text-white">
              <div class="inner">
                <h4><?php echo getExpiringSoonCount(); ?></h4>
                <p>Delinquent Members</p>
              </div>
              <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
          </div>

          <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="small-box bg-success text-white">
              <div class="inner">
                <h4><?php echo getTotalRevenueWithCurrency(); ?></h4>
                <p>Total Fund</p>
              </div>
              <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
          </div>

          <?php if ($_SESSION['role'] == 'officer'): ?>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="small-box bg-danger text-white">
              <div class="inner">
                <h4><?php echo getPendingTransactionsCount(); ?></h4>
                <p>Pending Transactions</p>
              </div>
              <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
            </div>
          </div>
          <?php endif; ?>

          <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm border-0">
              <div class="card-header bg-info text-white text-center rounded-top">
                <h5>Finished Projects (This Month)</h5>
              </div>
              <div class="card-body bg-light">
                <div class="row">
                  <div class="col-4 text-center">
                    <img src="/rotary/dist/img/project1.jpg" class="img-fluid rounded" alt="Project 1">
                  </div>
                  <div class="col-4 text-center">
                    <img src="/rotary/dist/img/project2.jpg" class="img-fluid rounded" alt="Project 2">
                  </div>
                  <div class="col-4 text-center">
                    <img src="/rotary/dist/img/project3.jpg" class="img-fluid rounded" alt="Project 3">
                  </div>
                </div>
                <div class="text-center mt-3">
                  <a href="/rotary/webpages/club-projects/manage-projects/manage_projects.php" class="btn btn-sm btn-outline-info">View All</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Achievements -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header bg-gradient-primary text-white">
                <h4 class="mb-0">🏆 Rotary Achievements</h4>
              </div>
              <div class="card-body bg-white">
                <div class="row text-center">
                  <div class="col-md-4">
                    <i class="fas fa-user-tie fa-2x text-primary"></i>
                    <h6 class="mt-2 font-weight-bold">Best Rotarian</h6>
                    <p>John Doe</p>
                  </div>
                  <div class="col-md-4">
                    <i class="fas fa-hand-holding-usd fa-2x text-success"></i>
                    <h6 class="mt-2 font-weight-bold">Most Donations</h6>
                    <p>Mary Smith</p>
                  </div>
                  <div class="col-md-4">
                    <i class="fas fa-handshake fa-2x text-warning"></i>
                    <h6 class="mt-2 font-weight-bold">Most Meetings Attended</h6>
                    <p>Robert Johnson</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Data Analytics & Financial Reports -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header bg-gradient-primary text-white">
                <h4 class="mb-0">📊 Financial Reports & Data Analytics</h4>
              </div>
              <div class="card-body bg-white">
                <div class="row">

                  <!-- Income vs Expense -->
                  <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100">
                      <div class="card-header bg-info text-white text-center">Monthly Income vs Expense</div>
                      <div class="card-body"><canvas id="incomeExpenseChart"></canvas></div>
                    </div>
                  </div>

                  <!-- Category Distribution -->
                  <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100">
                      <div class="card-header bg-warning text-white text-center">Transaction Category Distribution</div>
                      <div class="card-body"><canvas id="categoryChart"></canvas></div>
                    </div>
                  </div>

                  <!-- Payment Methods -->
                  <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100">
                      <div class="card-header bg-success text-white text-center">Payment Method Usage</div>
                      <div class="card-body"><canvas id="paymentMethodChart"></canvas></div>
                    </div>
                  </div>

                  <!-- Member Contributions -->
                  <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100">
                      <div class="card-header bg-primary text-white text-center">Top Member Contributions</div>
                      <div class="card-body"><canvas id="contributionsChart"></canvas></div>
                    </div>
                  </div>

                  <!-- Wallet Balances -->
                  <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100">
                      <div class="card-header bg-dark text-white text-center">Club Wallet Balances</div>
                      <div class="card-body"><canvas id="walletChart"></canvas></div>
                    </div>
                  </div>

                  <!-- Project Funding Progress -->
                  <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100">
                      <div class="card-header bg-secondary text-white text-center">Project Funding Progress</div>
                      <div class="card-body"><canvas id="projectFundingChart"></canvas></div>
                    </div>
                  </div>

                  <!-- Attendance Trend -->
                  <div class="col-xl-12 col-md-12 mb-4">
                    <div class="card h-100">
                      <div class="card-header bg-indigo text-white text-center">Monthly Attendance Trend</div>
                      <div class="card-body"><canvas id="attendanceChart"></canvas></div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
  </div>

  <footer class="main-footer">
    <div class="float-right d-none d-sm-inline-block">
      <b>Developed By</b> <a href="#">Group 9</a>
    </div>
  </footer>
</div>

<?php include($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/footer.php'); ?>

<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const months = <?= json_encode(array_column($monthlyData, 'month')) ?>;
  const incomeData = <?= json_encode(array_column($monthlyData, 'income')) ?>;
  const expenseData = <?= json_encode(array_column($monthlyData, 'expense')) ?>;
  const categoryLabels = <?= json_encode($categoryLabels) ?>;
  const categoryData = <?= json_encode($categoryData) ?>;
  const methodLabels = <?= json_encode($methodLabels) ?>;
  const methodCounts = <?= json_encode($methodCounts) ?>;
  const memberLabels = <?= json_encode($memberLabels) ?>;
  const memberAmounts = <?= json_encode($memberAmounts) ?>;
  const walletLabels = <?= json_encode($walletLabels) ?>;
  const walletAmounts = <?= json_encode($walletAmounts) ?>;
  const projectLabels = <?= json_encode($projectLabels) ?>;
  const projectProgress = <?= json_encode($projectProgress) ?>;
  const attendanceMonths = <?= json_encode($attendMonths) ?>;
  const attendanceCounts = <?= json_encode($attendCounts) ?>;

  new Chart(incomeExpenseChart, { type: 'bar', data: { labels: months, datasets: [{ label: 'Income', data: incomeData, backgroundColor: '#28a745' }, { label: 'Expense', data: expenseData, backgroundColor: '#dc3545' }] }, options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } } });
  new Chart(categoryChart, { type: 'doughnut', data: { labels: categoryLabels, datasets: [{ data: categoryData, backgroundColor: ['#007bff', '#ffc107', '#6c757d', '#17a2b8'] }] } });
  new Chart(paymentMethodChart, { type: 'pie', data: { labels: methodLabels, datasets: [{ data: methodCounts, backgroundColor: ['#28a745', '#007bff', '#ffc107', '#fd7e14', '#6f42c1'] }] } });
  new Chart(contributionsChart, { type: 'bar', data: { labels: memberLabels, datasets: [{ label: '₱ Contributions', data: memberAmounts, backgroundColor: '#17a2b8' }] }, options: { scales: { y: { beginAtZero: true } } } });
  new Chart(walletChart, { type: 'bar', data: { labels: walletLabels, datasets: [{ label: '₱ Balance', data: walletAmounts, backgroundColor: '#343a40' }] }, options: { scales: { y: { beginAtZero: true } } } });
  new Chart(projectFundingChart, { type: 'bar', data: { labels: projectLabels, datasets: [{ label: '% Funded', data: projectProgress, backgroundColor: '#6f42c1' }] }, options: { scales: { y: { beginAtZero: true, max: 100 } } } });
  new Chart(attendanceChart, { type: 'line', data: { labels: attendanceMonths, datasets: [{ label: 'Attendance', data: attendanceCounts, borderColor: '#007bff', backgroundColor: 'rgba(0, 123, 255, 0.2)', fill: true, tension: 0.4 }] } });
});
</script>
</body>
</html>