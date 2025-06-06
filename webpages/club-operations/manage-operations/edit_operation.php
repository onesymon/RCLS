<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

if ($_SESSION['role'] !== '4' && $_SESSION['role'] !== '3'&& $_SESSION['role'] !== '100' ) {
    header("Location: /rotary/dashboard.php");
    exit();
}

$response = ['success' => false, 'message' => ''];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $paid_to = $_POST['paid_to'];
    $notes = $_POST['notes'];
    $edit_id = $_POST['edit_id'];

    $stmt = $conn->prepare("
        UPDATE club_operations SET 
            category = ?, 
            amount = ?, 
            payment_date = ?, 
            paid_to = ?, 
            notes = ?
        WHERE id = ?
    ");
    $stmt->bind_param("sdsssi", $category, $amount, $payment_date, $paid_to, $notes, $edit_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Operation updated successfully!';
    } else {
        $response['message'] = 'Error: ' . $stmt->error;
    }
}

// Fetch operation data for the form
$edit_id = $_GET['id'] ?? $_POST['edit_id'] ?? null;

if (!$edit_id) {
    echo "Invalid operation ID.";
    exit();
}

$editQuery = $conn->prepare("SELECT * FROM club_operations WHERE id = ?");
$editQuery->bind_param("i", $edit_id);
$editQuery->execute();
$editData = $editQuery->get_result()->fetch_assoc();

if (!$editData) {
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
                        <div class="card-header">
                            <h3 class="card-title">Edit Club Operation</h3>
                        </div>

                       <form id="editProfileForm" method="post" action="" enctype="multipart/form-data">

                            <div class="card-body">
                                <input type="hidden" name="edit_id" value="<?= htmlspecialchars($editData['id']) ?>">

                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <input type="text" name="category" class="form-control" id="category" value="<?= htmlspecialchars($editData['category']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="amount">Amount</label>
                                    <input type="number" step="0.01" name="amount" class="form-control" id="amount" value="<?= htmlspecialchars($editData['amount']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="payment_date">Payment Date</label>
                                    <input type="date" name="payment_date" class="form-control" id="payment_date" value="<?= htmlspecialchars($editData['payment_date']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="paid_to">Paid To</label>
                                    <input type="text" name="paid_to" class="form-control" id="paid_to" value="<?= htmlspecialchars($editData['paid_to']) ?>"  >
                                </div>

                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" class="form-control" id="notes"><?= htmlspecialchars($editData['notes']) ?></textarea>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Update Operation</button>

                                <a href="/rotary/webpages/club-operations/manage-operations/manage_operations.php" class="btn btn-success float-right">
                                    <i class="fas fa-plus"></i> View Operations
                                </a>
                            </div>
                        </form>

                    </div>

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

<?php include('../../../includes/footer.php'); ?>

 
<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-primary">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="confirmModalLabel"><i class="fas fa-exclamation-circle"></i> Confirm Update</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to update this operation?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmSubmit">Yes, Update</button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript Dependencies --> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap -->

<!-- Custom Scripts -->
<script>
    function previewPhoto(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('photoPreview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    $(document).ready(function () {
        let form = $('#editProfileForm');

        form.on('submit', function (e) {
            // If not confirmed yet, block and show modal
            if (!form.data('confirmed')) {
                e.preventDefault(); // stop submission
                $('#confirmModal').modal('show'); // show confirmation modal
            }
        });

        $('#confirmSubmit').on('click', function () {
            $('#confirmModal').modal('hide'); // hide modal
            $('#editProfileForm').data('confirmed', true).submit(); // set confirmed flag and resubmit
        });
    });
</script>

</body>
</html>
