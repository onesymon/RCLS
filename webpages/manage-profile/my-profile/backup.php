<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

$response = array('success' => false, 'message' => '');

$memberId = $_SESSION['user_id'];

$fetchMemberQuery = "SELECT * FROM members WHERE id = $memberId";
$fetchMemberResult = $conn->query($fetchMemberQuery);

if ($fetchMemberResult->num_rows > 0) {
    $memberDetails = $fetchMemberResult->fetch_assoc();
} else {
    header("Location: members_list.php");
    exit();
}

function generateUniqueFileName($filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $basename = pathinfo($filename, PATHINFO_FILENAME);
    return $basename . '_' . time() . '.' . $ext;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $contactNumber = $_POST['contactNumber'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $occupation = $_POST['occupation'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    $updatePassword = false;
    $passwordError = '';

    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        $getPasswordQuery = "SELECT password FROM members WHERE id = $memberId";
        $getPasswordResult = $conn->query($getPasswordQuery);
        $storedHashedPassword = ($getPasswordResult->num_rows > 0) ? $getPasswordResult->fetch_assoc()['password'] : '';

        if (!password_verify($currentPassword, $storedHashedPassword)) {
            $passwordError = 'Current password is incorrect.';
        } elseif ($newPassword !== $confirmPassword) {
            $passwordError = 'New password and confirmation do not match.';
        } else {
            $updatePassword = true;
            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        }
    }

    if (empty($passwordError)) {
        $dobDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($dobDate)->y;

        if ($age < 18) {
            $response['message'] = 'You must be at least 18 years old.';
        } else {
            $photoUpdate = "";
            $uploadedPhoto = $_FILES['photo'];

            if (!empty($uploadedPhoto['name'])) {
                $uniquePhotoName = generateUniqueFileName($uploadedPhoto['name']);
                move_uploaded_file($uploadedPhoto['tmp_name'], 'uploads/member_photos/' . $uniquePhotoName);
                $photoUpdate = ", photo='$uniquePhotoName'";
                $_SESSION['photo'] = $uniquePhotoName;
            }

            $updateQuery = "UPDATE members SET 
                fullname='$fullname', 
                dob='$dob', 
                gender='$gender', 
                contact_number='$contactNumber', 
                email='$email', 
                address='$address',  
                occupation='$occupation'";

            if ($updatePassword) {
                $updateQuery .= ", password='$hashedNewPassword'";
            }

            $updateQuery .= " $photoUpdate WHERE id = $memberId";

            if ($conn->query($updateQuery) === TRUE) {
                $response['success'] = true;
                $response['message'] = 'Member updated successfully!';
                header("Location: /rotary/webpages/manage-profile/edit-profile/edit_profile.php");
                exit();
            } else {
                $response['message'] = 'Error updating member: ' . $conn->error;
            }
        }
    } else {
        $response['message'] = $passwordError;
    }
}
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
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <h5><i class="icon fas fa-check"></i> Success</h5>
                                <?php echo $response['message']; ?>
                            </div>
                        <?php elseif (!empty($response['message'])): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <h5><i class="icon fas fa-ban"></i> Error</h5>
                                <?php echo $response['message']; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Client-side Validation Alert -->
                        <div id="clientValidationAlert" class="alert alert-danger alert-dismissible" style="display: none;">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h5><i class="icon fas fa-ban"></i> Error</h5>
                            <span id="clientValidationMessage"></span>
                        </div>

                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-user-edit"></i> Edit Member Profile</h3>
                            </div>

                            <form id="editProfileForm" method="post" action="" enctype="multipart/form-data">
                                <div class="card-body">
                                    <h5 class="text-primary"><i class="fas fa-id-card"></i> Personal Information</h5>
                                    <div class="form-group">
                                        <label for="fullname">Full Name</label>
                                        <input type="text" class="form-control" id="fullname" name="fullname" required value="<?php echo $memberDetails['fullname']; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="dob">Date of Birth</label>
                                        <input type="date" class="form-control" id="dob" name="dob" required value="<?php echo $memberDetails['dob']; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="gender">Gender</label>
                                        <select class="form-control" id="gender" name="gender" required>
                                            <option value="Male" <?php echo ($memberDetails['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo ($memberDetails['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                            <option value="Other" <?php echo ($memberDetails['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>

                                    <hr>

                                    <h5 class="text-primary"><i class="fas fa-phone-alt"></i> Contact Information</h5>
                                    <div class="form-group">
                                        <label for="contactNumber">Contact Number</label>
                                        <input type="tel" class="form-control" id="contactNumber" name="contactNumber"
                                               pattern="^09\d{9}$" maxlength="11" inputmode="numeric"
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                               required value="<?php echo $memberDetails['contact_number']; ?>">
                                        <small class="form-text text-muted">Must start with 09 and be exactly 11 digits.</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo $memberDetails['email']; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="address">Home Address</label>
                                        <input type="text" class="form-control" id="address" name="address" required value="<?php echo $memberDetails['address']; ?>">
                                    </div>

                                    <hr>

                                    <h5 class="text-primary"><i class="fas fa-briefcase"></i> Occupation & Photo</h5>
                                    <div class="form-group">
                                        <label for="occupation">Occupation</label>
                                        <input type="text" class="form-control" id="occupation" name="occupation" required value="<?php echo $memberDetails['occupation']; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="photo">Profile Photo</label>
                                        <input type="file" class="form-control-file" id="photo" name="photo" accept="image/*" onchange="previewPhoto(event)">
                                        <small class="text-muted">Leave blank if you don’t want to change the photo.</small><br>
                                        <img id="photoPreview" src="/rotary/uploads/member_photos/<?php echo $memberDetails['photo']; ?>" alt="Current Photo" style="max-width: 150px; margin-top: 10px;">
                                    </div>

                                    <hr>

                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <h5 class="text-primary mb-0"><i class="fas fa-lock"></i> Change Password (Optional)</h5>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="collapse" data-target="#passwordCollapse">Toggle</button>
                                    </div>
                                    <div id="passwordCollapse" class="collapse">
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label for="currentPassword">Current Password</label>
                                                <input type="password" class="form-control" id="currentPassword" name="currentPassword">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="newPassword">New Password</label>
                                                <input type="password" class="form-control" id="newPassword" name="newPassword">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="confirmPassword">Confirm Password</label>
                                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>&copy; <?php echo date('Y'); ?> codeastro.com</strong> - All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Developed By</b> <a href="https://codeastro.com/">Group 9</a>
        </div>
    </footer>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-primary">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-circle"></i> Confirm Update</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">Are you sure you want to update your profile information?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmSubmit">Yes, Update</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function isAtLeast18YearsOld(dobValue) {
        const dob = new Date(dobValue);
        const today = new Date();
        const age = today.getFullYear() - dob.getFullYear();
        const month = today.getMonth() - dob.getMonth();
        return (age > 18 || (age === 18 && (month > 0 || (month === 0 && today.getDate() >= dob.getDate()))));
    }

    function showClientValidationError(message) {
        $('#clientValidationMessage').text(message);
        $('#clientValidationAlert').fadeIn();
    }

    function previewPhoto(event) {
        const reader = new FileReader();
        reader.onload = function () {
            const output = document.getElementById('photoPreview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    $(document).ready(function () {
        const form = $('#editProfileForm');

        form.on('submit', function (e) {
            const dobValue = $('#dob').val();
            const contact = $('#contactNumber').val();
            const phoneRegex = /^09\d{9}$/;

            $('#clientValidationAlert').hide();

            if (!isAtLeast18YearsOld(dobValue)) {
                e.preventDefault();
                showClientValidationError("You must be at least 18 years old.");
                return;
            }

            if (!phoneRegex.test(contact)) {
                e.preventDefault();
                showClientValidationError("Contact number must start with 09 and be exactly 11 digits.");
                return;
            }

            if (!form.data('confirmed')) {
                e.preventDefault();
                $('#confirmModal').modal('show');
            }
        });

        $('#confirmSubmit').on('click', function () {
            $('#confirmModal').modal('hide');
            $('#editProfileForm').data('confirmed', true).submit();
        });
    });
</script>

<?php include('../../../includes/footer.php'); ?>
</body>
</html>
