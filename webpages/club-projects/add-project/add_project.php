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

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $type = $_POST['type'];
    $start_date = $_POST['start_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $status = $_POST['status'];
    $encoded_by = $_SESSION['user_id'];
    $target_funding = isset($_POST['target_funding']) ? floatval(str_replace(',', '', $_POST['target_funding'])) : 0;

    $insertQuery = "INSERT INTO club_projects 
        (title, description, type, start_date, end_date, location, status, encoded_by, target_funding) 
        VALUES 
        ('$title', '$description', '$type', '$start_date', " . ($end_date ? "'$end_date'" : "NULL") . ", 
        '$location', '$status', $encoded_by, $target_funding)";

    if ($conn->query($insertQuery) === TRUE) {
        $response['success'] = true;
        $response['message'] = 'Project added successfully!';
    } else {
        $response['message'] = 'Error: ' . $conn->error;
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
<div class="card-header"><h3 class="card-title">Create New Club Project</h3></div>
<form id="editProfileForm" method="post" action="" enctype="multipart/form-data">
<div class="card-body">

<!-- Project Info -->
<h5 class="text-primary"><i class="fas fa-project-diagram"></i> Project Details</h5>
<div class="form-group">
    <label for="title">Project Name</label>
    <input type="text" class="form-control" id="title" name="title" placeholder="Enter the project name" required>
</div>

<div class="form-group">
    <label for="type">What kind of project is this?</label>
    <select class="form-control" id="type" name="type" required>
        <option value="project">Project</option>
        <option value="community service">Community Service</option>
        <option value="other">Other</option>
    </select>
</div>

<div class="form-group">
    <label for="description">Describe the Project</label>
    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Write a short description of what the project is about" required></textarea>
</div>

<hr>

<!-- Schedule Info -->
<h5 class="text-primary"><i class="fas fa-calendar-alt"></i> Schedule</h5>
<div class="form-group">
    <label for="start_date">Start Date</label>
    <input type="date" class="form-control" id="start_date" name="start_date" required>
</div>

<div class="form-group">
    <label for="end_date">End Date (Optional)</label>
    <input type="date" class="form-control" id="end_date" name="end_date">
</div>

<hr>

<!-- Location & Status -->
<h5 class="text-primary"><i class="fas fa-map-marker-alt"></i> Location & Status</h5>
<div class="form-group">
    <label for="location">Project Location</label>
    <input type="text" class="form-control" id="location" name="location" placeholder="Enter the location" required>
</div>

<div class="form-group">
    <label for="status">Current Status</label>
    <select class="form-control" id="status" name="status" required>
        <option value="upcoming">Upcoming</option>
        <option value="ongoing">Ongoing</option>
        <option value="completed">Completed</option>
    </select>
</div>

<hr>

<!-- Funding Info -->
<h5 class="text-primary"><i class="fas fa-hand-holding-usd"></i> Funding</h5>
<div class="form-group">
    <label for="target_funding">Target Funding Amount (₱)</label>
    <input type="text" class="form-control" id="target_funding" name="target_funding" placeholder="Enter the target funding amount" required>
</div>

</div>
<div class="card-footer">
    <button type="submit" class="btn btn-primary">Add Club Project</button>
    <a href="/rotary/webpages/club-projects/manage-projects/manage_projects.php" class="btn btn-success float-right">
        <i class="fas fa-eye"></i> View Projects
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
        <h5 class="modal-title" id="confirmModalLabel"><i class="fas fa-exclamation-circle"></i> Confirm Project Creation</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to add this project?
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
    let form = $('#editProfileForm');
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
    const amountInput = document.getElementById('target_funding');
    amountInput.addEventListener('input', function () {
        let value = this.value.replace(/[^0-9.]/g, '');
        this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    });
});
</script>

</body>
</html>
