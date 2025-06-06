<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
  header("Location: /rotary/webpages/logout/logout.php");
  exit();
}

// Counter functions
function getTotalMembersCount()
{
    global $conn;
    $query = "SELECT COUNT(*) AS total FROM members";
    $result = $conn->query($query);
    return $result->fetch_assoc()['total'] ?? 0;
}

function getPendingTransactionsCount()
{
    global $conn;
    $query = "SELECT COUNT(*) AS total FROM club_transactions WHERE payment_status = 'Pending'";
    $result = $conn->query($query);
    return $result->fetch_assoc()['total'] ?? 0;
}

function getExpiringSoonCount()
{
    global $conn;
    $query = "SELECT COUNT(*) AS total FROM members WHERE expiry_date BETWEEN CURDATE() AND CURDATE() + INTERVAL 7 DAY";
    $result = $conn->query($query);
    return $result->fetch_assoc()['total'] ?? 0;
}

function getTotalRevenueWithCurrency()
{
    global $conn;
    $currency = '$';
    $currencyQuery = "SELECT currency FROM settings LIMIT 1";
    $currencyResult = $conn->query($currencyQuery);
    if ($currencyResult->num_rows > 0) {
        $currency = $currencyResult->fetch_assoc()['currency'];
    }

    $revenueQuery = "SELECT SUM(amount) AS total FROM club_transactions WHERE payment_status = 'Paid'";
    $revenueResult = $conn->query($revenueQuery);
    $total = $revenueResult->fetch_assoc()['total'] ?? 0;

    return $currency . number_format($total, 2);
}

function getFinishedProjectsThisMonth()
{
    global $conn;
    $query = "SELECT COUNT(*) AS finishedCount FROM club_projects WHERE status = 'completed' AND MONTH(end_date) = MONTH(CURRENT_DATE()) AND YEAR(end_date) = YEAR(CURRENT_DATE())";
    $result = $conn->query($query);
    return $result->fetch_assoc()['finishedCount'] ?? 0;
}

$fetchLogoQuery = "SELECT logo FROM settings WHERE id = 1";
$fetchLogoResult = $conn->query($fetchLogoQuery);
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

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
  const incomeData = [5000, 7000, 8000, 6000, 7500, 9000];
  const expenseData = [3000, 4000, 3500, 5000, 4200, 6000];
  const categories = ['Club Project', 'Club Event', 'Club Operation', 'Club Fund'];
  const categoryAmounts = [12000, 8000, 5000, 3000];
  const methods = ['Cash', 'Bank Transfer', 'Gcash', 'Maya', 'System'];
  const methodCounts = [10, 8, 12, 5, 6];
  const members = ['JV Lim', 'Chano', 'Lescanos', 'Recto'];
  const contributions = [2000, 1800, 1500, 1200];
  const wallets = ['Club Donations', 'Club Memberships'];
  const walletBalances = [1500, 20000];
  const projectTitles = ['Tree Planting', 'Feeding', 'Cleanup'];
  const projectProgress = [60, 40, 80];
  const attendanceMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
  const attendanceCounts = [30, 28, 25, 35, 32, 38];

  new Chart(document.getElementById('incomeExpenseChart'), {
    type: 'bar',
    data: {
      labels: months,
      datasets: [
        { label: 'Income', data: incomeData, backgroundColor: '#28a745' },
        { label: 'Expense', data: expenseData, backgroundColor: '#dc3545' }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'top' } },
      scales: { y: { beginAtZero: true } }
    }
  });

  new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
      labels: categories,
      datasets: [{
        data: categoryAmounts,
        backgroundColor: ['#007bff', '#ffc107', '#6c757d', '#17a2b8']
      }]
    }
  });

  new Chart(document.getElementById('paymentMethodChart'), {
    type: 'pie',
    data: {
      labels: methods,
      datasets: [{
        data: methodCounts,
        backgroundColor: ['#28a745', '#007bff', '#ffc107', '#fd7e14', '#6f42c1']
      }]
    }
  });

  new Chart(document.getElementById('contributionsChart'), {
    type: 'bar',
    data: {
      labels: members,
      datasets: [{
        label: '₱ Contributions',
        data: contributions,
        backgroundColor: '#17a2b8'
      }]
    },
    options: {
      scales: { y: { beginAtZero: true } }
    }
  });

  new Chart(document.getElementById('walletChart'), {
    type: 'bar',
    data: {
      labels: wallets,
      datasets: [{
        label: '₱ Balance',
        data: walletBalances,
        backgroundColor: '#343a40'
      }]
    },
    options: {
      scales: { y: { beginAtZero: true } }
    }
  });

  new Chart(document.getElementById('projectFundingChart'), {
    type: 'bar',
    data: {
      labels: projectTitles,
      datasets: [{
        label: '% Funded',
        data: projectProgress,
        backgroundColor: '#6f42c1'
      }]
    },
    options: {
      scales: { y: { beginAtZero: true, max: 100 } }
    }
  });

  new Chart(document.getElementById('attendanceChart'), {
    type: 'line',
    data: {
      labels: attendanceMonths,
      datasets: [{
        label: 'Attendance',
        data: attendanceCounts,
        borderColor: '#007bff',
        backgroundColor: 'rgba(0, 123, 255, 0.2)',
        fill: true,
        tension: 0.4
      }]
    }
  });
});
</script>

</body>
</html>
