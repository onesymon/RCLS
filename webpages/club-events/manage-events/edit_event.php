<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

if ($_SESSION['role'] !== '1'&&$_SESSION['role']!=='3') {
    header("Location: /rotary/dashboard.php");
    exit();
}

$response = ['success' => false, 'message' => ''];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = $_POST['location'];
    $status = $_POST['status'] ?? 'Upcoming';
    $edit_id = $_POST['edit_id'];

    // Set funding values
    $needs_funding = $_POST['needs_funding'] ?? 'no';
    if ($needs_funding === 'yes') {
        $target_funding = floatval($_POST['target_funding']);
        $current_funding = 0.00;
    } else {
        $target_funding = 0.00;
        $current_funding = 0.00;
    }

    $stmt = $conn->prepare("
        UPDATE club_events SET 
            title = ?, 
            description = ?, 
            target_funding = ?, 
            current_funding = ?, 
            event_date = ?, 
            event_time = ?, 
            location = ?, 
            status = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssddssssi", $title, $description, $target_funding, $current_funding, $event_date, $event_time, $location, $status, $edit_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Event updated successfully!';
    } else {
        $response['message'] = 'Error: ' . $stmt->error;
    }
}

// Fetch event data for the form
$edit_id = $_GET['id'] ?? $_POST['edit_id'] ?? null;

if (!$edit_id) {
    echo "Invalid event ID.";
    exit();
}

$editQuery = $conn->prepare("SELECT * FROM club_events WHERE id = ?");
$editQuery->bind_param("i", $edit_id);
$editQuery->execute();
$editData = $editQuery->get_result()->fetch_assoc();

if (!$editData) {
    echo "Event not found.";
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
                            <h3 class="card-title">Edit Club Event</h3>
                        </div>

                 <form id="editProfileForm" method="post" action="" enctype="multipart/form-data">

                            <div class="card-body">
                                <input type="hidden" name="edit_id" value="<?= htmlspecialchars($editData['id']) ?>">

                                <div class="form-group">
                                    <label for="title">Event Title</label>
                                    <input type="text" name="title" class="form-control" id="title" value="<?= htmlspecialchars($editData['title']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" class="form-control" id="description"><?= htmlspecialchars($editData['description']) ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="event_date">Event Date</label>
                                    <input type="date" name="event_date" class="form-control" id="event_date" value="<?= htmlspecialchars($editData['event_date']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="event_time">Event Time</label>
                                    <input type="time" name="event_time" class="form-control" id="event_time" value="<?= htmlspecialchars($editData['event_time']) ?>">
                                </div>

                                <div class="form-group">
                                    <label for="location">Location</label>
                                    <input type="text" name="location" class="form-control" id="location" value="<?= htmlspecialchars($editData['location']) ?>">
                                </div>

                                <div class="form-group">
                                    <label>Does this event need funding?</label>
                                    <select class="form-control" id="needs_funding" name="needs_funding" required onchange="toggleFundingField()">
                                        <option value="no" <?= $editData['target_funding'] == 0 ? 'selected' : '' ?>>No</option>
                                        <option value="yes" <?= $editData['target_funding'] > 0 ? 'selected' : '' ?>>Yes</option>
                                    </select>
                                </div>

                                <div class="form-group" id="fundingField" style="<?= $editData['target_funding'] > 0 ? 'display: block;' : 'display: none;' ?>">
                                    <label for="target_funding">Target Funding</label>
                                    <input type="number" step="0.01" name="target_funding" class="form-control" id="target_funding" value="<?= $editData['target_funding'] ?>">
                                </div>

                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" name="status">
                                        <option value="Upcoming" <?= $editData['status'] == 'Upcoming' ? 'selected' : '' ?>>Upcoming</option>
                                        <option value="Ongoing" <?= $editData['status'] == 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                        <option value="Completed" <?= $editData['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    </select>
                                </div>

                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Update Event</button>

                                <a href="/rotary/webpages/club-events/manage-events/manage_events.php" class="btn btn-success float-right">
                                    <i class="fas fa-eye"></i> View Events
                                </a>
                            </div>
                        </form>

                    </div>

                </div>
            </div>
        </div>
    </section>
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
        Are you sure you want to update this event?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmSubmit">Yes, Update</button>
      </div>
    </div>
  </div>
</div>

 

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

<script>
    function toggleFundingField() {
        var needsFunding = document.getElementById('needs_funding').value;
        var fundingField = document.getElementById('fundingField');

        if (needsFunding === 'yes') {
            fundingField.style.display = 'block';
            document.getElementById('target_funding').required = true;
        } else {
            fundingField.style.display = 'none';
            document.getElementById('target_funding').required = false;
            document.getElementById('target_funding').value = '';
        }
    }
</script>

</body>
</html>
