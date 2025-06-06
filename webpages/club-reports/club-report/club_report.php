<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /rotary/webpages/logout/login.php");
    exit();
}

// Block members from accessing officer-only pages
if (!in_array($_SESSION['role'], ['1', '3', '4', '100'])) {
    header("Location: /rotary/dashboard.php");
    exit();
}

$reportType = '';
$reportPeriod = '';
$displayReport = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportType = $_POST['reportType'] ?? '';
    $reportPeriod = $_POST['reportPeriod'] ?? '';
    $displayReport = true;

    $_SESSION['club_report_type'] = $reportType;
    $_SESSION['club_report_period'] = $reportPeriod;
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}
?>

<?php include('../../../includes/header.php'); ?>
<style>
    .report-section, .card-body {
        margin-top: 20px;
        font-size: 0.9rem;
    }
    .report-section h4 {
        margin: 20px 0 10px;
        font-size: 1rem;
        font-weight: 600;
        color: #007bff;
        border-bottom: 1px solid #007bff;
        padding-bottom: 5px;
    }
    table.report-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    table.report-table th, table.report-table td {
        border: 1px solid #ccc;
        padding: 5px 8px;
    }
    table.report-table th {
        background-color: #007bff;
        color: white;
    }
    .summary-section {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .summary-card {
        flex: 1;
        background: #f0f2f5;
        border-left: 4px solid #007bff;
        padding: 10px;
        font-weight: 600;
    }
    .summary-breakdown ul {
        padding-left: 20px;
        margin: 5px 0 0 0;
        list-style: disc;
    }
    .summary-breakdown li {
        font-weight: normal;
    }
    @media print {
        form, .generate-pdf-btn, .print-btn {
            display: none;
        }
    }
</style>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
    <?php include('../../../includes/nav.php'); ?>
    <?php include('../../../includes/sidebar.php'); ?>

    <div class="content-wrapper">
        <?php include('../../../includes/page_title.php'); ?>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title"><i class="fas fa-file-alt"></i> Club Report Generator</h3>
                    </div>

                    <form method="post" id="reportForm" class="card-body p-3">
                        <div class="form-row align-items-end">
                            <div class="form-group col-md-4">
                                <label for="reportType">Report Type</label>
                                <select class="form-control" name="reportType" id="reportType" required>
                                    <option value="">Select Type</option>
                                    <option value="monthly" <?= $reportType === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                    <option value="quarterly" <?= $reportType === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                                    <option value="yearly" <?= $reportType === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                                </select>
                            </div>
                            <div class="form-group col-md-5" id="periodInputContainer"></div>
                            <div class="form-group col-md-3">
                                <button type="submit" class="btn btn-success btn-block"><i class="fas fa-chart-line"></i> Generate</button>
                            </div>
                        </div>

                        <?php if ($displayReport) : ?>
                            <div class="text-right">
                                <a href="generate_club_report_pdf.php" target="_blank" class="btn btn-secondary generate-pdf-btn"><i class="fas fa-file-pdf"></i> PDF</a>
                                <button type="button" onclick="window.print()" class="btn btn-info print-btn"><i class="fas fa-print"></i> Print</button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>

                <?php if ($displayReport) : ?>
                    <div class="report-section card card-outline card-primary">
                        <div class="card-body">
                            <h4>Club Report - <?= ucfirst($reportType) ?> (<?= htmlspecialchars($reportPeriod) ?>)</h4>
                            <p><strong>Report period:</strong> <?= htmlspecialchars($reportPeriod) ?></p>

                            <!-- Projects -->
                            <h4>Projects</h4>
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Name</th><th>Status</th><th>Start</th><th>End</th><th>Budget</th><th>Funding</th><th>Remaining</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>Clean Water Initiative</td><td>Ongoing</td><td>2025-01-15</td><td>2025-06-30</td><td><?= formatCurrency(100000) ?></td><td><?= formatCurrency(75000) ?></td><td><?= formatCurrency(25000) ?></td></tr>
                                    <tr><td>Youth Education Program</td><td>Completed</td><td>2024-07-01</td><td>2024-12-15</td><td><?= formatCurrency(50000) ?></td><td><?= formatCurrency(50000) ?></td><td><?= formatCurrency(0) ?></td></tr>
                                </tbody>
                            </table>

                            <!-- Events -->
                            <h4>Events</h4>
                            <table class="report-table">
                                <thead>
                                    <tr><th>Name</th><th>Date</th><th>Location</th><th>Attendees</th><th>Expenses</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Annual Gala</td><td>2025-03-20</td><td>Main Hall</td><td>150</td><td><?= formatCurrency(20000) ?></td></tr>
                                    <tr><td>Community Outreach</td><td>2025-04-10</td><td>City Park</td><td>75</td><td><?= formatCurrency(5000) ?></td></tr>
                                </tbody>
                            </table>

                            <!-- Wallet -->
                            <h4>Wallet Transactions</h4>
                            <table class="report-table">
                                <thead><tr><th>Date</th><th>Category</th><th>Type</th><th>Description</th><th>Amount</th></tr></thead>
                                <tbody>
                                    <tr><td>2025-02-01</td><td>Donations</td><td>Income</td><td>Donation from ABC Corp</td><td><?= formatCurrency(15000) ?></td></tr>
                                    <tr><td>2025-02-15</td><td>Membership Dues</td><td>Income</td><td>Monthly dues</td><td><?= formatCurrency(8000) ?></td></tr>
                                    <tr><td>2025-03-05</td><td>Operations</td><td>Expense</td><td>Office Supplies</td><td><?= formatCurrency(3000) ?></td></tr>
                                </tbody>
                            </table>

                            <!-- Donations -->
                            <h4>Donations & Sponsorships</h4>
                            <table class="report-table">
                                <thead><tr><th>Source</th><th>Type</th><th>Amount</th></tr></thead>
                                <tbody>
                                    <tr><td>ABC Corp</td><td>Corporate</td><td><?= formatCurrency(15000) ?></td></tr>
                                    <tr><td>XYZ Foundation</td><td>Sponsorship</td><td><?= formatCurrency(10000) ?></td></tr>
                                    <tr><td>John Doe</td><td>Individual</td><td><?= formatCurrency(5000) ?></td></tr>
                                </tbody>
                            </table>

                            <!-- Membership -->
                            <h4>Membership Dues</h4>
                            <table class="report-table">
                                <thead><tr><th>Month</th><th>Paid</th><th>Amount</th></tr></thead>
                                <tbody>
                                    <tr><td>January 2025</td><td>50</td><td><?= formatCurrency(50000) ?></td></tr>
                                    <tr><td>February 2025</td><td>47</td><td><?= formatCurrency(47000) ?></td></tr>
                                </tbody>
                            </table>

                            <!-- Summary -->
                            <div class="summary-section">
                                <div class="summary-card">
                                    Overall Income: <?= formatCurrency($totalIncome = 15000 + 8000 + 15000 + 10000 + 5000 + 50000 + 47000) ?>
                                    <div class="summary-breakdown">
                                        <ul>
                                            <li>Donations: <?= formatCurrency(15000 + 15000 + 5000) ?></li>
                                            <li>Membership Dues: <?= formatCurrency(8000 + 50000 + 47000) ?></li>
                                            <li>Sponsorship: <?= formatCurrency(10000) ?></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="summary-card">
                                    Overall Expense: <?= formatCurrency($totalExpense = 3000 + 20000 + 5000) ?>
                                    <div class="summary-breakdown">
                                        <ul>
                                            <li>Office Supplies: <?= formatCurrency(3000) ?></li>
                                            <li>Events: <?= formatCurrency(20000 + 5000) ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endif; ?>
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
    function createPeriodInput(type, selectedValue = '') {
        const container = document.getElementById('periodInputContainer');
        container.innerHTML = '';

        if (type === 'monthly') {
            const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            let html = '<label for="reportPeriod">Select Month</label><select class="form-control" name="reportPeriod" id="reportPeriod" required>';
            html += '<option value="">Select Month</option>';
            months.forEach((month, idx) => {
                const val = (idx + 1).toString().padStart(2, '0') + '-2025';
                html += `<option value="${val}" ${selectedValue === val ? 'selected' : ''}>${month} 2025</option>`;
            });
            html += '</select>';
            container.innerHTML = html;
        } else if (type === 'quarterly') {
            const quarters = [
                { label: 'Jan - Mar 2025', value: 'Q1-2025' },
                { label: 'Apr - Jun 2025', value: 'Q2-2025' },
                { label: 'Jul - Sep 2025', value: 'Q3-2025' },
                { label: 'Oct - Dec 2025', value: 'Q4-2025' },
            ];
            let html = '<label for="reportPeriod">Select Quarter</label><select class="form-control" name="reportPeriod" id="reportPeriod" required>';
            html += '<option value="">Select Quarter</option>';
            quarters.forEach(q => {
                html += `<option value="${q.value}" ${selectedValue === q.value ? 'selected' : ''}>${q.label}</option>`;
            });
            html += '</select>';
            container.innerHTML = html;
        } else if (type === 'yearly') {
            const years = ['2024', '2025', '2026'];
            let html = '<label for="reportPeriod">Select Year</label><select class="form-control" name="reportPeriod" id="reportPeriod" required>';
            html += '<option value="">Select Year</option>';
            years.forEach(year => {
                html += `<option value="${year}" ${selectedValue === year ? 'selected' : ''}>${year}</option>`;
            });
            html += '</select>';
            container.innerHTML = html;
        }
    }

    window.onload = function() {
        const selectedType = '<?= $reportType ?>';
        const selectedPeriod = '<?= $reportPeriod ?>';
        if (selectedType) {
            createPeriodInput(selectedType, selectedPeriod);
        }
    };

    document.getElementById('reportType').addEventListener('change', function() {
        createPeriodInput(this.value);
    });
</script>
</body>
</html>