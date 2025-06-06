<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

if ($_SESSION['role'] !== '1' && $_SESSION['role'] !== '4'&& $_SESSION['role'] !== '100' ) {
    header("Location: /rotary/dashboard.php");
    exit();
}

$response = array('success' => false, 'message' => '');

$edit_id = $_GET['id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $member_id = $_POST['member_id'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $purpose = $_POST['purpose'];
    $reference_number = $_POST['reference_number'];
    $remarks = $_POST['remarks'];
    $transaction_date = $_POST['transaction_date'];
    $edit_id = $_POST['edit_id'];
    $encoded_by = $_SESSION['user_id']; // <-- get the user ID

    $updateQuery = "UPDATE club_transactions SET 
                        member_id = $member_id, 
                        amount = $amount, 
                        payment_method = '$payment_method',
                        purpose = '$purpose',
                        reference_number = '$reference_number',
                        remarks = '$remarks',
                        transaction_date = '$transaction_date',
                             encoded_by = '$encoded_by'
                    
                    WHERE id = $edit_id";

    if ($conn->query($updateQuery) === TRUE) {
        $response['success'] = true;
        $response['message'] = 'Transaction updated successfully!';
    } else {
        $response['message'] = 'Error: ' . $conn->error;
    }
}

$editQuery = "SELECT * FROM club_transactions WHERE id = $edit_id";
$result = $conn->query($editQuery);
$editData = $result->fetch_assoc();

$membersResult = $conn->query("SELECT id, fullname FROM members ORDER BY fullname");
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

                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-edit"></i> Edit Transaction</h3>
                            </div>

                            <form method="post" action="">
                                <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">

                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="member_id">Member</label>
                                        <select class="form-control" name="member_id" required>
                                            <option value="">Select Member</option>
                                            <?php while ($member = $membersResult->fetch_assoc()): ?>
                                                <option value="<?= $member['id'] ?>" <?= $member['id'] == $editData['member_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($member['fullname']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="amount">Amount</label>
                                        <input type="number" step="0.01" class="form-control" name="amount" value="<?= $editData['amount'] ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="payment_method">Payment Method</label>
                                        <input type="text" class="form-control" name="payment_method" value="<?= htmlspecialchars($editData['payment_method']) ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="purpose">Purpose</label>
                                        <input type="text" class="form-control" name="purpose" value="<?= htmlspecialchars($editData['purpose']) ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="reference_number">Reference Number</label>
                                        <input type="text" class="form-control" name="reference_number" value="<?= htmlspecialchars($editData['reference_number']) ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="remarks">Remarks</label>
                                        <textarea class="form-control" name="remarks"><?= htmlspecialchars($editData['remarks']) ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="transaction_date">Transaction Date</label>
                                        <input type="datetime-local" class="form-control" name="transaction_date" value="<?= date('Y-m-d\TH:i', strtotime($editData['transaction_date'])) ?>" required>
                                    </div>
                                </div>

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                    
                                    <a href="/rotary/webpages/club-transactions/manage-transactions/manage_transactions.php" class="btn btn-success float-right">
                                        <i class="fas fa-plus"></i> View Transactions
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include('../../../includes/footer.php'); ?>
</div>
</body>
</html>
