<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

$memberId = $_SESSION['member_id'] ?? null;
$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = (float)str_replace(',', '', $_POST['amount']);
    $paymentMethod = $_POST['payment_method'] ?? '';
    $category = $_POST['category'] ?? '';
    $activityId = $_POST['activity_id'] ?? null;
    $customActivity = trim($_POST['custom_activity'] ?? '');
    $entryType = $_POST['entry_type'] ?? '';
    $remarks = trim($_POST['remarks'] ?? '');
    $referenceNumber = trim($_POST['reference_number'] ?? '');
    $encodedBy = $_SESSION['user_id'];

    if (!$amount || !$paymentMethod || !$entryType || !$category) {
        $response['message'] = 'Please fill out all required fields.';
    } elseif ($category === 'Other Purpose' && empty($customActivity)) {
        $response['message'] = 'Please specify the purpose for this transaction.';
    } else {
        if (empty($referenceNumber)) {
            $prefix = 'REF';
            $methodQuery = $conn->prepare("SELECT method_name FROM payment_method WHERE id = ?");
            $methodQuery->bind_param("i", $paymentMethod);
            $methodQuery->execute();
            $result = $methodQuery->get_result();
            if ($row = $result->fetch_assoc()) {
                switch (strtolower($row['method_name'])) {
                    case 'cash': $prefix = 'CSH'; break;
                    case 'gcash': $prefix = 'GC'; break;
                    case 'maya': $prefix = 'MY'; break;
                    case 'bank transfer': $prefix = 'BT'; break;
                }
            }
            $referenceNumber = "$prefix-" . mt_rand(10000000, 99999999);
        }

        $finalActivityId = ($category === 'Other Purpose') ? null : ($activityId ?: null);
        $finalRemarks = ($category === 'Other Purpose')
            ? "[Other Purpose: $customActivity]" . ($remarks ? " - $remarks" : '')
            : $remarks;

        $paymentStatus = 'Pending';

        $stmt = $conn->prepare("INSERT INTO club_transactions 
            (member_id, amount, payment_method, category, activity_id, remarks, reference_number, encoded_by, entry_type, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("idissssiss", $memberId, $amount, $paymentMethod, $category, $finalActivityId, $finalRemarks, $referenceNumber, $encodedBy, $entryType, $paymentStatus);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Transaction successfully added! It will be reviewed shortly.';
            $_POST = [];
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
    }
}
?>

<?php include('../../../includes/header.php'); ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
<?php include('../../../includes/nav.php'); ?>
<?php include('../../../includes/sidebar.php'); ?>

<div class="content-wrapper">
<?php include('../../../includes/page_title.php'); ?>

<section class="content">
<div class="container-fluid">

<?php if ($response['success']): ?>
  <div class="alert alert-success"><?= htmlspecialchars($response['message']) ?></div>
<?php elseif (!empty($response['message'])): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($response['message']) ?></div>
<?php endif; ?>

<div class="row">
  <div class="col-lg-9">

    <!-- Toggle Button -->
    <div class="mb-3">
      <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#transactionForm" aria-expanded="false" aria-controls="transactionForm" id="toggleFormBtn">
        <span id="btnText">Add Personal Transaction</span>
      </button>
    </div>

    <!-- Toggleable Form -->
    <div class="collapse" id="transactionForm">
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">Personal Transaction Form</h3>
        </div>
      <form id="editProfileForm" method="post" action="" enctype="multipart/form-data">

          <div class="card-body">
            <div class="form-group">
              <label for="entry_type">Is this money for Contribution or Dues?</label>
              <select class="form-control" name="entry_type" required>
                <option value="">Select Category</option>
                <option value="Contribution">Contribution</option>
                <option value="Income">Dues</option>
              </select>
            </div>

            <div class="form-group">
              <label for="category">What is this transaction related to?</label>
              <select class="form-control" name="category" id="category" required>
                <option value="">Choose Category</option>
                <option value="Club Project">Club Project</option>
                <option value="Club Event">Club Event</option>
                <option value="Club Fund">Club Wallet</option>

              </select>
            </div>

            <div class="form-group" id="activity_container" style="display: none;">
              <label id="activity_label" for="activity_id">Choose Related Activity</label>
              <select class="form-control" name="activity_id" id="activity_id">
                <option value="">Select Activity</option>
              </select>
            </div>

            <div class="form-group d-none" id="custom_activity_container">
              <label for="custom_activity">Enter the Purpose</label>
              <input type="text" class="form-control" name="custom_activity" id="custom_activity" placeholder="e.g. Donation, Fundraiser" />
            </div>

            <div class="form-group">
              <label for="payment_method">How did you pay?</label>
              <select class="form-control" name="payment_method" required>
                <option value="">Select Method</option>
                <?php
                $result = $conn->query("SELECT id, method_name FROM payment_method ORDER BY method_name ASC");
                while ($row = $result->fetch_assoc()) {
                    if (strtolower($row['method_name']) === 'cash') continue;
                    echo "<option value='{$row['id']}'>{$row['method_name']}</option>";
                }
                ?>
              </select>
            </div>

            <div class="form-group">
              <label for="amount">How much money?</label>
              <input type="text" class="form-control" name="amount" id="amount" required placeholder="Enter amount in PHP">
            </div>

            <div class="form-group">
              <label for="reference_number">Reference Number (optional)</label>
              <input type="text" class="form-control" name="reference_number" placeholder="Auto-generated if left blank">
            </div>

            <div class="form-group">
              <label for="remarks">Additional Notes (optional)</label>
              <input type="text" class="form-control" name="remarks" placeholder="e.g. For Project ABC">
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-primary">Submit Transaction</button>
            <a href="/rotary/webpages/club-transactions/manage-transactions/manage_transactions.php" class="btn btn-success float-right">
              <i class="fas fa-eye"></i> View Transactions
            </a>
          </div>
        </form>
      </div>
    </div>

<!-- Modern Membership Dues + Contribution Breakdown Section -->
<div class="card mt-4 shadow-lg rounded">
  <div class="card-header bg-primary text-white">
    <h3 class="card-title">My Financial Contributions Overview</h3>
  </div>
  <div class="card-body">
    <?php
    // Fetch total contributions grouped by category
    $breakdownQuery = "
      SELECT category, SUM(amount) AS total 
      FROM club_transactions 
      WHERE member_id = ? AND entry_type = 'Contribution' AND payment_status = 'Paid' 
      GROUP BY category
    ";
    $stmt = $conn->prepare($breakdownQuery);
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();

    $breakdown = [
      'Club Project' => 0,
      'Club Event' => 0,
      'Club Fund' => 0,
      'Other Purpose' => 0
    ];
    $grandTotal = 0;

    while ($row = $result->fetch_assoc()) {
      $category = $row['category'];
      $amount = (float)$row['total'];
      if (isset($breakdown[$category])) {
        $breakdown[$category] += $amount;
      } else {
        $breakdown['Other Purpose'] += $amount;
      }
      $grandTotal += $amount;
    }
    ?>

    <div class="row">
      <!-- Membership Dues Card -->
      <div class="col-md-6">
        <div class="card border-info mb-3">
          <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Membership Dues</h5>
          </div>
          <div class="card-body">
            <p>₱20,000 per quarter × 4 quarters</p>
            <p><strong>Total Annual Dues:</strong> ₱80,000</p>
            <p class="mb-2"><small>These dues fund core operations and are expected every 3 months.</small></p>
            <ul class="list-group">
              <li class="list-group-item d-flex justify-content-between align-items-center">
                Quarter 1 <span class="badge badge-pill badge-primary">₱20,000</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                Quarter 2 <span class="badge badge-pill badge-primary">₱20,000</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                Quarter 3 <span class="badge badge-pill badge-primary">₱20,000</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                Quarter 4 <span class="badge badge-pill badge-primary">₱20,000</span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Total Contribution Card -->
      <div class="col-md-6">
        <div class="card border-success mb-3">
          <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0">Total Contributions</h5>
          </div>
          <div class="card-body">
            <h4 class="text-success">₱<?php echo number_format($grandTotal, 2); ?></h4>
            <p class="mb-0"><small>This is the total of all your paid contributions.</small></p>
          </div>
        </div>
      </div>
    </div>

    <hr>

    <!-- Detailed Breakdown -->
    <h5 class="mb-3">Contribution Breakdown by Category</h5>
    <ul class="list-group">
      <li class="list-group-item d-flex justify-content-between align-items-center">
        Club Projects
        <span class="badge badge-primary badge-pill">₱<?php echo number_format($breakdown['Club Project'], 2); ?></span>
      </li>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        Club Events
        <span class="badge badge-info badge-pill">₱<?php echo number_format($breakdown['Club Event'], 2); ?></span>
      </li>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        Club Fund
        <span class="badge badge-warning badge-pill">₱<?php echo number_format($breakdown['Club Fund'], 2); ?></span>
      </li>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        Other Purposes
        <span class="badge badge-secondary badge-pill">₱<?php echo number_format($breakdown['Other Purpose'], 2); ?></span>
      </li>
    </ul>
  </div>
</div>

    <div class="card mt-4">
      <div class="card-header">
        <h3 class="card-title">My Transaction History</h3>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-bordered table-hover table-striped">
          <thead class="thead-light">
            <tr>
              <th>Date</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Purpose</th>
              <th>Activity</th>
              <th>Payment Method</th>
              <th>Reference No.</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $query = "SELECT ct.*, 
                        pm.method_name, 
                        COALESCE(cp.title, ce.title, cw.fund_name, '') AS activity_title
                      FROM club_transactions ct
                      LEFT JOIN payment_method pm ON ct.payment_method = pm.id
                      LEFT JOIN club_projects cp ON ct.activity_id = cp.id AND ct.category = 'Club Project'
                      LEFT JOIN club_events ce ON ct.activity_id = ce.id AND ct.category = 'Club Event'
                      LEFT JOIN club_wallet_categories cw ON ct.activity_id = cw.id AND ct.category = 'Club Fund'
                      WHERE ct.member_id = ?
                      ORDER BY ct.transaction_date DESC";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $memberId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . date("M d, Y", strtotime($row['transaction_date'])) . "</td>";
                echo "<td>" . htmlspecialchars($row['entry_type']) . "</td>";
                echo "<td>₱" . number_format($row['amount'], 2) . "</td>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                echo "<td>" . htmlspecialchars($row['activity_title'] ?? '-') . "</td>";
                echo "<td>" . htmlspecialchars($row['method_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['reference_number']) . "</td>";
                echo "<td>" . htmlspecialchars($row['payment_status']) . "</td>";
                echo '<td><a href="/rotary/webpages/club-transactions/manage-transactions/view_receipt.php?id=' . $row['id'] . '" class="btn btn-info btn-sm" title="View Receipt"><i class="fas fa-receipt"></i></a></td>';
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='9' class='text-center'>No transactions found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
  
  <!-- Attendance List on Right Side -->
<div class="col-lg-3">
  <div class="card shadow-sm border-left-primary h-100">
    <div class="card-header bg-info text-white">
      <h5 class="card-title mb-0">My Attendance Record</h5>
    </div>
    <div class="card-body p-3" style="max-height: calc(100vh - 250px); overflow-y: auto;">
      <?php
      $attendanceQuery = "
        SELECT ca.*, 
               COALESCE(cp.title, ce.title, '') AS activity_title
        FROM club_attendances ca
        LEFT JOIN club_projects cp ON ca.activity_id = cp.id AND ca.category = 'Club Project'
        LEFT JOIN club_events ce ON ca.activity_id = ce.id AND ca.category = 'Club Event'
        WHERE ca.member_id = ?
        ORDER BY ca.attendance_date DESC
      ";
      $stmt = $conn->prepare($attendanceQuery);
      $stmt->bind_param("i", $memberId);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
        echo '<ul class="list-group list-group-flush">';
        while ($row = $result->fetch_assoc()) {
          $activity = htmlspecialchars($row['activity_title'] ?? 'N/A');
          $category = htmlspecialchars($row['category']);
          $date = date("M d, Y", strtotime($row['attendance_date']));
          $status = htmlspecialchars($row['status']);
          $badge = match($status) {
            'Present' => 'badge-success',
            'Excused' => 'badge-warning',
            'Absent' => 'badge-danger',
            default => 'badge-secondary'
          };

          echo "
            <li class='list-group-item'>
              <div class='d-flex justify-content-between align-items-start'>
                <div>
                  <strong>{$activity}</strong><br>
                  <small class='text-muted'>{$category} • {$date}</small>
                </div>
                <span class='badge {$badge} badge-pill'>{$status}</span>
              </div>
            </li>
          ";
        }
        echo '</ul>';
      } else {
        echo "<p class='text-muted'>You have no attendance records yet.</p>";
      }
      ?>
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
        Are you sure you want to update your profile information?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmSubmit">Yes, Update</button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript Dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
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

<!-- JavaScript for dynamic form behavior -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  const category = document.getElementById("category");
  const activityContainer = document.getElementById("activity_container");
  const customContainer = document.getElementById("custom_activity_container");
  const activityLabel = document.getElementById("activity_label");
  const activitySelect = document.getElementById("activity_id");

  category.addEventListener("change", function () {
    const val = this.value;

    if (val === "Other Purpose") {
      activityContainer.style.display = "none";
      customContainer.classList.remove("d-none");
    } else if (["Club Project", "Club Event", "Club Fund"].includes(val)) {
      activityContainer.style.display = "block";
      customContainer.classList.add("d-none");
      activityLabel.textContent = `Choose ${val}`;
      fetch("/rotary/includes/get_activities.php?category=" + encodeURIComponent(val))
        .then(res => res.json())
        .then(data => {
          activitySelect.innerHTML = '<option value="">Select Activity</option>';
          data.forEach(item => {
            const opt = document.createElement("option");
            opt.value = item.id;
            opt.textContent = item.title;
            activitySelect.appendChild(opt);
          });
        });
    } else {
      activityContainer.style.display = "none";
      customContainer.classList.add("d-none");
    }
  });

  // Toggle button label
  const toggleBtn = document.getElementById('toggleFormBtn');
  const toggleText = document.getElementById('btnText');
  $('#transactionForm').on('shown.bs.collapse', function () {
    toggleText.textContent = "Hide Personal Transaction";
  });
  $('#transactionForm').on('hidden.bs.collapse', function () {
    toggleText.textContent = "Add Personal Transaction";
  });

  // Format amount input
  const amountInput = document.getElementById('amount');
  amountInput.addEventListener('input', function() {
    let value = this.value.replace(/[^0-9.]/g, ''); // Remove non-numeric characters except dots
    this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ','); // Add commas for formatting
  });
});
</script>

</body>
</html>
