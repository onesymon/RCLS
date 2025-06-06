<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

// Count unseen logs
$notif_sql = "SELECT COUNT(*) AS unseen_count FROM audit_logs WHERE seen = 0";
$notif_result = $conn->query($notif_sql);
$unseen_count = ($notif_result && $notif_result->num_rows > 0) ? $notif_result->fetch_assoc()['unseen_count'] : 0;


// Get latest 5 unseen logs
$logs_sql = "SELECT a.*, m.fullname
             FROM audit_logs a
             LEFT JOIN members m ON a.user_id = m.id
             WHERE a.seen = 0
             ORDER BY a.timestamp DESC
             LIMIT 5";
$logs_result = $conn->query($logs_sql);
?>


<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
    </li>
  </ul>


  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
   
    <!-- Notifications Dropdown -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" role="button" title="Notifications">
        <i class="far fa-bell"></i>
        <?php if ($unseen_count > 0): ?>
          <span class="badge badge-danger navbar-badge"><?= $unseen_count ?></span>
        <?php endif; ?>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-header"><?= $unseen_count ?> New Notification<?= $unseen_count == 1 ? '' : 's' ?></span>
        <div class="dropdown-divider"></div>


        <?php if ($logs_result && $logs_result->num_rows > 0): ?>
          <?php while ($log = $logs_result->fetch_assoc()): ?>
            <a href="/rotary/webpages/audit-logs/audit_logs.php" class="dropdown-item">
              <i class="fas fa-user-edit mr-2"></i>
              <?= htmlspecialchars($log['fullname'] ?? 'Unknown User') ?>
              <span class="float-right text-muted text-sm"><?= date('M d', strtotime($log['timestamp'])) ?></span><br>
              <small><?= ucfirst($log['action']) ?> in <?= $log['table_name'] ?></small>
            </a>
            <div class="dropdown-divider"></div>
          <?php endwhile; ?>
        <?php else: ?>
          <span class="dropdown-item text-muted">No new notifications</span>
          <div class="dropdown-divider"></div>
        <?php endif; ?>


        <a href="/rotary/webpages/audit-logs/audit_logs.php" class="dropdown-item dropdown-footer">
          View All Logs
        </a>
      </div>
    </li>


    <!-- Go Back Button -->
    <li class="nav-item">
      <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary mr-2">
        <i class="fas fa-arrow-left"></i> Go Back
      </a>
    </li>


  </ul>
</nav>
<!-- /.navbar -->



