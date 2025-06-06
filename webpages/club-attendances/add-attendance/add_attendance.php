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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetch_members'])) {
    $result = $conn->query("SELECT id, fullname FROM members ORDER BY fullname");

    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }

    echo json_encode($members);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['statuses'])) {
    $statuses = $_POST['statuses'];
    $category = $_POST['category'] ?? null;
    $activity_id = $_POST['activity_id'] ?? null;
    $createdBy = $_SESSION['user_id'];

    if (!$category || !$activity_id || empty($statuses)) {
        $response['message'] = 'Please select at least one member and complete all fields.';
    } else {
        $stmt = $conn->prepare("INSERT INTO club_attendances (member_id, category, activity_id, attendance_date, status, encoded_by) VALUES (?, ?, ?, CURRENT_TIMESTAMP(), ?, ?)");
        foreach ($statuses as $memberId => $status) {
            $stmt->bind_param("isiss", $memberId, $category, $activity_id, $status, $createdBy);
            $stmt->execute();
        }
        $response['success'] = true;
        $response['message'] = 'Attendance recorded for selected members!';
    }
}
?>

<?php include('../../../includes/header.php'); ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<style>
.toggle-group {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  border: 1px solid #ccc;
  border-radius: 50px;
  overflow: hidden;
  background: #f8f9fa;
}
.toggle-option {
  flex: 1;
  padding: 3px 4px;
  border: none;
  background: none;
  color: #6c757d;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.3s, color 0.3s;
  font-size: clamp(0.75rem, 1.8vw, 0.9rem);
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
}
.toggle-option.active {
  background-color: #007bff;
  color: #fff;
  font-weight: 600;
}
</style>

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
              <div class="alert alert-success"><?php echo $response['message']; ?></div>
            <?php elseif (!empty($response['message'])): ?>
              <div class="alert alert-danger"><?php echo $response['message']; ?></div>
            <?php endif; ?>

            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Create Club Member's Attendance</h3>
              </div>

              <form id="editProfileForm" method="post" action="" enctype="multipart/form-data">

                <div class="card-body">

                  <div class="form-group">
                    <label for="category">Select Attendance Category</label>
                    <select name="category" id="category" class="form-control" required>
                      <option value="">Select Category</option>
                      <option value="Club Project">Club Project</option>
                      <option value="Club Event">Club Event</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="activity_id" id="activity_label">Select Related Activity</label>
                    <select name="activity_id" id="activity_id" class="form-control" required>
                      <option value="">Select Activity</option>
                    </select>
                  </div>

                  <div class="card mt-4 d-none" id="namesCard">
                    <div class="card-header bg-primary text-white">
                      <h5 class="card-title mb-0">Mark Attendance Status</h5>
                    </div>
                    <div class="card-body">
                 

                      <table class="table table-striped" id="attendanceTable">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th style="text-align: right;">Status</th>
                                 <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                                  <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="markAll('Absent')">Mark All Absent</button>
                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="markAll('Late')">Mark All Late</button>
                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="markAll('Present')">Mark All Present</button>
                                  </div>
                                  <div class="dataTables_filter" style="margin-left: auto;"></div> <!-- DataTable will auto-populate this -->
                                </div>
                          </tr>
                        </thead>
                        <tbody></tbody>
                      </table>
                    </div>
                  </div>

                </div>

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">Add Member's Attendance</button>
                  <a href="/rotary/webpages/club-attendances/manage-attendances/manage_attendances.php" class="btn btn-success float-right">
                    <i class="fas fa-eye"></i> View Club Attendances
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
</div>

 
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
        Are you sure you want to add this attendance?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmSubmit">Yes, Update</button>
      </div>
    </div>
  </div>
</div>
 
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


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
let attendanceTable;

$(document).ready(function () {
  attendanceTable = $('#attendanceTable').DataTable({
    responsive: true,
    autoWidth: false,
    searching: true,
    ordering: false,
    paging: false,
    info: false
  });

  $('#category').on('change', function () {
    const category = $(this).val();
    $('#activity_label').text(category === 'Club Project' ? 'Select Club Project' : category === 'Club Event' ? 'Select Club Event' : 'Select Activity');
    $('#activity_id').html('<option>Loading...</option>');
    fetch(`/rotary/includes/get_activities.php?category=${encodeURIComponent(category)}`)
      .then(response => response.json())
      .then(data => {
        let options = '<option value="">Select Activity</option>';
        data.forEach(item => {
          options += `<option value="${item.id}">${item.title}</option>`;
        });
        $('#activity_id').html(options);
      });
  });

  $('#activity_id').on('change', function () {
    const category = $('#category').val();
    const activityId = $(this).val();

    if (category && activityId) {
      fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ fetch_members: 1 })
      })
      .then(res => res.json())
      .then(data => {
        const namesCard = document.getElementById("namesCard");
        attendanceTable.clear();

        if (data.length > 0) {
          data.forEach((member, index) => {
            const toggleHTML = `
              <div class='toggle-group' data-member-id='${member.id}'>
                <button type='button' class='toggle-option active' onclick='toggleStatus(this, "Absent")'>Absent</button>
                <button type='button' class='toggle-option' onclick='toggleStatus(this, "Late")'>Late</button>
                <button type='button' class='toggle-option' onclick='toggleStatus(this, "Present")'>Present</button>
                <input type='hidden' name='statuses[${member.id}]' value='Absent'>
              </div>
            `;

            attendanceTable.row.add([
              index + 1,
              member.fullname,
              toggleHTML
            ]);
          });

          attendanceTable.draw();
          namesCard.classList.remove("d-none");
        } else {
          namesCard.classList.add("d-none");
        }
      });
    }
  });
});

function toggleStatus(button, statusValue) {
  const group = button.parentElement;
  const buttons = group.querySelectorAll('.toggle-option');
  const input = group.querySelector('input[type="hidden"]');

  buttons.forEach(btn => btn.classList.remove('active'));
  button.classList.add('active');
  input.value = statusValue;
}

function markAll(statusValue) {
  document.querySelectorAll('.toggle-group').forEach(group => {
    const buttons = group.querySelectorAll('.toggle-option');
    const input = group.querySelector('input[type="hidden"]');

    buttons.forEach(btn => {
      const btnText = btn.textContent.trim();
      if (btnText === statusValue) {
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        input.value = statusValue;
      }
    });
  });
}
</script>
</body>
</html>
