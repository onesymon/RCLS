<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}
if ($_SESSION['role'] !== '1' && $_SESSION['role'] !== '5' && $_SESSION['role'] !== '3' && $_SESSION['role'] !== '6' && $_SESSION['role'] !== '100') {
    header("Location: /rotary/dashboard.php");
    exit();
}

// Handle update status actions
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'];

    if (!in_array($status, ['Upcoming', 'Ongoing', 'Completed'])) {
        die("Invalid status.");
    }

    $stmt = $conn->prepare("UPDATE club_events SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        header("Location: manage_events.php?msg=Status+updated");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    exit();
}

// Currency symbol
$currencySymbol = '₱';
$currencyQuery = "SELECT currency FROM settings WHERE id = 1";
$currencyResult = $conn->query($currencyQuery);
if ($currencyResult->num_rows > 0) {
    $currencySymbol = $currencyResult->fetch_assoc()['currency'];
}

// Handle filtering
$filterStatus = $_GET['filter'] ?? 'All';
$allowedFilters = ['All', 'Upcoming', 'Ongoing', 'Completed'];
if (!in_array($filterStatus, $allowedFilters)) {
    $filterStatus = 'Upcoming';
}

// Query for club_events
$query = "
    SELECT ce.*, 
           m.fullname AS encoded_by_name
    FROM club_events ce
    LEFT JOIN members m ON ce.encoded_by = m.id
";

if ($filterStatus !== 'All') {
    $query .= " WHERE ce.status = ?";
}

$query .= " ORDER BY ce.event_date DESC";

$stmt = $conn->prepare($query);

if ($filterStatus !== 'All') {
    $stmt->bind_param("s", $filterStatus);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<?php include('../../../includes/header.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
                                <h3 class="card-title mb-0">Club Events Table</h3>

                                <div class="mx-auto">
                                    <form method="GET" class="form-inline">
                                        <label for="filter" class="mr-2 mb-0">Filter by Status:</label>
                                        <select name="filter" id="filter" class="form-control" onchange="this.form.submit()">
                                            <option value="All" <?= $filterStatus == 'All' ? 'selected' : '' ?>>All</option>
                                            <option value="Upcoming" <?= $filterStatus == 'Upcoming' ? 'selected' : '' ?>>Upcoming</option>
                                            <option value="Ongoing" <?= $filterStatus == 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                            <option value="Completed" <?= $filterStatus == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                    </form>
                                </div>

                                <?php if ($_SESSION['role'] === '3' || $_SESSION['role'] === '100'): ?>
                                    <a href="/rotary/webpages/club-events/add-event/add_event.php" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Add Event
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <table id="eventsTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Title</th>
                                            <th>Target Funding</th>
                                            <th>Current Funding</th>
                                            <th>Remaining Funding</th>
                                            <th>Event Date</th>
                                            <th>Event Time</th>
                                            <th>Location</th>
                                            <th>Encoded By</th>
                                            <th>Status</th>
                                            <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '3' || $_SESSION['role'] === '5' || $_SESSION['role'] === '100'): ?>
                                                <th>Actions</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $counter = 1;
                                        while ($row = $result->fetch_assoc()) {
                                            $noFunding = ($row['target_funding'] == 0);

                                            $remaining = $row['target_funding'] - $row['current_funding'];
                                            $remainingFormatted = $noFunding ? "No Funding Needed" : $currencySymbol . number_format($remaining, 2);

                                            echo "<tr>";
                                            echo "<td>{$counter}</td>";
                                            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                            echo "<td>" . ($noFunding ? "No Funding Needed" : $currencySymbol . number_format($row['target_funding'], 2)) . "</td>";
                                            echo "<td>" . ($noFunding ? "No Funding Needed" : $currencySymbol . number_format($row['current_funding'], 2)) . "</td>";
                                            echo "<td>" . $remainingFormatted . "</td>";
                                            echo "<td>" . date("F j, Y", strtotime($row['event_date'])) . "</td>";
                                            echo "<td>" . (!empty($row['event_time']) ? date("g:i A", strtotime($row['event_time'])) : '-') . "</td>";
                                            echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['encoded_by_name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";

                                            if (in_array($_SESSION['role'], ['1', '3', '5', '100'])) {
                                                echo "<td>";
                                                if (in_array($_SESSION['role'], ['1', '3', '100'])) {
                                                    if ($row['status'] === 'Upcoming') {
                                                    echo '<button class="btn btn-success btn-sm confirm-action" data-id="' . $row['id'] . '" data-status="Ongoing" data-action-type="status" title="Mark as Ongoing"><i class="fas fa-check"></i></button> ';
                                                    echo '<a href="edit_event.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm" title="Edit Event"><i class="fas fa-edit"></i></a> ';
                                                    echo '<button class="btn btn-danger btn-sm confirm-action" data-id="' . $row['id'] . '" data-action-type="delete" title="Delete"><i class="fas fa-trash"></i></button>';
                                                } elseif ($row['status'] === 'Ongoing') {
                                                    echo '<button class="btn btn-success btn-sm confirm-action" data-id="' . $row['id'] . '" data-status="Completed" data-action-type="status" title="Mark as Completed"><i class="fas fa-check"></i></button> ';
                                                    echo '<a href="event_details.php?id=' . $row['id'] . '" class="btn btn-info btn-sm" title="View Details"><i class="fas fa-eye"></i></a>';
                                                } elseif ($row['status'] === 'Completed') {
                                                    echo '<a href="event_details.php?id=' . $row['id'] . '" class="btn btn-info btn-sm" title="View Details"><i class="fas fa-eye"></i></a> ';
                                                    echo '<button class="btn btn-danger btn-sm confirm-action" data-id="' . $row['id'] . '" data-action-type="delete" title="Delete"><i class="fas fa-trash"></i></button>';
                                                }

                                                } elseif ($_SESSION['role'] === '5' || $_SESSION['role'] === '100') {
                                                    echo '<a href="event_details.php?id=' . $row['id'] . '" class="btn btn-info btn-sm" title="View Details"><i class="fas fa-eye"></i></a>';
                                                }
                                                echo "</td>";
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
    <!-- Bootstrap Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="confirmModalBody">
        Are you sure you want to perform this action?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" id="confirmActionBtn" class="btn btn-primary">Yes, Proceed</button>
      </div>
    </div>
  </div>
</div>

</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let actionUrl = '';

    $(function () {
        $("#eventsTable").DataTable({
            responsive: true,
            autoWidth: false,
            searching: true,
        });

        // Trigger modal on action button click
        $('.card-body').on('click', '.confirm-action', function () {
            const id = $(this).data('id');
            const status = $(this).data('status');
            const actionType = $(this).data('action-type');

            if (actionType === 'status') {
                actionUrl = `manage_events.php?id=${id}&status=${encodeURIComponent(status)}`;
                $('#confirmModalLabel').text(`Confirm ${status}`);
                $('#confirmModalBody').html(`Are you sure you want to mark this event as <strong>${status}</strong>?`);
            } else if (actionType === 'delete') {
                actionUrl = `delete_event.php?id=${id}`;
                $('#confirmModalLabel').text("Delete Event");
                $('#confirmModalBody').html("Are you sure you want to <strong>delete</strong> this event?");
            }

            $('#confirmModal').modal('show');
        });

        $('#confirmActionBtn').on('click', function () {
            if (actionUrl !== '') {
                window.location.href = actionUrl;
            }
        });
    });
</script>


</body>
</html>
