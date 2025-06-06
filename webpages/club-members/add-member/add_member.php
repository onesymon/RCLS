<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if ($_SESSION['role'] !== '1' && $_SESSION['role'] !== '3' && $_SESSION['role'] !== '100') {
    header("Location: /rotary/dashboard.php");
    exit();
}

$response = ['success' => false, 'message' => ''];

function generateUniqueFileName($originalName) {
    $timestamp = time();
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return $timestamp . '_' . uniqid() . '.' . $extension;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $contactNumber = $_POST['contactNumber'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $occupation = $_POST['occupation'];
    $password = $_POST['password'];
    $membershipNumber = 'CA-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

    $dobDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($dobDate)->y;

    if ($age < 18) {
        $response['message'] = 'You must be at least 18 years old.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $response['message'] = 'Password must be at least 8 characters and include uppercase, lowercase, and a number.';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $uniquePhotoName = 'default.jpg';

        if (!empty($_FILES['photo']['name'])) {
            $uploadedPhoto = $_FILES['photo'];
            $uniquePhotoName = generateUniqueFileName($uploadedPhoto['name']);
            move_uploaded_file($uploadedPhoto['tmp_name'], 'uploads/member_photos/' . $uniquePhotoName);
        }
// Check if email already exists
$checkEmailQuery = "SELECT id FROM members WHERE email = ?";
$stmt = $conn->prepare($checkEmailQuery);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $response['message'] = 'This email address is already registered. Please use a different one.';
    $stmt->close();
} else {
    $stmt->close();

        $insertQuery = "INSERT INTO members (fullname, dob, gender, contact_number, email, address, occupation, membership_number, photo, password, created_at) 
                        VALUES ('$fullname', '$dob', '$gender', '$contactNumber', '$email', '$address', '$occupation',
                                '$membershipNumber', '$uniquePhotoName','$hashedPassword', NOW())";

        if ($conn->query($insertQuery) === TRUE) {
            $response['success'] = true;
            $response['message'] = 'Member added successfully! Membership Number: ' . $membershipNumber;
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
    }
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
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h5><i class="icon fas fa-check"></i> Success</h5>
    <?php echo $response['message']; ?>
</div>
<?php elseif (!empty($response['message'])): ?>
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h5><i class="icon fas fa-ban"></i> Error</h5>
    <?php echo $response['message']; ?>
</div>
<?php endif; ?>

<div id="clientValidationAlert" class="alert alert-danger alert-dismissible" style="display: none;">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h5><i class="icon fas fa-ban"></i> Error</h5>
    <span id="clientValidationMessage"></span>
</div>

<div class="card card-primary">
<div class="card-header"><h3 class="card-title">Register New Club Member</h3></div>
<form id="editProfileForm" method="post" action="" enctype="multipart/form-data">
<div class="card-body">

<!-- Personal Info -->
<h5 class="text-primary"><i class="fas fa-id-card"></i> Personal Information</h5>
<div class="form-group">
    <label for="fullname">Full Name</label>
    <input type="text" class="form-control" id="fullname" name="fullname" placeholder="e.g., Juan Dela Cruz" required>
</div>
<div class="form-group">
    <label for="dob">Date of Birth</label>
    <input type="date" class="form-control" id="dob" name="dob" required>
</div>
<div class="form-group">
    <label for="gender">Gender</label>
    <select class="form-control" id="gender" name="gender" required>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Other">Other</option>
    </select>
</div>

<hr>

<!-- Contact Info -->
<h5 class="text-primary"><i class="fas fa-phone-alt"></i> Contact Information</h5>
<div class="form-group">
    <label for="contactNumber">Contact Number</label>
    <input type="tel" class="form-control" id="contactNumber" name="contactNumber" placeholder="e.g. 09123456789" pattern="^09\d{9}$" maxlength="11" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
    <small class="form-text text-muted">Must start with 09 and be exactly 11 digits.</small>
</div>
<div class="form-group">
    <label for="email">Email Address</label>
    <input type="email" class="form-control" id="email" name="email" placeholder="e.g., juan@example.com" required>
</div>
<div class="form-group">
    <label for="address">Address</label>
    <input type="text" class="form-control" id="address" name="address" placeholder="e.g., 123 Main St" required>
</div>

<hr>

<!-- Occupation & Photo -->
<h5 class="text-primary"><i class="fas fa-briefcase"></i> Occupation & Photo</h5>
<div class="form-group">
    <label for="occupation">Occupation</label>
    <input type="text" class="form-control" id="occupation" name="occupation" placeholder="e.g., Engineer" required>
</div>
<div class="form-group">
    <label for="photo">Photo (optional)</label>
    <input type="file" class="form-control" id="photo" name="photo">
</div>

<hr>

<!-- PASSWORD SECTION -->
<h5 class="text-primary"><i class="fas fa-lock"></i> Password</h5>
<div class="form-group position-relative">
    <label for="password">Set a password for the member</label>
    <div class="input-group">
        <input type="password" class="form-control" id="password" name="password" placeholder="Enter a secure password" required>
        <div class="input-group-append">
            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>
    <small class="form-text text-muted">At least 8 characters with uppercase, lowercase, and a number.</small>
</div>

<div class="form-group position-relative">
    <label for="confirmPassword">Confirm password</label>
    <div class="input-group">
        <input type="password" class="form-control" id="confirmPassword" placeholder="Re-enter password" required>
        <div class="input-group-append">
            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#confirmPassword">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>
</div>


<div class="card-footer">
    <button type="submit" class="btn btn-primary">Save Member</button>
    <a href="/rotary/webpages/club-members/manage-members/manage_members.php" class="btn btn-success float-right">
        <i class="fas fa-eye"></i> View Members
    </a>
</div>
</form>
</div>

</div></div></div></section></div>

<footer class="main-footer">
    <div class="float-right d-none d-sm-inline-block">
        <b>Developed By</b> Group 9
    </div>
</footer>
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
        Are you sure you want to add this member?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmSubmit">Yes, Update</button>
      </div>
    </div>
  </div>
</div>

<script>
function isStrongPassword(password) {
    return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(password);
}

function isAtLeast18YearsOld(dobValue) {
    const dob = new Date(dobValue);
    const today = new Date();
    const age = today.getFullYear() - dob.getFullYear();
    const month = today.getMonth() - dob.getMonth();
    return (age > 18 || (age === 18 && (month > 0 || (month === 0 && today.getDate() >= dob.getDate()))));
}

function isValidEmail(email) {
    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i;
    return emailPattern.test(email);
}

function showClientValidationError(message) {
    $('#clientValidationMessage').text(message);
    $('#clientValidationAlert').fadeIn();
}

$(document).ready(function () {
    const form = $('#editProfileForm');

    form.on('submit', function (e) {
        $('#clientValidationAlert').hide();

        const dobValue = $('#dob').val();
        const contact = $('#contactNumber').val();
        const email = $('#email').val();
        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();

        // Age validation
        if (!isAtLeast18YearsOld(dobValue)) {
            e.preventDefault();
            showClientValidationError("You must be at least 18 years old.");
            return;
        }

        // Contact number format
        if (!/^09\d{9}$/.test(contact)) {
            e.preventDefault();
            showClientValidationError("Contact number must start with 09 and be exactly 11 digits.");
            return;
        }

        // Email format
        if (!isValidEmail(email)) {
            e.preventDefault();
            showClientValidationError("Please enter a valid email address.");
            return;
        }

        // Password strength
        if (!isStrongPassword(password)) {
            e.preventDefault();
            showClientValidationError("Password must be at least 8 characters and include uppercase, lowercase, and a number.");
            return;
        }

        // Password match
        if (password !== confirmPassword) {
            e.preventDefault();
            showClientValidationError("Passwords do not match.");
            return;
        }

        // Show confirmation modal
        if (!form.data('confirmed')) {
            e.preventDefault();
            $('#confirmModal').modal('show');
        }
    });

    $('#confirmSubmit').on('click', function () {
        $('#confirmModal').modal('hide');
        $('#editProfileForm').data('confirmed', true).submit();
    });

    // Toggle show/hide password
    $('.toggle-password').on('click', function () {
        const targetInput = $($(this).data('target'));
        const icon = $(this).find('i');
        const type = targetInput.attr('type') === 'password' ? 'text' : 'password';
        targetInput.attr('type', type);
        icon.toggleClass('fa-eye fa-eye-slash');
    });
});
</script>



</body>
</html>
