<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

if (!in_array($_SESSION['role'], ['1', '3', '4', '100'])) {
    header("Location: /rotary/dashboard.php");
    exit();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fund_name = trim($_POST['fund_name']);
    $description = trim($_POST['description']);
    $currency = trim($_POST['currency']);
    $status = trim($_POST['status']);
    $owner = trim($_POST['owner']);
    $current_balance = floatval($_POST['current_balance']);
    $encoded_by = $_SESSION['user_id'];

    if (empty($fund_name) || $current_balance < 0) {
        $response['message'] = 'Fund name and starting balance are required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO club_wallet_categories (fund_name, description, current_balance, currency, status, owner, encoded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsssi", $fund_name, $description, $current_balance, $currency, $status, $owner, $encoded_by);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Fund wallet added successfully!';
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }

        $stmt->close();
    }
}
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
<div class="row">
<div class="col-md-12">

<?php if ($response['success']): ?>
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h5><i class="icon fas fa-check"></i> Success</h5>
    <?php echo $response['message']; ?>
</div>
<?php elseif (!empty($response['message'])): ?>
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h5><i class="icon fas fa-ban"></i> Error</h5>
    <?php echo $response['message']; ?>
</div>
<?php endif; ?>

<div class="card card-primary">
<div class="card-header"><h3 class="card-title">Create New Club Wallet</h3></div>
<form id="clubWalletForm" method="post" action="" enctype="multipart/form-data">
<div class="card-body">

<!-- Fund Details -->
<h5 class="text-primary"><i class="fas fa-wallet"></i> Wallet Information</h5>
<div class="form-group">
    <label for="fund_name">Fund Name</label>
    <input type="text" class="form-control" name="fund_name" placeholder="e.g., Club Fund, Donations" required>
</div>

<div class="form-group">
    <label for="description">Description (optional)</label>
    <textarea class="form-control" name="description" rows="3" placeholder="Describe the purpose of this fund wallet..."></textarea>
</div>

<hr>

<!-- Financial Settings -->
<h5 class="text-primary"><i class="fas fa-coins"></i> Financial Settings</h5>
<div class="form-group">
    <label for="currency">Currency</label>
    <input type="text" class="form-control" name="currency" value="PHP" placeholder="e.g., PHP, USD" required>
</div>

<div class="form-group">
    <label for="current_balance">Starting Balance (₱)</label>
    <input type="number" step="0.01" class="form-control" name="current_balance" placeholder="Enter starting balance" required>
</div>

<div class="form-group">
    <label for="status">Status</label>
    <select class="form-control" name="status" required>
        <option value="Active" selected>Active</option>
        <option value="Inactive">Inactive</option>
    </select>
</div>

<hr>

<!-- Ownership Info -->
<h5 class="text-primary"><i class="fas fa-user-shield"></i> Ownership</h5>
<div class="form-group">
    <label for="owner">Owner / Responsible Person (optional)</label>
    <input type="text" class="form-control" name="owner" placeholder="e.g., Treasurer, Member Name">
</div>

</div>
<div class="card-footer">
    <button type="submit" class="btn btn-primary">Add Wallet</button>
    <a href="/rotary/webpages/club-wallets/manage-wallets/manage_wallets.php" class="btn btn-success float-right">
        <i class="fas fa-eye"></i> View Wallets
    </a>
</div>
</form>
</div>

</div></div></div></section></div>

<footer class="main-footer">
  <div class="float-right d-none d-sm-inline-block">
    <b>Developed By</b> Group 9
  </div>
</footer>
</div>

<?php include('../../../includes/footer.php'); ?>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-primary">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="confirmModalLabel"><i class="fas fa-exclamation-circle"></i> Confirm Wallet Creation</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to add this club wallet?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmSubmit">Yes, Add</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {
    const form = $('#clubWalletForm');

    form.on('submit', function (e) {
        if (!form.data('confirmed')) {
            e.preventDefault();
            $('#confirmModal').modal('show');
        }
    });

    $('#confirmSubmit').on('click', function () {
        $('#confirmModal').modal('hide');
        $('#clubWalletForm').data('confirmed', true).submit();
    });
});
</script>

</body>
</html>
