<?php
/**
 * Organizer Reports
 * 
 * Four-tab report view filtered to events owned by the
 * logged-in organizer:
 *   Tab 1 – Event-wise registration summary
 *   Tab 2 – Participant details
 *   Tab 3 – Attendance records
 *   Tab 4 – Cancelled registrations
 *
 * Each tab includes a print button.
 *
 * @requires auth_check.php  – session bootstrap & role guard
 * @requires db_connect.php  – PDO $pdo connection
 * @requires helpers.php     – flash(), etc.
 */

require_once '../includes/auth_check.php';
requireRole(['Organizer']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

$user_id = $_SESSION['user_id'];

// ── Tab 1: Event-wise registration counts ──────────────────────
$stmtTab1 = $pdo->prepare(
    "SELECT e.Event_Name, e.Event_Date, e.Venue, e.Capacity,
            SUM(CASE WHEN r.Status = 'Confirmed' THEN 1 ELSE 0 END) AS confirmed,
            SUM(CASE WHEN r.Status = 'Pending'   THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN r.Status = 'Cancelled' THEN 1 ELSE 0 END) AS cancelled,
            COUNT(r.Registration_ID) AS total
     FROM events e
     LEFT JOIN registrations r ON e.Event_ID = r.Event_ID
     WHERE e.created_by = ?
     GROUP BY e.Event_ID
     ORDER BY e.Event_Date DESC"
);
$stmtTab1->execute([$user_id]);
$eventWise = $stmtTab1->fetchAll(PDO::FETCH_ASSOC);

// ── Tab 2: All participant details ─────────────────────────────
$stmtTab2 = $pdo->prepare(
    "SELECT e.Event_Name, u.Name, u.Email, u.Mobile,
            r.College_Organization, r.Registration_Date, r.Status
     FROM registrations r
     JOIN users u  ON r.User_ID  = u.User_ID
     JOIN events e ON r.Event_ID = e.Event_ID
     WHERE e.created_by = ?
     ORDER BY e.Event_Name, r.Registration_Date DESC"
);
$stmtTab2->execute([$user_id]);
$participantDetails = $stmtTab2->fetchAll(PDO::FETCH_ASSOC);

// ── Tab 3: Attendance records ──────────────────────────────────
$stmtTab3 = $pdo->prepare(
    "SELECT e.Event_Name, u.Name, u.Email,
            a.Status AS Attendance_Status, a.marked_at AS Marked_At
     FROM attendance a
     JOIN registrations r ON a.Registration_ID = r.Registration_ID
     JOIN users u          ON r.User_ID  = u.User_ID
     JOIN events e         ON r.Event_ID = e.Event_ID
     WHERE e.created_by = ?
     ORDER BY e.Event_Name, u.Name"
);
$stmtTab3->execute([$user_id]);
$attendanceRecords = $stmtTab3->fetchAll(PDO::FETCH_ASSOC);

// ── Tab 4: Cancelled registrations ─────────────────────────────
$stmtTab4 = $pdo->prepare(
    "SELECT e.Event_Name, u.Name, u.Email, u.Mobile,
            r.Registration_Date
     FROM registrations r
     JOIN users u  ON r.User_ID  = u.User_ID
     JOIN events e ON r.Event_ID = e.Event_ID
     WHERE e.created_by = ? AND r.Status = 'Cancelled'
     ORDER BY r.Registration_Date DESC"
);
$stmtTab4->execute([$user_id]);
$cancelledRegs = $stmtTab4->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Organizer Reports";
require_once '../includes/header.php';
?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="bi bi-graph-up me-2"></i>Reports
        </h2>
        <a href="organizer_dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <?php echo getFlashMessage(); ?>

    <!-- ============================================================
         TAB NAVIGATION
         ============================================================ -->
    <ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab1-tab" data-bs-toggle="tab"
                    data-bs-target="#tab1" type="button" role="tab">
                <i class="bi bi-bar-chart me-1"></i>Event Registrations
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab2-tab" data-bs-toggle="tab"
                    data-bs-target="#tab2" type="button" role="tab">
                <i class="bi bi-people me-1"></i>Participant Details
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab3-tab" data-bs-toggle="tab"
                    data-bs-target="#tab3" type="button" role="tab">
                <i class="bi bi-check2-square me-1"></i>Attendance
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab4-tab" data-bs-toggle="tab"
                    data-bs-target="#tab4" type="button" role="tab">
                <i class="bi bi-x-octagon me-1"></i>Cancelled
            </button>
        </li>
    </ul>

    <!-- ============================================================
         TAB CONTENT
         ============================================================ -->
    <div class="tab-content" id="reportTabContent">

        <!-- ── TAB 1: Event-wise Registrations ──────────────────── -->
        <div class="tab-pane fade show active" id="tab1" role="tabpanel">
            <div class="card card-custom glass-card">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Event-wise Registration Summary</h5>
                    <button class="btn btn-outline-secondary btn-sm" onclick="printTab('tab1')">
                        <i class="bi bi-printer me-1"></i>Print
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($eventWise)): ?>
                        <p class="text-muted text-center">No data available.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-custom align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Event Name</th>
                                        <th>Date</th>
                                        <th>Venue</th>
                                        <th>Capacity</th>
                                        <th>Confirmed</th>
                                        <th>Pending</th>
                                        <th>Cancelled</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($eventWise as $i => $row): ?>
                                        <tr>
                                            <td><?php echo $i + 1; ?></td>
                                            <td><?php echo htmlspecialchars($row['Event_Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['Event_Date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row['Venue'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo (int) $row['Capacity']; ?></td>
                                            <td><span class="badge badge-confirmed"><?php echo (int) $row['confirmed']; ?></span></td>
                                            <td><span class="badge badge-pending"><?php echo (int) $row['pending']; ?></span></td>
                                            <td><span class="badge badge-cancelled"><?php echo (int) $row['cancelled']; ?></span></td>
                                            <td><strong><?php echo (int) $row['total']; ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ── TAB 2: Participant Details ───────────────────────── -->
        <div class="tab-pane fade" id="tab2" role="tabpanel">
            <div class="card card-custom glass-card">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Participant Details</h5>
                    <button class="btn btn-outline-secondary btn-sm" onclick="printTab('tab2')">
                        <i class="bi bi-printer me-1"></i>Print
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($participantDetails)): ?>
                        <p class="text-muted text-center">No data available.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-custom align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Event</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>College / Org</th>
                                        <th>Registered On</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($participantDetails as $i => $row): ?>
                                        <tr>
                                            <td><?php echo $i + 1; ?></td>
                                            <td><?php echo htmlspecialchars($row['Event_Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row['Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row['Mobile'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row['College_Organization'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['Registration_Date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <?php
                                                $bc = 'badge-pending';
                                                if ($row['Status'] === 'Confirmed') $bc = 'badge-confirmed';
                                                if ($row['Status'] === 'Cancelled') $bc = 'badge-cancelled';
                                                ?>
                                                <span class="badge <?php echo $bc; ?>">
                                                    <?php echo htmlspecialchars($row['Status'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ── TAB 3: Attendance ────────────────────────────────── -->
        <div class="tab-pane fade" id="tab3" role="tabpanel">
            <div class="card card-custom glass-card">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Attendance Records</h5>
                    <button class="btn btn-outline-secondary btn-sm" onclick="printTab('tab3')">
                        <i class="bi bi-printer me-1"></i>Print
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($attendanceRecords)): ?>
                        <p class="text-muted text-center">No attendance data recorded yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-custom align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Event</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Marked At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendanceRecords as $i => $row): ?>
                                        <tr>
                                            <td><?php echo $i + 1; ?></td>
                                            <td><?php echo htmlspecialchars($row['Event_Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row['Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <?php if ($row['Attendance_Status'] === 'Present'): ?>
                                                    <span class="badge badge-confirmed">Present</span>
                                                <?php else: ?>
                                                    <span class="badge badge-cancelled">Absent</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                echo $row['Marked_At']
                                                    ? htmlspecialchars(date('d M Y h:i A', strtotime($row['Marked_At'])), ENT_QUOTES, 'UTF-8')
                                                    : 'N/A';
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ── TAB 4: Cancelled Registrations ───────────────────── -->
        <div class="tab-pane fade" id="tab4" role="tabpanel">
            <div class="card card-custom glass-card">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Cancelled Registrations</h5>
                    <button class="btn btn-outline-secondary btn-sm" onclick="printTab('tab4')">
                        <i class="bi bi-printer me-1"></i>Print
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($cancelledRegs)): ?>
                        <p class="text-muted text-center">No cancelled registrations found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-custom align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Event</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Cancelled On</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cancelledRegs as $i => $row): ?>
                                        <tr>
                                            <td><?php echo $i + 1; ?></td>
                                            <td><?php echo htmlspecialchars($row['Event_Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row['Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row['Mobile'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['Registration_Date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /tab-content -->
</div>

<!-- Print helper: clones the target tab's content into a new window -->
<script>
function printTab(tabId) {
    const content  = document.getElementById(tabId);
    const printWin = window.open('', '_blank', 'width=900,height=700');
    printWin.document.write(`
        <html>
        <head>
            <title>Print Report</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>body{padding:20px;} @media print{.no-print{display:none;}}</style>
        </head>
        <body>
            <h3 class="mb-3">Organizer Report</h3>
            ${content.innerHTML}
            <script>window.print(); window.close();<\/script>
        </body>
        </html>
    `);
    printWin.document.close();
}
</script>

<?php require_once '../includes/footer.php'; ?>
