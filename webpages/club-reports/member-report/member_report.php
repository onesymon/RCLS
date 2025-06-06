<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

if ($_SESSION['role'] !== '1' && $_SESSION['role'] !== '3' && $_SESSION['role'] !== '4' && $_SESSION['role'] !== '100') {
    header("Location: /rotary/dashboard.php");
    exit();
}

$members = [];
$officers = [];
$regularMembers = [];

$sql = "SELECT * FROM members ORDER BY fullname ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    if (in_array($row['role'], ['3', '4', '100'])) {
        $officers[] = $row;
    } else {
        $regularMembers[] = $row;
    }
}

$loggedInUserId = $_SESSION['user_id'];
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
                <div class="d-flex justify-content-end align-items-center mb-3 flex-wrap gap-2">
                    <div class="btn-group flex-wrap" role="group" aria-label="Action buttons" style="gap: 10px;">
                        <button id="toggleSelectBtn" class="btn btn-primary btn-sm" onclick="toggleSelectAll()">Select All Members</button>
                        <button id="sendReportBtn" class="btn btn-success btn-sm" onclick="sendReport()" title="Send Report">
                            <i class="fas fa-paper-plane"></i> Send Report
                        </button>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Member List Panel -->
                    <div class="col-md-3">
                        <div class="card card-outline card-primary h-100 shadow-sm">
                            <div class="card-header py-2">
                                <h4 class="card-title mb-0" style="font-size: 1.1rem; font-weight: 600;">Member List</h4>
                            </div>
                            <div class="card-body overflow-auto" style="max-height: 650px; padding: 0.5rem 1rem;" id="memberList">
                                <form id="memberForm" class="mb-0" aria-label="Member selection form">
                                    <div class="mb-3">
                                        <strong class="d-block mb-2 text-secondary" style="font-size: 0.95rem; letter-spacing: 0.03em;">Officers</strong>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($officers as $member): ?>
                                                <label class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2 rounded mb-1 shadow-sm bg-white">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input member-checkbox" type="checkbox" name="members[]" value="<?= $member['id'] ?>" onchange="onMemberCheckboxChange()" <?= $member['id'] == $loggedInUserId ? 'checked' : '' ?> id="memberCheckbox<?= $member['id'] ?>" style="margin-top: 0.1rem;">
                                                        <label for="memberCheckbox<?= $member['id'] ?>" class="form-check-label mb-0 ms-2" style="font-size: 0.9rem;"><?= htmlspecialchars($member['fullname']) ?></label>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-primary btn-sm btn-view-report py-0 px-2" id="viewBtn<?= $member['id'] ?>" onclick="toggleViewMemberReport(<?= $member['id'] ?>)">View</button>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <div class="mb-0">
                                        <strong class="d-block mb-2 text-secondary" style="font-size: 0.95rem; letter-spacing: 0.03em;">Members</strong>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($regularMembers as $member): ?>
                                                <label class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2 rounded mb-1 shadow-sm bg-white">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input member-checkbox" type="checkbox" name="members[]" value="<?= $member['id'] ?>" onchange="onMemberCheckboxChange()" <?= $member['id'] == $loggedInUserId ? 'checked' : '' ?> id="memberCheckbox<?= $member['id'] ?>" style="margin-top: 0.1rem;">
                                                        <label for="memberCheckbox<?= $member['id'] ?>" class="form-check-label mb-0 ms-2" style="font-size: 0.9rem;"><?= htmlspecialchars($member['fullname']) ?></label>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-primary btn-sm btn-view-report py-0 px-2" id="viewBtn<?= $member['id'] ?>" onclick="toggleViewMemberReport(<?= $member['id'] ?>)">View</button>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Report Content Panel -->
                    <div class="col-md-9">
                        <div class="report-panel bg-white rounded shadow-sm p-4" style="min-height: 650px; border-left: 1px solid #ddd;" id="reportContent" tabindex="0" aria-live="polite" aria-label="Membership report content">
                            <p>Loading your membership report...</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline-block">
            <b>Developed By</b> <a href="https://codeastro.com/">Group 9</a>
        </div>
    </footer>
</div>

<?php include('../../../includes/footer.php'); ?>

<script>
let allSelected = false;
const loggedInUserId = <?= json_encode($loggedInUserId) ?>;
let isViewingSingleMember = false;
let currentlyViewedMemberId = null;

function toggleSelectAll() {
    if (isViewingSingleMember) return;
    allSelected = !allSelected;
    document.querySelectorAll('.member-checkbox').forEach(cb => cb.checked = allSelected);
    document.getElementById('toggleSelectBtn').innerText = allSelected ? "Unselect All Members" : "Select All Members";
}

function onMemberCheckboxChange() {
    if (isViewingSingleMember) return;
    const checkboxes = Array.from(document.querySelectorAll('.member-checkbox'));
    const checkedCount = checkboxes.filter(cb => cb.checked).length;
    allSelected = checkedCount === checkboxes.length;
    document.getElementById('toggleSelectBtn').innerText = allSelected ? "Unselect All Members" : "Select All Members";
    generateReports(getSelectedMemberIds());
}

function getSelectedMemberIds() {
    if (isViewingSingleMember) return [currentlyViewedMemberId];
    const selectedCheckboxes = Array.from(document.querySelectorAll('.member-checkbox:checked'));
    if (selectedCheckboxes.length === 0) return [loggedInUserId];
    return selectedCheckboxes.map(cb => parseInt(cb.value));
}

function sendReport() {
    alert('Send Report functionality is not implemented yet.');
}

function toggleViewMemberReport(memberId) {
    const btn = document.getElementById('viewBtn' + memberId);
    if (isViewingSingleMember && currentlyViewedMemberId === memberId) {
        unviewReport();
        return;
    }
    isViewingSingleMember = true;
    currentlyViewedMemberId = memberId;
    document.querySelectorAll('.member-checkbox').forEach(cb => cb.disabled = true);
    document.getElementById('toggleSelectBtn').disabled = true;
    document.getElementById('sendReportBtn').disabled = false;
    document.querySelectorAll('.btn-view-report').forEach(b => b.innerText = "View");
    btn.innerText = "Unview";
    generateReports([memberId]);
}

function unviewReport() {
    isViewingSingleMember = false;
    currentlyViewedMemberId = null;
    document.querySelectorAll('.member-checkbox').forEach(cb => {
        cb.disabled = false;
        cb.checked = cb.value == loggedInUserId;
    });
    document.getElementById('toggleSelectBtn').disabled = false;
    document.getElementById('toggleSelectBtn').innerText = "Select All Members";
    allSelected = false;
    document.querySelectorAll('.btn-view-report').forEach(b => b.innerText = "View");
    generateReports([loggedInUserId]);
}

function generateReports(memberIds = null) {
    const selected = memberIds ?? getSelectedMemberIds();
    document.getElementById('reportContent').innerHTML = `<p class="text-muted">Loading report...</p>`;
    fetch('/rotary/webpages/club-reports/member-report/generate_member_report.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ member_ids: selected })
    })
    .then(res => res.text())
    .then(html => {
        document.getElementById('reportContent').innerHTML = html;
    })
    .catch(() => {
        document.getElementById('reportContent').innerHTML = '<p class="text-danger">Failed to load report. Please try again.</p>';
    });
}

window.addEventListener('DOMContentLoaded', () => {
    generateReports([loggedInUserId]);
});
</script>
</body>
</html>
