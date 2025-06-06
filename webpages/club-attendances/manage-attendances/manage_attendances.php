<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

if (!in_array($_SESSION['role'], ['1', '3', '100'])) {
    header("Location: /rotary/dashboard.php");
    exit();
}

$eventsQuery = "SELECT id, title FROM club_events ORDER BY title";
$eventsResult = $conn->query($eventsQuery);

$projectsQuery = "SELECT id, title FROM club_projects ORDER BY title";
$projectsResult = $conn->query($projectsQuery);

$filterActivity = $_GET['filter'] ?? 'All';
$filterActivityID = $_GET['filter_id'] ?? '';

$allowedFilters = ['All', 'Event', 'Club Project'];
if (!in_array($filterActivity, $allowedFilters)) {
    $filterActivity = 'All';
}

$query = "
    SELECT a.*, 
           m.fullname AS member_name, 
           cp.title AS project_title,
           ce.title AS event_title,
           creator.fullname AS encoded_by_name
    FROM club_attendances a
    JOIN members m ON a.member_id = m.id
    LEFT JOIN club_projects cp ON a.activity_id = cp.id
    LEFT JOIN club_events ce ON a.activity_id = ce.id
    LEFT JOIN members creator ON a.encoded_by = creator.id
";

if ($filterActivity !== 'All' && $filterActivityID) {
    $query .= " WHERE a.category = ?";
    if ($filterActivity === 'Event' || $filterActivity === 'Club Project') {
        $query .= " AND a.activity_id = ?";
    }
}

$query .= " ORDER BY a.attendance_date DESC";
$stmt = $conn->prepare($query);

if ($filterActivity !== 'All' && $filterActivityID) {
    if ($filterActivity === 'Event' || $filterActivity === 'Club Project') {
        $stmt->bind_param("si", $filterActivity, $filterActivityID);
    }
}

$stmt->execute();
$result = $stmt->get_result();
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
                    <div class="col-12">

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h3 class="card-title mb-0">Club Attendance Records</h3>

                                <div class="mx-auto">
                                    <form method="GET" class="form-inline">
                                        <label for="filter" class="mr-2 mb-0">Filter by Category:</label>
                                        <select name="filter" id="filter" class="form-control" onchange="this.form.submit()">
                                            <option value="All" <?= $filterActivity === 'All' ? 'selected' : '' ?>>All</option>
                                            <option value="Club Project" <?= $filterActivity === 'Club Project' ? 'selected' : '' ?>>Club Project</option>
                                            <option value="Event" <?= $filterActivity === 'Event' ? 'selected' : '' ?>>Club Event</option>
                                        </select>

                                        <span class="ml-3"></span>

                                        <label for="filter_id" class="mr-2 mb-0">Select Activity:</label>
                                        <select name="filter_id" id="filter_id" class="form-control" onchange="this.form.submit()">
                                            <option value="">-- Select --</option>
                                            <?php
                                            if ($filterActivity === 'Event') {
                                                while ($event = $eventsResult->fetch_assoc()) {
                                                    $selected = $filterActivityID == $event['id'] ? 'selected' : '';
                                                    echo "<option value='{$event['id']}' $selected>{$event['title']}</option>";
                                                }
                                            } elseif ($filterActivity === 'Club Project') {
                                                while ($project = $projectsResult->fetch_assoc()) {
                                                    $selected = $filterActivityID == $project['id'] ? 'selected' : '';
                                                    echo "<option value='{$project['id']}' $selected>{$project['title']}</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </form>
                                </div>

                                <?php if (in_array($_SESSION['role'], ['1', '100'])): ?>
                                    <a href="/rotary/webpages/club-attendances/add-attendance/add_attendance.php" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Add Attendance
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <table id="attendanceTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Member</th>
                                            <th>Category</th>
                                            <th>Activity</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Remarks</th>
                                            <th>Created By</th>
                                            <?php if (in_array($_SESSION['role'], ['1', '4', '5', '100'])): ?>
                                                <th>Actions</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $counter = 1;
                                        while ($row = $result->fetch_assoc()) {
                                            $category = $row['category'] === 'Club Project' ? 'Club Project' : ($row['category'] === 'Club Event' ? 'Event' : '-');
                                            $activity = $row['category'] === 'Club Project' ? ($row['project_title'] ?? '-') : ($row['event_title'] ?? '-');

                                            // Highlighted status
                                            $status = strtolower($row['status']);
                                            switch ($status) {
                                                case 'present':
                                                    $statusClass = 'badge badge-success';
                                                    break;
                                                case 'absent':
                                                    $statusClass = 'badge badge-danger';
                                                    break;
                                                case 'late':
                                                    $statusClass = 'badge badge-warning';
                                                    break;
                                                default:
                                                    $statusClass = 'badge badge-secondary';
                                                    break;
                                            }

                                            echo "<tr>";
                                            echo "<td>{$counter}</td>";
                                            echo "<td>" . htmlspecialchars($row['member_name']) . "</td>";
                                            echo "<td>{$category}</td>";
                                            echo "<td>" . htmlspecialchars($activity) . "</td>";
                                            echo "<td>" . date("F j, Y", strtotime($row['attendance_date'])) . "</td>";
                                            echo "<td><span class=\"$statusClass\">" . htmlspecialchars($row['status']) . "</span></td>";
                                            echo "<td>" . htmlspecialchars($row['remarks']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['encoded_by_name'] ?? '-') . "</td>";
                                            if (in_array($_SESSION['role'], ['1', '4', '5', '100'])) {
                                                echo '<td>';
                                                echo '<a href="/rotary/webpages/club-attendances/manage-attendances/edit_attendance.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm mr-1" title="Edit"><i class="fas fa-edit"></i></a>';
                                                echo '<button class="btn btn-danger btn-sm" onclick="confirmDelete(' . $row['id'] . ')" title="Delete"><i class="fas fa-trash"></i></button>';
                                                echo '</td>';
                                            }
                                            echo "</tr>";
                                            $counter++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include('../../../includes/footer.php'); ?>
    
</div>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteConfirmModalLabel"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this member? This action cannot be undone.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Cancel</button>
        <a href="#" class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete</a>
      </div>
    </div>
  </div>
</div>


<script>
  // Trigger delete modal and bind dynamic URL
function confirmDelete(attendanceId) {
    const deleteUrl = `/rotary/webpages/club-attendances/manage-attendances/delete_attendance.php?id=${attendanceId}`;
    $('#confirmDeleteBtn').attr('href', deleteUrl);
    $('#deleteConfirmModal').modal('show');
}

</script>


<script>
    $(function () {
        $("#attendanceTable").DataTable({
            "responsive": true,
            "autoWidth": false,
            "searching": true
        });
    });

    function deleteAttendance(id) {
        if (confirm("Are you sure you want to delete this attendance record?")) {
            window.location.href = '/rotary/webpages/club-attendances/manage-attendances/delete_attendance.php?id=' + id;
        }
    }
</script>

</body>
</html>
