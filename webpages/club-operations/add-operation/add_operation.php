<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

if ($_SESSION['role'] !== '1' && $_SESSION['role'] !== '3' && $_SESSION['role'] !== '4' && $_SESSION['role'] !== '100') {
    header("Location: /rotary/dashboard.php");
    exit();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = trim($_POST['category']);
    $amount = floatval(str_replace(',', '', $_POST['amount']));
    $payment_date = $_POST['payment_date'];
    $paid_to = trim($_POST['paid_to']);
    $notes = trim($_POST['notes']);
    $status = $_POST['status'] ?? 'Unpaid';
    $encoded_by = $_SESSION['user_id'];

    if (empty($category) || empty($amount) || empty($payment_date)) {
        $response['message'] = 'Expense Type, Amount, and Payment Date are required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO club_operations (category, amount, payment_date, paid_to, notes, status, encoded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdssssi", $category, $amount, $payment_date, $paid_to, $notes, $status, $encoded_by);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Expense added successfully!';
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
<div class="card-header"><h3 class="card-title">Create New Club Operation</h3></div>
<form id="editProfileForm" method="post" action="">
<div class="card-body">

<!-- Expense Info -->
<h5 class="text-primary"><i class="fas fa-file-invoice-dollar"></i> Operation Details</h5>
<div class="form-group">
    <label for="category">Expense Type</label>
    <input type="text" class="form-control" name="category" placeholder="e.g. Electricity Bill, Cleaning Service" required>
</div>

<div class="form-group">
    <label for="amount">Total Amount (₱)</label>
    <input type="text" class="form-control" name="amount" id="amount" placeholder="Enter amount in pesos" required>
</div>

<div class="form-group">
    <label for="payment_date">Payment Date</label>
    <input type="date" class="form-control" name="payment_date" required>
</div>

<hr>

<!-- Optional Info -->
<h5 class="text-primary"><i class="fas fa-info-circle"></i> Additional Information</h5>
<div class="form-group">
    <label for="paid_to">Paid To (optional)</label>
    <input type="text" class="form-control" name="paid_to" placeholder="Name of person/company">
</div>

<div class="form-group">
    <label for="notes">Additional Notes (optional)</label>
    <textarea class="form-control" name="notes" rows="3" placeholder="Any extra information..."></textarea>
</div>

<div class="form-group">
    <label for="status">Payment Status</label>
    <select class="form-control" name="status">
        <option value="Unpaid">Unpaid</option>
        <option value="Paid">Paid</option>
    </select>
</div>

</div>
<div class="card-footer">
    <button type="submit" class="btn btn-primary">Add Club Operation</button>
    <a href="/rotary/webpages/club-operations/manage-operations/manage_operations.php" class="btn btn-success float-right">
        <i class="fas fa-eye"></i> View Club Operations
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
        <h5 class="modal-title" id="confirmModalLabel"><i class="fas fa-exclamation-circle"></i> Confirm Operation</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to add this type of expense?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmSubmit">Yes, Add</button>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {
    const form = $('#editProfileForm');

    form.on('submit', function (e) {
        if (!form.data('confirmed')) {
            e.preventDefault();
            $('#confirmModal').modal('show');
        }
    });

    $('#confirmSubmit').on('click', function () {
        $('#confirmModal').modal('hide');
        form.data('confirmed', true).submit();
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const amountInput = document.getElementById('amount');
    if (amountInput) {
        amountInput.addEventListener('input', function () {
            let value = this.value.replace(/[^0-9.]/g, '');
            this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        });
    }
});
</script>

</body>
</html>
