<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if ($_SESSION['role'] !== '1'&& $_SESSION['role'] !== '5' && $_SESSION['role'] !== '100' ) {
    header("Location: /rotary/dashboard.php");
    exit();
}

// Fetch audit logs along with user name from members table
$sql = "SELECT a.*, m.fullname AS user_name
        FROM audit_logs a
        LEFT JOIN members m ON a.user_id = m.id
        ORDER BY a.timestamp DESC";
$result = $conn->query($sql);


// Mark all unseen logs as seen AFTER fetching successfully
if ($result && $result->num_rows > 0) {
    $conn->query("UPDATE audit_logs SET seen = 1 WHERE seen = 0");
}


include('../../includes/header.php');
?>


<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">


<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
    <?php include('../../includes/nav.php'); ?>
    <?php include('../../includes/sidebar.php'); ?>


    <div class="content-wrapper">
        <?php include('../../includes/page_title.php'); ?>


        <section class="content">
            <div class="container-fluid">
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Audit Logs</h3>
                    </div>
                    <div class="card-body">
                        <table id="auditLogsTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Table</th>
                                    <th>Record ID</th>
                                    <th>Changes</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <?php
                                            $changes = json_decode($row['changes'], true);
                                            $prettyChanges = $changes
                                                ? json_encode($changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                                                : 'Invalid JSON';
                                           
                                            // Define badge class based on action
                                            $action = strtolower($row['action']);
                                            $badgeClass = 'secondary';
                                            if ($action === 'insert') $badgeClass = 'success';
                                            elseif ($action === 'update') $badgeClass = 'warning';
                                            elseif ($action === 'delete') $badgeClass = 'danger';
                                        ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= htmlspecialchars($row['user_name'] ?? 'Unknown') ?></td>
                                            <td>
                                                <span class="badge bg-<?= $badgeClass ?>">
                                                    <?= ucfirst($row['action']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($row['table_name']) ?></td>
                                            <td><?= $row['record_id'] ?></td>
                                            <td><pre><?= $prettyChanges ?></pre></td>
                                            <td><?= date('M j, Y | h:ia', strtotime($row['timestamp'])) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center">No audit logs available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>


    <aside class="control-sidebar control-sidebar-dark"></aside>


    <footer class="main-footer">
     
        <div class="float-right d-none d-sm-inline-block">
            <b>Developed By</b> <a href="https://codeastro.com/">Group 9</a>
        </div>
    </footer>
</div>


<!-- DataTables Scripts -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('#auditLogsTable').DataTable({
            responsive: true,
            order: [[6, 'desc']],
            pageLength: 10
        });
    });
</script>


<?php include('../../includes/footer.php'); ?>
</body>
</html>



