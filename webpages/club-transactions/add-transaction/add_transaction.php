<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

if ($_SESSION['role'] !== '1' && $_SESSION['role'] !== '4' && $_SESSION['role'] !== '100') {
    header("Location: /rotary/dashboard.php");
    exit();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sourceType = $_POST['source_type'] ?? null;
    $memberId = ($sourceType === 'Member') ? ($_POST['member_id'] ?? null) : null;
    $externalSource = ($sourceType === 'Officer') ? 'Club Officer' : (($sourceType === 'External') ? trim($_POST['external_source'] ?? '') : null);
    $fundWalletId = ($sourceType === 'Fund Wallet') ? ($_POST['fund_wallet_id'] ?? null) : null;

    $amount = isset($_POST['amount']) ? (float) str_replace(',', '', $_POST['amount']) : null;
    $paymentMethod = $_POST['payment_method'] ?? null;
    $category = $_POST['category'] ?? null;
    $activityId = $_POST['activity_id'] ?? null;
    $customActivity = $_POST['custom_activity'] ?? null;
    $entryType = $_POST['entry_type'] ?? 'Income';
    $remarks = $_POST['remarks'] ?? '';
    $referenceNumber = trim($_POST['reference_number'] ?? '');
    $encodedBy = $_SESSION['user_id'];

    // ✅ VALIDATION SECTION FIXED
    if (!$amount || !$paymentMethod || !$entryType || !$sourceType || !$category) {
        $response['message'] = 'Please fill all required fields.';
    } elseif ($sourceType === 'External' && empty($externalSource)) {
        $response['message'] = 'Please enter a source name for external transactions.';
    } elseif ($sourceType === 'Fund Wallet' && empty($fundWalletId)) {
        $response['message'] = 'Please select a fund wallet.';
    } elseif ($category === 'Other Purpose' && empty($customActivity)) {
        $response['message'] = 'Please specify the other purpose.';
    } elseif (!in_array($category, ['Club Fund', 'Other Purpose']) && empty($activityId)) {
        $response['message'] = 'Missing activity selection. Please go back and choose one.';
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

        $stmt = $conn->prepare("INSERT INTO club_transactions 
            (member_id, external_source, amount, payment_method, category, activity_id, remarks, reference_number, encoded_by, entry_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $finalActivityId = ($category === 'Other Purpose') ? null : $activityId;
        $finalRemarks = $remarks;
        if ($category === 'Other Purpose') {
            $finalRemarks = "[Other Purpose: $customActivity]" . ($remarks ? " - $remarks" : '');
        }

        $stmt->bind_param("isdssissss", $memberId, $externalSource, $amount, $paymentMethod, $category, $finalActivityId, $finalRemarks, $referenceNumber, $encodedBy, $entryType);

        if ($stmt->execute()) {
            $transactionId = $stmt->insert_id;

            // ✅ Club Project update logic when source is Fund Wallet
            if ($category === 'Club Project' && $sourceType === 'Fund Wallet') {
                $stmt = $conn->prepare("UPDATE club_wallet_categories SET current_balance = current_balance - ? WHERE id = ?");
                $stmt->bind_param("di", $amount, $fundWalletId);
                $stmt->execute();

                $stmt2 = $conn->prepare("SELECT current_funding, target_funding FROM club_projects WHERE id = ?");
                $stmt2->bind_param("i", $activityId);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                if ($row = $result2->fetch_assoc()) {
                    $newFunding = $row['current_funding'] + $amount;
                    $remaining = max(0, $row['target_funding'] - $newFunding);
                    $stmt3 = $conn->prepare("UPDATE club_projects SET current_funding = ?, remaining_funding = ? WHERE id = ?");
                    $stmt3->bind_param("ddi", $newFunding, $remaining, $activityId);
                    $stmt3->execute();
                }
            }

            // ✅ Club Fund logic — track wallet balance and insert to wallet transactions
            if ($category === 'Club Fund') {
                $transactionType = ($entryType === 'Expense') ? 'withdrawal' : 'deposit';
                $balanceSQL = ($transactionType === 'withdrawal')
                    ? "UPDATE club_wallet_categories SET current_balance = current_balance - ? WHERE id = ?"
                    : "UPDATE club_wallet_categories SET current_balance = current_balance + ? WHERE id = ?";
                $stmt4 = $conn->prepare($balanceSQL);
                $stmt4->bind_param("di", $amount, $activityId);
                $stmt4->execute();

<<<<<<< HEAD
                
=======
                $stmt5 = $conn->prepare("INSERT INTO club_wallet_transactions 
                    (fund_id, transaction_type, amount, remarks, member_id, reference_id, encoded_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt5->bind_param("ssdssii", $activityId, $transactionType, $amount, $remarks, $memberId, $transactionId, $encodedBy);
                $stmt5->execute();
>>>>>>> 0be913085bee1194fc22a9f58d90293ee6be9cde
                
// ✅ Now sync the wallet balance
$updateBalanceQuery = "
    UPDATE club_wallet_categories
    SET current_balance = (
        SELECT COALESCE(SUM(
            CASE 
                WHEN transaction_type = 'deposit' THEN amount
                WHEN transaction_type = 'withdrawal' THEN -amount
                ELSE 0
            END
        ), 0)
        FROM club_wallet_transactions
        WHERE fund_id = ?
    )
    WHERE id = ?
";
$updateStmt = $conn->prepare($updateBalanceQuery);
$updateStmt->bind_param("ii", $fund_id, $fund_id);
$updateStmt->execute();
            }

            $response['success'] = true;
            $response['message'] = 'Transaction added successfully!';
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
    }
}


?>


<?php include('../../../includes/header.php'); ?>
<link rel="stylesheet" href="/rotary/webpages/club-transactions/add-transaction/style.css?v=<?php echo time(); ?>">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
  <?php include('../../../includes/nav.php'); ?>
  <?php include('../../../includes/sidebar.php'); ?>

  <div class="content-wrapper">
    <?php include('../../../includes/page_title.php'); ?>
    <section class="content">
      <div class="container-fluid">
        <?php if ($response['success']): ?>
          <div class="alert alert-success"><?php echo $response['message']; ?></div>
        <?php elseif (!empty($response['message'])): ?>
          <div class="alert alert-danger"><?php echo $response['message']; ?></div>
        <?php endif; ?>

        <div class="card card-primary card-outline shadow">
          <div class="card-header bg-primary text-white">
            <h3 class="card-title font-weight-bold">Create Club Transaction</h3>
          </div>

          <div class="card-body d-md-flex gap-4">
            <ul class="list-group progress-tracker" id="step-tracker">
              <li class="tracker-step active" data-step="step-1" data-goto="step-1">
                <div class="step-dot"><i class="fas fa-user"></i></div>
                <span>1. Source</span>
              </li>
              <li class="tracker-step" data-step="step-2" data-goto="step-2">
                <div class="step-dot"><i class="fas fa-money-bill-wave"></i></div>
                <span>2. Payment Method</span>
              </li>
              <li class="tracker-step" data-step="step-3" data-goto="step-3">
                <div class="step-dot"><i class="fas fa-exchange-alt"></i></div>
                <span>3. Entry Type</span>
              </li>
              <li class="tracker-step" data-step="step-4" data-goto="step-4">
                <div class="step-dot"><i class="fas fa-list-check"></i></div>
                <span>4. Category</span>
              </li>
              <li class="tracker-step" data-step="step-5" data-goto="step-5">
                <div class="step-dot"><i class="fas fa-coins"></i></div>
                <span>5. Amount</span>
              </li>
              <li class="tracker-step" data-step="step-6" data-goto="step-6">
                <div class="step-dot"><i class="fas fa-eye"></i></div>
                <span>6. Review</span>
              </li>
            </ul>

            <!-- Step Container -->
            <form id="editProfileForm" class="flex-fill" method="post">
              <div id="step-container">

                <!-- STEP 1: Source -->
                <div class="step step-1">
                  <label class="h5 d-block font-weight-bold mb-3 text-center">Who is this transaction from?</label>
                  <div class="tile-wrapper">
                    <button type="button" class="tile-card" data-next="step-1a" data-value="Member" data-name="source_type">Member</button>
                    <button type="button" class="tile-card" data-next="step-1c" data-value="Officer" data-name="source_type">Officer</button>
                  </div>
                </div>

                <!-- STEP 1A -->
                <div class="step step-1a d-none">
                  <label class="h5 d-block font-weight-bold mb-3 text-center">Member Source Options</label>
                  <div class="tile-wrapper">
                    <button type="button" class="tile-card" data-next="step-1a" data-value="Scan" data-name="member_mode">Scan Membership Card (Coming Soon)</button>
                    <button type="button" class="tile-card" data-next="step-1b" data-value="Search" data-name="member_mode">Search Member</button>
                  </div>
                  <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2 goBackBtn">Go Back</button>
                  </div>
                </div>

                <!-- STEP 1B -->
                <div class="step step-1b d-none">
                  <label class="h5 d-block font-weight-bold mb-3 text-center">Search Member</label>
                  <div class="d-flex justify-content-center">
                    <input type="text" class="pos-input mb-3" id="search_value" placeholder="Search by name or ID">
                    <input type="hidden" name="member_id" id="member_id">
                  </div>
                  <div class="d-flex justify-content-center">
                    <div id="suggestions" class="list-group w-100" style="max-width: 600px;"></div>
                  </div>
                    <div class="text-center mt-4">
                      <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2 goBackBtn">Go Back</button>
                      <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2 ml-3 proceed" data-next="step-2">Continue</button>
                    </div>
                </div>

                <!-- STEP 1C: Officer -->
                <div class="step step-1c d-none">
                  <label class="h5 font-weight-bold d-block text-center mb-3">Officer Source Type</label>
                  <div class="tile-wrapper">
                    <button type="button" class="tile-card" data-next="step-1c-wallet" data-value="Fund Wallet" data-name="source_type">Club Wallet</button>
                    <button type="button" class="tile-card" data-next="step-1c-external" data-value="External" data-name="source_type">External Source</button>
                  </div>
                  <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2 goBackBtn">Go Back</button>
                  </div>
                </div>

                <!-- STEP 1C Wallet -->
                <div class="step step-1c-wallet d-none">
                  <label class="h5 font-weight-bold d-block text-center mb-3">Select Club Wallet</label>
                  <div class="tile-wrapper">
                    <?php
                    $funds = $conn->query("SELECT id, fund_name FROM club_wallet_categories ORDER BY fund_name ASC");
                    while ($f = $funds->fetch_assoc()) {
                      echo "<button type='button' class='tile-card' data-next='step-2' data-value='{$f['id']}' data-name='fund_wallet_id'>{$f['fund_name']}</button>";
                    }
                    ?>
                  </div>
                  <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2 goBackBtn">Go Back</button>
                  </div>
                </div>

                <!-- STEP 1C External -->
                <div class="step step-1c-external d-none">
                  <label class="h5 font-weight-bold mb-2 text-center d-block">Enter External Source Name</label>
                  <div class="d-flex justify-content-center">
                    <input type="text" class="pos-input mb-3" name="external_source" id="external_source" placeholder="External Source Name">
                  </div>
                  <div class="text-center mt-4">
                    <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2 goBackBtn">Go Back</button>
                    <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2 ml-3 proceed" data-next="step-2" data-name="external_source_dynamic">Continue</button>
                  </div>
                </div>

                <!-- STEP 2 -->
                <div class="step step-2 d-none">
                  <label class="h5 font-weight-bold d-block text-center mb-3">How was the payment made?</label>
                  <div class="tile-wrapper">
                    <?php
                    $methods = ['Cash', 'GCash', 'Bank Transfer', 'Maya'];
                    $result = $conn->query("SELECT id, method_name FROM payment_method");
                    while ($row = $result->fetch_assoc()) {
                      if (in_array(strtolower($row['method_name']), array_map('strtolower', $methods))) {
                        echo "<button type='button' class='tile-card' data-next='step-3' data-value='{$row['id']}' data-name='payment_method'>{$row['method_name']}</button>";
                      }
                    }
                    ?>
                  </div>
                  <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2 goBackBtn">Go Back</button>
                  </div>
                </div>

                <!-- STEP 3 -->
                <div class="step step-3 d-none">
                  <label class="h5 font-weight-bold text-center d-block mb-3">Is this money coming in or going out?</label>
                  <div class="tile-wrapper" id="entry-type-options"></div>
                  <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2 goBackBtn">Go Back</button>
                  </div>
                </div>

                <!-- STEP 4 -->
                <div class="step step-4 d-none">
                  <label class="h5 font-weight-bold text-center d-block mb-3">What is this transaction for?</label>
                  <div class="tile-wrapper" id="category-options"></div>
                  <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2 goBackBtn">Go Back</button>
                  </div>
                </div>

                <!-- STEP 4A -->
                <div class="step step-4a d-none">
                  <label class="h5 font-weight-bold d-block text-center mb-3" id="activity_label">Select Activity</label>
                  <div id="activity-card-container" class="tile-wrapper"></div>
                  <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2 goBackBtn">Go Back</button>
                  </div>
                </div>

                <!-- STEP 5 -->
                <div class="step step-5 d-none">
                  <label class="h5 font-weight-bold d-block text-center mb-3">Enter Amount Details</label>
                  <div class="d-flex justify-content-center flex-column align-items-center">
                    <input type="text" class="pos-input mb-2" name="amount" id="amount" placeholder="Amount (₱)" required>
                    <input type="text" class="pos-input mb-2" name="reference_number" placeholder="Reference Number (Optional)">
                    <input type="text" class="pos-input mb-2" name="remarks" placeholder="Additional Notes (Optional)">
                  </div>
                  <div class="text-center mt-4">
                    <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2 goBackBtn">Go Back</button>
                    <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2 ml-3 proceed" data-next="step-6">Continue</button>
                  </div>
                </div>

                <!-- STEP 6 -->
                <div class="step step-6 d-none">
                  <label class="h5 font-weight-bold d-block text-center mb-3">Review and Submit</label>
                  <div id="review-summary" class="modern-review-box mb-4"></div>
                  <div class="text-center mt-4">
                    <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2 goBackBtn">Go Back</button>
                    <button type="submit" class="btn btn-primary btn-lg px-4 py-2">Submit Transaction</button>
                  </div>
                </div>
              </div>
            </form>

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
        <h5 class="modal-title" id="confirmModalLabel"><i class="fas fa-exclamation-circle"></i> Confirm Wallet Creation</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to add this transaction?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmSubmit">Yes, Add</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("editProfileForm");
  const steps = document.querySelectorAll(".step");
  const reviewBox = document.getElementById("review-summary");
  const inputs = {};
  const historyStack = {};
  const tracker = document.querySelectorAll("#step-tracker .list-group-item");

  function showStep(stepClass, isBack = false) {
    steps.forEach(s => s.classList.add("d-none"));
    const targetStep = document.querySelector(`.${stepClass}`);
    if (targetStep) {
      targetStep.classList.remove("d-none");
      if (!isBack) historyStack[stepClass] = true;
    }

    updateTracker(stepClass);

    if (stepClass === "step-3") populateEntryTypeOptions();
    if (stepClass === "step-4") populateCategoryOptions();
    if (stepClass === "step-4a") loadActivities(inputs["category"]);
    if (stepClass === "step-6") generateReview();

    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function updateTracker(currentStep) {
    const sectionMap = {
      "step-1": 0, "step-1a": 0, "step-1b": 0, "step-1c": 0,
      "step-1c-wallet": 0, "step-1c-external": 0,
      "step-2": 1,
      "step-3": 2,
      "step-4": 3, "step-4a": 3,
      "step-5": 4,
      "step-6": 5
    };

    const activeIndex = sectionMap[currentStep] ?? -1;
    const allSteps = document.querySelectorAll("#step-tracker .tracker-step");
    allSteps.forEach((step, idx) => {
      step.classList.remove("active", "completed");
      if (idx < activeIndex) step.classList.add("completed");
      if (idx === activeIndex) step.classList.add("active");
    });
  }

  // Allow clicking tracker to go back
  tracker.forEach(item => {
    item.addEventListener("click", function () {
      const gotoStep = this.dataset.goto;
      if (historyStack[gotoStep]) {
        showStep(gotoStep, true);
      }
    });
  });

  // Proceed buttons (tile-card OR normal continue)
  document.querySelectorAll(".proceed").forEach(btn => {
    btn.addEventListener("click", function () {
      const next = this.dataset.next;
      const value = this.dataset.value;
      const name = this.dataset.name;

      if (name && value) {
        inputs[name] = value;
        let hidden = form.querySelector(`[name="${name}"]`);
        if (!hidden) {
          hidden = document.createElement("input");
          hidden.type = "hidden";
          hidden.name = name;
          form.appendChild(hidden);
        }
        hidden.value = value;
      }

      if (next) showStep(next);
    });
  });

  // General tile-card buttons
  document.querySelectorAll(".tile-card").forEach(tile => {
    tile.addEventListener("click", function () {
      const next = this.dataset.next;
      const value = this.dataset.value;
      const name = this.dataset.name;

      if (name === "external_source_dynamic") {
        const extInput = document.getElementById("external_source");
        const extValue = extInput.value.trim();
        if (!extValue) return alert("Please enter a valid external source.");
        inputs["external_source"] = extValue;

        let hidden = form.querySelector('[name="external_source"]');
        if (!hidden) {
          hidden = document.createElement("input");
          hidden.type = "hidden";
          hidden.name = "external_source";
          form.appendChild(hidden);
        }
        hidden.value = extValue;
        showStep(next);
        return;
      }

      if (name && value) {
        inputs[name] = value;
        let hidden = form.querySelector(`[name="${name}"]`);
        if (!hidden) {
          hidden = document.createElement("input");
          hidden.type = "hidden";
          hidden.name = name;
          form.appendChild(hidden);
        }
        hidden.value = value;
      }

      if (value === "Search" && next === "step-1b") {
        showStep("step-1b");
        return;
      }

      if (inputs["member_mode"] === "Scan" && next === "step-2") {
        alert("Waiting to scan a Membership Card... (Feature Placeholder)");
      }

      if (next) showStep(next);
    });
  });

  // Go back buttons
  document.querySelectorAll(".goBackBtn").forEach(btn => {
    btn.addEventListener("click", function () {
      const last = Object.keys(historyStack).pop();
      delete historyStack[last];
      const previous = Object.keys(historyStack).pop();
      if (previous) showStep(previous, true);
    });
  });
function loadActivities(category) {
  const label = document.getElementById("activity_label");
  const container = document.getElementById("activity-card-container");
  label.textContent = `Select ${category}`;
  container.innerHTML = `<div class="text-muted">Loading...</div>`;

  // Special case for Club Fund → load from wallet table
  if (category === "Club Fund") {
    fetch("/rotary/includes/get_wallets.php")
      .then(res => res.json())
      .then(data => {
        container.innerHTML = '';
        if (!data.length) {
          container.innerHTML = '<div class="text-danger">⚠️ No wallets found.</div>';
          return;
        }
        data.forEach(wallet => {
          const card = document.createElement("div");
          card.classList.add("tile-card");
          card.textContent = wallet.fund_name;
          card.dataset.value = wallet.id;
          card.dataset.name = "activity_id";
          card.dataset.next = "step-5";

          card.addEventListener("click", function () {
            inputs["activity_id"] = wallet.id;
            inputs["activity_title"] = wallet.fund_name;

            let hidden = form.querySelector('[name="activity_id"]');
            if (!hidden) {
              hidden = document.createElement("input");
              hidden.type = "hidden";
              hidden.name = "activity_id";
              form.appendChild(hidden);
            }
            hidden.value = wallet.id;
            showStep("step-5");
          });

          container.appendChild(card);
        });
      })
      .catch(err => {
        container.innerHTML = '<div class="text-danger">⚠️ Failed to load wallets.</div>';
        console.error("Wallet fetch error:", err);
      });
  } else {
    // Default: Load from get_activities.php for Projects, Events, etc.
    fetch(`/rotary/includes/get_activities.php?category=${encodeURIComponent(category)}`)
      .then(res => res.json())
      .then(data => {
        container.innerHTML = '';
        data.forEach(item => {
          const card = document.createElement("div");
          card.classList.add("tile-card");
          card.textContent = item.title;
          card.dataset.value = item.id;
          card.dataset.name = "activity_id";
          card.dataset.next = "step-5";

          card.addEventListener("click", function () {
            inputs["activity_id"] = item.id;
            inputs["activity_title"] = item.title;

            let hidden = form.querySelector('[name="activity_id"]');
            if (!hidden) {
              hidden = document.createElement("input");
              hidden.type = "hidden";
              hidden.name = "activity_id";
              form.appendChild(hidden);
            }
            hidden.value = item.id;
            showStep("step-5");
          });

          container.appendChild(card);
        });
      });
  }
}


function generateReview() {
  const reviewData = [];

  if (inputs["source_type"] === "Member") {
    const memberName = document.getElementById("search_value")?.value || "(not selected)";
    reviewData.push({ icon: "fa-user", label: "Member Name", value: memberName });
  } else if (inputs["source_type"] === "Fund Wallet") {
    const walletBtn = document.querySelector(`[data-name="fund_wallet_id"][data-value="${inputs["fund_wallet_id"]}"]`);
    const walletName = walletBtn?.textContent?.trim() || "(not selected)";
    reviewData.push({ icon: "fa-university", label: "Source Name", value: walletName });
  } else if (inputs["source_type"] === "External") {
    reviewData.push({ icon: "fa-globe", label: "Source Name", value: inputs["external_source"] || "(missing)" });
  }

  const methodId = inputs["payment_method"];
  const methodBtn = document.querySelector(`[data-name="payment_method"][data-value="${methodId}"]`);
  reviewData.push({ icon: "fa-money-bill", label: "Payment Method", value: methodBtn?.textContent || methodId });

  reviewData.push({ icon: "fa-right-left", label: "Entry Type", value: inputs["entry_type"] });
  reviewData.push({ icon: "fa-folder", label: "What is this transaction for?", value: inputs["category"] });
  reviewData.push({ icon: "fa-bullseye", label: "Specific Activity", value: inputs["activity_title"] || "(Not Selected)" });

  const amount = document.getElementById("amount")?.value;
  if (amount) reviewData.push({ icon: "fa-peso-sign", label: "Amount", value: `₱${amount}` });

  const ref = document.querySelector('[name="reference_number"]')?.value;
  if (ref?.trim()) reviewData.push({ icon: "fa-hashtag", label: "Reference Number", value: ref });

  const remarks = document.querySelector('[name="remarks"]')?.value;
  if (remarks?.trim()) reviewData.push({ icon: "fa-comment", label: "Additional Notes", value: remarks });

  let html = `<h4 class="text-center font-weight-bold mb-4">🔍 Transaction Summary</h4><div class="modern-review-box">`;
  reviewData.forEach(item => {
    html += `
      <div class="review-item">
        <span class="review-label"><i class="fas ${item.icon} review-icon"></i>${item.label}:</span>
        <span class="review-value">${item.value}</span>
      </div>`;
  });
  html += '</div>';
  document.getElementById("review-summary").innerHTML = html;
}

  function populateEntryTypeOptions() {
    const container = document.getElementById("entry-type-options");
    container.innerHTML = '';
    const sourceType = inputs["source_type"];

    if (sourceType === "Member") {
      const btn = document.createElement("div");
      btn.className = "tile-card proceed";
      btn.dataset.next = "step-4";
      btn.dataset.value = "Contribution";
      btn.dataset.name = "entry_type";
      btn.textContent = "Contribution";
      container.appendChild(btn);
    } else {
      ["Income", "Expense", "Contribution"].forEach(type => {
        const btn = document.createElement("div");
        btn.className = "tile-card proceed";
        btn.dataset.next = "step-4";
        btn.dataset.value = type;
        btn.dataset.name = "entry_type";
        btn.textContent = type === "Income" ? "Money In" :
                          type === "Expense" ? "Money Out" : "Contribution";
        container.appendChild(btn);
      });
    }

    container.querySelectorAll(".tile-card").forEach(tile => {
      tile.addEventListener("click", function () {
        const next = this.dataset.next;
        const value = this.dataset.value;
        const name = this.dataset.name;

        if (name) {
          inputs[name] = value;
          let hidden = form.querySelector(`[name="${name}"]`);
          if (!hidden) {
            hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = name;
            form.appendChild(hidden);
          }
          hidden.value = value;
        }

        showStep(next);
      });
    });
  }
function populateCategoryOptions() {
  const container = document.getElementById("category-options");
  container.innerHTML = '';
  const sourceType = inputs["source_type"];

  const categories = [
    { label: "Club Project", value: "Club Project" },
    { label: "Club Event", value: "Club Event" },
    { label: "Club Wallet", value: "Club Fund" },
    { label: "Club Operation", value: "Club Operation" }
  ];

  categories.forEach(cat => {
    const btn = document.createElement("div");
    btn.className = "tile-card";
    btn.dataset.name = "category";
    btn.dataset.value = cat.value;
    btn.textContent = cat.label;

    btn.addEventListener("click", function () {
      inputs["category"] = cat.value;
      let hidden = form.querySelector('[name="category"]');
      if (!hidden) {
        hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = "category";
        form.appendChild(hidden);
      }
      hidden.value = cat.value;

      // 🛠 Club Fund special case
      if (cat.value === "Club Fund") {
        // Let member choose which wallet it belongs to
     // Load wallets as activities for Club Fund
fetch("/rotary/includes/get_wallets.php")
  .then(res => res.json())
  .then(data => {
    const container = document.getElementById("activity-card-container");
    container.innerHTML = '';
    data.forEach(wallet => {
      const card = document.createElement("div");
      card.classList.add("tile-card");
      card.textContent = wallet.fund_name;
      card.dataset.value = wallet.id;
      card.dataset.name = "activity_id";
      card.dataset.next = "step-5";

      card.addEventListener("click", function () {
        inputs["activity_id"] = wallet.id;
        let hidden = form.querySelector('[name="activity_id"]');
        if (!hidden) {
          hidden = document.createElement("input");
          hidden.type = "hidden";
          hidden.name = "activity_id";
          form.appendChild(hidden);
        }
        hidden.value = wallet.id;
        showStep("step-5");
      });

      container.appendChild(card);
    });
    showStep("step-4a");
  });

      } else {
        showStep("step-4a");
      }
    });

    container.appendChild(btn);
  });
}

  // Member search suggestion
  const searchInput = document.getElementById("search_value");
  const suggestionsBox = document.getElementById("suggestions");
  const memberIdInput = document.getElementById("member_id");

  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const query = this.value.trim();
      if (!query) return suggestionsBox.innerHTML = '';

      fetch("/rotary/includes/member_suggestions.php?query=" + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
          suggestionsBox.innerHTML = '';
          data.forEach(item => {
            const div = document.createElement("div");
            div.classList.add("list-group-item", "list-group-item-action");
            div.textContent = `${item.fullname} (${item.membership_number})`;
            div.onclick = () => {
              searchInput.value = item.fullname;
              memberIdInput.value = item.id;
              suggestionsBox.innerHTML = '';
            };
            suggestionsBox.appendChild(div);
          });
        });
    });
  }

  const amountInput = document.getElementById("amount");
  if (amountInput) {
    amountInput.addEventListener("input", function () {
      let value = this.value.replace(/[^0-9.]/g, '');
      this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    });
  }

  form.addEventListener("submit", function (e) {
    if (!form.dataset.confirmed) {
      e.preventDefault();
      $('#confirmModal').modal('show');
    }
  });

  const confirmBtn = document.getElementById("confirmSubmit");
  if (confirmBtn) {
    confirmBtn.addEventListener("click", function () {
      $('#confirmModal').modal('hide');
      form.dataset.confirmed = true;
      form.submit();
    });
  }

  showStep("step-1");
});
</script>

</body>
</html>