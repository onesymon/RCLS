<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');

// Currency symbol
$currencySymbol = '₱';
$currencyQuery = "SELECT currency FROM settings WHERE id = 1";
$currencyResult = $conn->query($currencyQuery);
if ($currencyResult->num_rows > 0) {
    $currencySymbol = $currencyResult->fetch_assoc()['currency'];
}

$data = json_decode(file_get_contents("php://input"), true);
$member_ids = $data['member_ids'] ?? [];

$html = '';

foreach ($member_ids as $id) {
    $stmt = $conn->prepare("SELECT m.*, p.position_name FROM members m LEFT JOIN club_position p ON m.role = p.id WHERE m.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();

    if (!$member) continue;

    $html .= "<div class='card mb-4 shadow-sm border-0'>";
    $html .= "<div class='card-header text-center bg-primary text-white py-3 rounded-top'>";
    $html .= "<h3 class='mb-0'>" . htmlspecialchars($member['fullname']) . "</h3>";
    $html .= "<small class='d-block'>Membership Report</small>";
    $html .= "</div>";

    $html .= "<div class='card-body p-4' id='member-report-{$member['id']}'>";

    // Member Info
    $html .= "<div class='row mb-4 text-muted'>";
    $html .= "<div class='col-md-6'><i class='fas fa-envelope mr-2'></i><strong>Email:</strong> " . htmlspecialchars($member['email']) . "</div>";
    $html .= "<div class='col-md-6'><i class='fas fa-phone mr-2'></i><strong>Contact:</strong> " . htmlspecialchars($member['contact_number']) . "</div>";
    $html .= "<div class='col-md-6'><i class='fas fa-user-tag mr-2'></i><strong>Position:</strong> " . htmlspecialchars($member['position_name'] ?? 'N/A') . "</div>";
    $html .= "<div class='col-md-6'><i class='fas fa-calendar-alt mr-2'></i><strong>Joined:</strong> " . date("M j, Y", strtotime($member['created_at'])) . "</div>";
    $html .= "</div>";

    // Transactions
    $stmt2 = $conn->prepare("SELECT * FROM club_transactions WHERE member_id = ? ORDER BY transaction_date DESC LIMIT 8");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $transactions = $stmt2->get_result();

    $html .= "<h5 class='border-bottom pb-2 mt-4 mb-3'><i class='fas fa-wallet mr-2'></i>Recent Transactions</h5>";
    if ($transactions->num_rows > 0) {
        $html .= "<table class='table table-sm table-striped table-bordered mb-3'>";
        $html .= "<thead class='thead-light'><tr><th>Date</th><th class='text-right'>Amount</th><th>Reference #</th></tr></thead><tbody>";
        while ($row = $transactions->fetch_assoc()) {
            $html .= "<tr>";
            $html .= "<td>" . date("M j, Y", strtotime($row['transaction_date'])) . "</td>";
            $html .= "<td class='text-right'>{$currencySymbol} " . number_format($row['amount'], 2) . "</td>";
            $html .= "<td>" . htmlspecialchars($row['reference_number']) . "</td>";
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";
    } else {
        $html .= "<p class='text-muted small'>No transactions found.</p>";
    }

    // Summary
    $stmt3 = $conn->prepare("SELECT SUM(amount) as total, COUNT(*) as txn_count FROM club_transactions WHERE member_id = ?");
    $stmt3->bind_param("i", $id);
    $stmt3->execute();
    $totalData = $stmt3->get_result()->fetch_assoc();
    $total = $totalData['total'] ?? 0;
    $txnCount = $totalData['txn_count'] ?? 0;

    $html .= "<div class='my-3 p-3 bg-light rounded shadow-sm'>";
    $html .= "<strong>Total Contributions:</strong> <span class='h5 text-success ml-2'>{$currencySymbol} " . number_format($total, 2) . "</span> ";
    $html .= "<small class='text-muted'>({$txnCount} transactions)</small>";
    $html .= "</div>";

    // Attendance
    $stmt5 = $conn->prepare("SELECT a.activity_id, COALESCE(e.title, p.title) AS title, COALESCE(e.event_date, p.start_date) AS date 
                         FROM club_attendances a 
                         LEFT JOIN club_events e ON a.category = 'Club Event' AND a.activity_id = e.id 
                         LEFT JOIN club_projects p ON a.category = 'Club Project' AND a.activity_id = p.id 
                         WHERE a.member_id = ? ORDER BY date DESC LIMIT 8");
    $stmt5->bind_param("i", $id);
    $stmt5->execute();
    $attendances = $stmt5->get_result();

    $html .= "<h5 class='border-bottom pb-2 mt-4 mb-3'><i class='fas fa-calendar-check mr-2'></i>Recent Attendance</h5>";
    if ($attendances->num_rows > 0) {
        $html .= "<ul class='list-group list-group-flush small mb-3'>";
        while ($row = $attendances->fetch_assoc()) {
            $date = $row['date'] ? date("M j, Y", strtotime($row['date'])) : 'N/A';
            $html .= "<li class='list-group-item d-flex justify-content-between align-items-center py-2'>";
            $html .= "<span><i class='fas fa-check-circle text-success mr-2'></i>" . htmlspecialchars($row['title']) . "</span>";
            $html .= "<span class='text-muted'>{$date}</span></li>";
        }
        $html .= "</ul>";
    } else {
        $html .= "<p class='text-muted small'>No recent attendance found.</p>";
    }

    // Export
    $html .= "<div class='text-center mt-4'>";
    $html .= "<button class='btn btn-outline-primary btn-sm mr-2' onclick=\"downloadPDF('member-report-{$member['id']}', 'membership_report_{$member['id']}')\"><i class='fas fa-file-pdf'></i> PDF</button>";
    $html .= "<button class='btn btn-outline-success btn-sm' onclick=\"printSection('member-report-{$member['id']}')\"><i class='fas fa-print'></i> Print</button>";
    $html .= "</div>";

    $html .= "</div></div>";
}

$html .= "
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        font-size: 0.95rem;
    }
    .card { border-radius: 0.5rem; }
    .table th, .table td { vertical-align: middle; }
    .list-group-item { border: none; padding: 0.5rem 0; }
    .bg-light { background-color: #f8f9fa !important; }
    @media print {
        body * { visibility: hidden; }
        #printable-section, #printable-section * { visibility: visible; }
        #printable-section {
            position: absolute; left: 0; top: 0; width: 100%;
        }
    }
</style>

<script src='https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js'></script>
<script>
function downloadPDF(elementId, filename) {
    const element = document.getElementById(elementId);
    if (!element) return alert('Content not found for PDF export.');
    const opt = {
        margin: 0.4,
        filename: filename + '.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(element).save();
}

function printSection(id) {
    const content = document.getElementById(id);
    if (!content) return alert('Content not found for printing.');
    const win = window.open('', '', 'width=900,height=700');
    win.document.write('<html><head><title>Print Report</title>');
    win.document.write('<link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css\">');
    win.document.write('</head><body>');
    win.document.write(content.innerHTML);
    win.document.write('</body></html>');
    win.document.close();
    setTimeout(() => { win.print(); win.close(); }, 500);
}
</script>
";

echo $html;
