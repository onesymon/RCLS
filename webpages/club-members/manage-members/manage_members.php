<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

// Validate user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

// Block members from accessing officer-only pages
if ($_SESSION['role'] !== '1' && $_SESSION['role'] !== '3' && $_SESSION['role'] === '4' && $_SESSION['role'] === '100') {
  header("Location: /rotary/dashboard.php"); // or show a 403 error page
  exit();
}

// Get filter type from GET or default to 'all'
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build the SQL query based on filter
$baseQuery = "SELECT members.*, club_position.position_name 
              FROM members 
              LEFT JOIN club_position ON members.role = club_position.id
              WHERE 1"; // ensures safe WHERE chaining


// Prepare conditions for filter
$whereConditions = ["members.role != 100"]; // always exclude superadmin

if ($filter === 'members') {
    $whereConditions[] = "LOWER(club_position.position_name) = 'member'";
} elseif ($filter === 'officers') {
    $officerRoles = ["president", "secretary", "treasurer", "auditor"];
    $officerRolesIn = "'" . implode("','", $officerRoles) . "'";
    $whereConditions[] = "LOWER(club_position.position_name) IN ($officerRolesIn)";
}

if (count($whereConditions) > 0) {
    $baseQuery .= " AND " . implode(" AND ", $whereConditions);
}

$baseQuery .= " ORDER BY members.created_at ASC";

$result = $conn->query($baseQuery);

if (!$result) {
    die("Query failed: " . $conn->error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $membershipAmount = $_POST['membershipAmount'];

    $insertQuery = "INSERT INTO membership_types (type, amount) VALUES ( $membershipAmount)";
    
    if ($conn->query($insertQuery) === TRUE) {
        $successMessage = 'Membership type added successfully!';
    } else {
        echo "Error: " . $insertQuery . "<br>" . $conn->error;
    }
}
?>

<?php include('../../../includes/header.php');?>

<style>
    /* Role badge styles: background behind text only */
    .role-badge {
        padding: 3px 10px;
        border-radius: 12px;
        font-weight: 600;
        color: white;
        font-size: 0.9em;
        display: inline-block;
        min-width: 80px;
        text-align: center;
        user-select: none;
    }
    .role-president {
        background-color: #e74c3c; /* strong red */
    }
    .role-secretary {
        background-color: #27ae60; /* strong green */
    }
    .role-treasurer {
        background-color: #2980b9; /* strong blue */
    }
    .role-auditor {
        background-color: #f39c12; /* strong orange */
    }
    .role-member {
        background-color: #7f8c8d; /* medium gray */
    }

    /* Fallback: all other dynamic roles use member color */
    [class^="role-"]:not(.role-president):not(.role-secretary):not(.role-treasurer):not(.role-auditor):not(.role-member) {
        background-color: #7f8c8d; /* same as member */
    }

    /* Common badge style for all */
    [class^="role-"] {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: bold;
        font-size: 0.85rem;
        color: #fff;
        text-transform: capitalize;
    }

    /* Header flex container */
    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    /* Title on left */
    .card-title {
        flex: 0 0 auto;
    }

    /* Filter wrapper to center */
    .filter-wrapper {
        flex: 1 1 auto;
        display: flex;
        justify-content: center;
    }

    /* Filter form styles */
    .filter-form {
        margin: 0;
        display: flex;
        align-items: center;
    }
    .filter-form label {
        margin-right: 8px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Button on right */
    .add-member-btn {
        flex: 0 0 auto;
    }
</style>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
  <?php include('../../../includes/nav.php');?>
  <?php include('../../../includes/sidebar.php');?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    
    <?php include('../../../includes/page_title.php');?>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">

        <div class="row">
          <div class="col-12">

            <div class="card">

              <div class="card-header">
                <h3 class="card-title">Members DataTable</h3>

                <div class="filter-wrapper">
                  <!-- Filter form -->
                  <form method="GET" class="filter-form">
                    <label for="filter">Filter by Role:</label>
                    <select name="filter" id="filter" class="form-control" onchange="this.form.submit()">
                      <option value="all" <?php if ($filter === 'all') echo 'selected'; ?>>All</option>
                      <option value="members" <?php if ($filter === 'members') echo 'selected'; ?>>Members</option>
                      <option value="officers" <?php if ($filter === 'officers') echo 'selected'; ?>>Officers</option>
                    </select>
                    <noscript><input type="submit" value="Filter" class="btn btn-primary ml-2"></noscript>
                  </form>
                </div>

                <?php if ($_SESSION['role'] === '3' || $_SESSION['role'] === '100'): ?>
                  <a href="/rotary/webpages/club-members/add-member/add_member.php" class="btn btn-success add-member-btn"><i class="fas fa-plus"></i> Add Members</a>
                <?php endif; ?>
              </div>

              <div class="card-body">

                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                      <tr>
                          <th>#</th>
                          <th>Fullname</th>
                          <th>Contact</th>
                          <th>Email</th>
                          <th>Address</th>
                          <th>Role</th>
                          <th>Joined Date</th>
                          <th>Status</th>
                          <th>Actions</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php
                      while ($row = $result->fetch_assoc()) {
                          
                          $expiryDate = strtotime($row['expiry_date']);
                          $currentDate = time();
                          $daysDifference = floor(($expiryDate - $currentDate) / (60 * 60 * 24));

                          $membershipStatus = ($daysDifference < 0) ? 'Expired' : 'Active';

                          // Determine the role badge class based on position_name (case-insensitive)
                          $roleClass = '';
                          $roleName = strtolower(trim($row['position_name'] ?? ''));

                          if ($roleName === 'president') {
                              $roleClass = 'role-president';
                          } elseif ($roleName === 'secretary') {
                              $roleClass = 'role-secretary';
                          } elseif ($roleName === 'treasurer') {
                              $roleClass = 'role-treasurer';
                          } elseif ($roleName === 'auditor') {
                              $roleClass = 'role-auditor';
                          } elseif ($roleName === 'member') {
                              $roleClass = 'role-member';
                          }

                          echo "<tr>";
                          echo "<td>" . htmlspecialchars($row['membership_number']) . "</td>";
                          echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                          echo "<td>" . htmlspecialchars($row['contact_number']) . "</td>";
                          echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                          echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                          echo "<td><span class='role-badge $roleClass'>" . htmlspecialchars($row['position_name']) . "</span></td>";

                          $rawDate = $row['created_at'];
                          $formattedDate = date("F j, Y | h:ia", strtotime($rawDate));
                          echo "<td data-order='$rawDate'>$formattedDate</td>";

                          echo "<td>" . $membershipStatus . "</td>";

                          echo "<td>";
                          echo "<a href='/rotary/webpages/club-members/manage-members/member_profile.php?id={$row['id']}' class='btn btn-info' title='View Profile'><i class='fas fa-id-card'></i></a> ";

                          if ($_SESSION['role'] === '3' || $_SESSION['role'] === '1' || $_SESSION['role'] === '100') {
                              echo "<a href='/rotary/webpages/club-members/manage-members/edit_member.php?id={$row['id']}' class='btn btn-primary' title='Edit Member'><i class='fas fa-edit'></i></a> ";
                              echo "<button class='btn btn-danger' onclick='confirmDelete({$row['id']})' title='Delete Member'><i class='fas fa-trash'></i></button>";
                          }

                          echo "</td>";

                          echo "</tr>";
                      }
                      ?>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->

            </div>
            <!-- /.card -->

          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
        
      </div><!--/. container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer">
  
    <div class="float-right d-none d-sm-inline-block">
      <b>Developed By</b>  Group 9</a>
    </div>
  </footer>
  
</div>
<!-- ./wrapper -->

<?php include('../../../includes/footer.php');?>
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
function confirmDelete(memberId) {
    const deleteUrl = `/rotary/webpages/club-members/manage-members/delete_member.php?id=${memberId}`;
    $('#confirmDeleteBtn').attr('href', deleteUrl);
    $('#deleteConfirmModal').modal('show');
}
</script>


<script>
  $(function () {
    $("#example1").DataTable({
      "responsive": true,
      "autoWidth": false,
      "order": [[6, "desc"]] // Joined Date descending
    });
  });

  function deleteMember(id) {
      if (confirm("Are you sure you want to delete this member?")) {
          window.location.href = 'delete_member.php?id=' + id;
      }
  }
</script>

</body>
</html>
