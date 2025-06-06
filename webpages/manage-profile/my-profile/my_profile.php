<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

$memberId = $_SESSION['user_id'];
$memberDetails = [];

$fetchMemberQuery = "SELECT * FROM members WHERE id = $memberId";
$fetchMemberResult = $conn->query($fetchMemberQuery);

if ($fetchMemberResult->num_rows > 0) {
    $memberDetails = $fetchMemberResult->fetch_assoc();
} else {
    header("Location: members_list.php");
    exit();
}

$officerHistory = $conn->query("SELECT cp.position_name, m.created_at FROM club_position cp JOIN members m ON cp.id = m.role WHERE m.id = $memberId");

$attendanceRecords = $conn->query("SELECT ca.*, 
    CASE 
        WHEN ca.category = 'Club Project' THEN cp.title 
        WHEN ca.category = 'Club Event' THEN ce.title 
        ELSE 'Unknown Activity' 
    END AS activity_title,
    ca.attendance_date
FROM club_attendances ca
LEFT JOIN club_projects cp ON ca.category = 'Club Project' AND ca.activity_id = cp.id
LEFT JOIN club_events ce ON ca.category = 'Club Event' AND ca.activity_id = ce.id
WHERE ca.member_id = $memberId
ORDER BY ca.attendance_date DESC
LIMIT 5");
?>

<?php include('../../../includes/header.php'); ?>
<link rel="stylesheet" href="/rotary/webpages/manage-profile/my-profile/style.css">
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
    <?php include('../../../includes/nav.php'); ?>
    <?php include('../../../includes/sidebar.php'); ?>

    <div class="content-wrapper">
        <?php include('../../../includes/page_title.php'); ?>

        <section class="content">
            <div class="container-fluid">

                <!-- Profile Overview -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0"><i class="fas fa-user-circle mr-2"></i> My Profile</h3>
                        <div class="ml-auto">
                            <button class="btn btn-light btn-sm" data-toggle="modal" data-target="#editProfileModal">
                                <i class="fas fa-edit mr-1"></i> Edit Profile
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center mb-3">
                                <img src="/rotary/uploads/member_photos/<?php echo $memberDetails['photo']; ?>" class="img-thumbnail rounded-circle" style="width: 200px; height: 200px; object-fit: cover;">
                                <h4 class="mt-3 font-weight-bold text-primary"><?php echo $memberDetails['fullname']; ?></h4>
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-sm-6 mb-2"><strong>Date of Birth:</strong> <?php echo $memberDetails['dob']; ?></div>
                                    <div class="col-sm-6 mb-2"><strong>Gender:</strong> <?php echo $memberDetails['gender']; ?></div>
                                    <div class="col-sm-6 mb-2"><strong>Contact Number:</strong> <?php echo $memberDetails['contact_number']; ?></div>
                                    <div class="col-sm-6 mb-2"><strong>Email:</strong> <?php echo $memberDetails['email']; ?></div>
                                    <div class="col-sm-12 mb-2"><strong>Address:</strong> <?php echo $memberDetails['address']; ?></div>
                                    <div class="col-sm-12 mb-2"><strong>Occupation:</strong> <?php echo $memberDetails['occupation']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="row">

                    <!-- Officer History -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-info text-white"><i class="fas fa-briefcase mr-2"></i> Officer History</div>
                            <div class="card-body member-card-list officer-history-card">
                                <?php if ($officerHistory->num_rows > 0): ?>
                                    <?php while ($hist = $officerHistory->fetch_assoc()): ?>
                                        <div class="profile-card">
                                            <h6 class="mb-1">👔 <?php echo $hist['position_name']; ?></h6>
                                            <small class="text-muted">Started on <?php echo date('F d, Y', strtotime($hist['created_at'])); ?></small>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted">No officer history found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Past Achievements -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-success text-white"><i class="fas fa-trophy mr-2"></i> Past Achievements</div>
                            <div class="card-body member-card-list achievements-card">
                                <div class="profile-card">
                                    <p class="text-muted mb-0 text-center">🏅 No awards or achievements connected yet.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-warning text-dark"><i class="fas fa-calendar-check mr-2"></i> Attendance</div>
                            <div class="card-body member-card-list attendance-card">
                                <?php if ($attendanceRecords->num_rows > 0): ?>
                                    <?php while ($att = $attendanceRecords->fetch_assoc()): ?>
                                        <div class="profile-card">
                                            <h6 class="mb-1">📅 <?php echo $att['activity_title']; ?></h6>
                                            <small class="badge badge-secondary"><?php echo $att['category']; ?></small><br>
                                            <small class="text-muted"><?php echo date('M d, Y', strtotime($att['attendance_date'])); ?></small>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted">No attendance records found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                <?php include('edit_profile_modal.php'); ?>

            </div>
        </section>
    </div>

    <?php include('../../../includes/footer.php'); ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>