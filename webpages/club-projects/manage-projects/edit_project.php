<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

if ($_SESSION['role'] !== '1' && $_SESSION['role'] !== '3'&& $_SESSION['role'] !== '100' ) {
    header("Location: /rotary/dashboard.php");
    exit();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['edit_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $type = $_POST['type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];
    $target_funding = $_POST['target_funding'];

    $updateQuery = "UPDATE club_projects SET 
        title = '$title',
        description = '$description',
        type = '$type',
        start_date = '$start_date',
        end_date = '$end_date',
        status = '$status',
        target_funding = '$target_funding',
        remaining_funding = '$remaining_funding'
        WHERE id = $id";

    if ($conn->query($updateQuery) === TRUE) {
        $response['success'] = true;
        $response['message'] = 'Project updated successfully!';
    } else {
        $response['message'] = 'Error: ' . $conn->error;
    }
}

$edit_id = $_GET['id'] ?? null;
$editQuery = "SELECT * FROM club_projects WHERE id = $edit_id";
$result = $conn->query($editQuery);
$editData = $result->fetch_assoc();
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
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Edit Club Project</h3>
              </div>

             <form id="editProfileForm" method="post" action="" enctype="multipart/form-data">

                <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">

                <div class="card-body">
                  <div class="form-group">
                    <label for="title">Project Title</label>
                    <input type="text" class="form-control" name="title" value="<?php echo $editData['title']; ?>" required>
                  </div>

                  <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" name="description" rows="3" required><?php echo $editData['description']; ?></textarea>
                  </div>

                  <div class="form-group">
                    <label for="type">Project Type</label>
                    <input type="text" class="form-control" name="type" value="<?php echo $editData['type']; ?>" required>
                  </div>

                  <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="<?php echo $editData['start_date']; ?>" required>
                  </div>

                  <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="<?php echo $editData['end_date']; ?>" required>
                  </div>

                  <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control" name="status" required>
                      <option value="planned" <?php echo ($editData['status'] === 'planned') ? 'selected' : ''; ?>>Planned</option>
                      <option value="ongoing" <?php echo ($editData['status'] === 'ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                      <option value="completed" <?php echo ($editData['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                      <option value="cancelled" <?php echo ($editData['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="target_funding">Target Funding</label>
                    <input type="number" class="form-control" name="target_funding" value="<?php echo $editData['target_funding']; ?>" required>
                  </div>

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">Update Project</button>

                  <a href="/rotary/webpages/club-projects/manage-projects/manage_projects.php" class="btn btn-success float-right">
                    <i class="fas fa-plus"></i> View Projects
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
        Are you sure you want to update this project?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmSubmit">Yes, Update</button>
      </div>
    </div>
  </div>
</div>
 
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
