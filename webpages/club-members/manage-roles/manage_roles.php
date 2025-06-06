<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['1', '100'])) {
    header("Location: /rotary/dashboard.php");
    exit();
}

$successMessage = $errorMessage = '';

// Handle Add Role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addRole'])) {
    $newRole = trim($_POST['position_name']);
    if (!empty($newRole)) {
        $check = $conn->prepare("SELECT id FROM club_position WHERE LOWER(position_name) = LOWER(?)");
        $check->bind_param("s", $newRole);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errorMessage = 'This role already exists.';
        } else {
            $stmt = $conn->prepare("INSERT INTO club_position (position_name) VALUES (?)");
            $stmt->bind_param("s", $newRole);
            $stmt->execute();
            $successMessage = 'New role added successfully.';
            $stmt->close();
        }
        $check->close();
    } else {
        $errorMessage = 'Please enter a valid role name.';
    }
}

// Handle Edit Role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editRole'])) {
    $roleId = intval($_POST['role_id']);
    $newName = trim($_POST['edit_position_name']);
    $newMember = intval($_POST['edit_assigned_member']);

    if ($roleId && $newName) {
        $conn->begin_transaction();

        try {
            // Update role name
            $stmt = $conn->prepare("UPDATE club_position SET position_name = ? WHERE id = ?");
            $stmt->bind_param("s", $newName, $roleId);
            $stmt->execute();
            $stmt->close();

            // Remove from previous holders
            $conn->query("UPDATE members SET role = NULL WHERE role = $roleId");

            // Assign to new member
            if ($newMember) {
                $stmt = $conn->prepare("UPDATE members SET role = ? WHERE id = ?");
                $stmt->bind_param("ii", $roleId, $newMember);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();
            $successMessage = 'Role updated successfully.';
        } catch (Exception $e) {
            $conn->rollback();
            $errorMessage = 'Error updating role: ' . $e->getMessage();
        }
    } else {
        $errorMessage = 'Missing role name or member.';
    }
}

// Handle Delete Role
if (isset($_GET['delete_role_id'])) {
    $roleId = intval($_GET['delete_role_id']);

    $check = $conn->prepare("SELECT COUNT(*) FROM members WHERE role = ?");
    $check->bind_param("i", $roleId);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();

    if ($count > 0) {
        $errorMessage = 'Cannot delete: This role is still assigned to one or more members.';
    } else {
        $delete = $conn->prepare("DELETE FROM club_position WHERE id = ?");
        $delete->bind_param("i", $roleId);
        $delete->execute();
        $successMessage = 'Role deleted successfully.';
        $delete->close();
    }
}

// Load officer roles only (exclude Member ID = 6 and Super Admin ID = 100)
$roles = $conn->query("SELECT * FROM club_position WHERE id NOT IN (6, 100) ORDER BY id ASC");

// Members
$members = [];
$res = $conn->query("SELECT id, fullname FROM members ORDER BY fullname ASC");
while ($row = $res->fetch_assoc()) $members[$row['id']] = $row['fullname'];

// Current assignments
$assigned = [];
$res = $conn->query("SELECT id, role FROM members WHERE role IS NOT NULL");
while ($row = $res->fetch_assoc()) $assigned[$row['role']] = $row['id'];

include('../../../includes/header.php');
?>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
<?php include('../../../includes/nav.php'); ?>
<?php include('../../../includes/sidebar.php'); ?>
<div class="content-wrapper">
<?php include('../../../includes/page_title.php'); ?>

<section class="content">
<div class="container-fluid">
<?php if ($successMessage) echo "<div class='alert alert-success'>$successMessage</div>"; ?>
<?php if ($errorMessage) echo "<div class='alert alert-danger'>$errorMessage</div>"; ?>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-tag"></i> Manage Roles / Positions</h3>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="form-group">
                <label>Add New Role:</label>
                <input type="text" name="position_name" class="form-control" required>
                <input type="hidden" name="addRole" value="1">
            </div>
            <button type="submit" class="btn btn-primary">Add Role</button>
        </form>

        <hr>
        <h5>Officer Roles:</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead><tr><th>Role Name</th><th>Assigned Member</th><th>Actions</th></tr></thead>
                <tbody>
                <?php while ($role = $roles->fetch_assoc()):
                    $roleId = $role['id'];
                    $currentMember = $assigned[$roleId] ?? null;
                ?>
                <tr>
                    <td><?= htmlspecialchars($role['position_name']) ?></td>
                    <td><?= $currentMember ? htmlspecialchars($members[$currentMember]) : 'Unassigned' ?></td>
                    <td>
                        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#editModal<?= $roleId ?>"><i class="fas fa-edit"></i> Edit</button>
                        <a href="?delete_role_id=<?= $roleId ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>

                <!-- Modal -->
                <div class="modal fade" id="editModal<?= $roleId ?>" tabindex="-1" role="dialog">
                  <div class="modal-dialog" role="document">
                    <form method="post">
                    <input type="hidden" name="editRole" value="1">
                    <input type="hidden" name="role_id" value="<?= $roleId ?>">
                    <div class="modal-content">
                      <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Edit Role - <?= htmlspecialchars($role['position_name']) ?></h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                      </div>
                      <div class="modal-body">
                        <div class="form-group">
                            <label>Role Name</label>
                            <input type="text" name="edit_position_name" value="<?= htmlspecialchars($role['position_name']) ?>" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Assign to Member</label>
                            <select name="edit_assigned_member" class="form-control" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($members as $mid => $mname): ?>
                                    <option value="<?= $mid ?>" <?= ($mid == $currentMember) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mname) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                      </div>
                    </div>
                    </form>
                  </div>
                </div>

                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
</section>
</div>
<?php include('../../../includes/footer.php'); ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
