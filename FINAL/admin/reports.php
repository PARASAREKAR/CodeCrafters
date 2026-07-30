<?php
/**
 * System Reports
 * --------------
 * Admin-only comprehensive reporting page with 4 tabbed reports:
 * 1. Event-wise Registration Report (capacity vs registered)
 * 2. Participant Details Report (all participant registrations)
 * 3. Attendance Report (present/absent/not-marked)
 * 4. Cancelled Registrations Report
 *
 * @requires auth_check.php  – session bootstrap & role guard
 * @requires db_connect.php  – PDO $pdo connection
 * @requires helpers.php     – flash messages, sanitize, etc.
 */

require_once '../includes/auth_check.php';
requireRole(['Admin']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

// ================================================================
// TAB 1: Event-wise Registration Report
// ================================================================
$stmtEventReport = $pdo->query(
    "SELECT e.Event_ID,
            e.Event_Name,
            e.Venue,
            e.Event_Date,
            e.Capacity,
            COUNT(r.Registration_ID) AS reg_count
     FROM events e
     LEFT JOIN registrations r ON e.Event_ID = r.Event_ID
                               AND r.Status = 'Confirmed'
     GROUP BY e.Event_ID
     ORDER BY e.Event_Date DESC"
);
$eventReport = $stmtEventReport->fetchAll(PDO::FETCH_ASSOC);

// ================================================================
// TAB 2: Participant Details Report
// ================================================================
$stmtParticipants = $pdo->query(
    "SELECT u.Name       AS participant_name,
            u.Email      AS participant_email,
            u.Mobile     AS participant_mobile,
            e.Event_Name AS event_name,
            r.Registration_Date,
            r.Status
     FROM registrations r
     JOIN users  u ON r.User_ID  = u.User_ID
     JOIN events e ON r.Event_ID = e.Event_ID
     WHERE u.Role = 'Participant'
     ORDER BY r.Registration_Date DESC"
);
$participantReport = $stmtParticipants->fetchAll(PDO::FETCH_ASSOC);

// ================================================================
// TAB 3: Attendance Report
// ================================================================
$stmtAttendance = $pdo->query(
    "SELECT e.Event_Name  AS event_name,
            u.Name        AS participant_name,
            r.Status      AS reg_status,
            a.Status      AS attendance_status
     FROM registrations r
     JOIN events e ON r.Event_ID = e.Event_ID
     JOIN users  u ON r.User_ID  = u.User_ID
     LEFT JOIN attendance a ON r.Registration_ID = a.Registration_ID
     ORDER BY e.Event_Name, u.Name"
);
$attendanceReport = $stmtAttendance->fetchAll(PDO::FETCH_ASSOC);

// Attendance summary stats
$totalPresent  = 0;
$totalAbsent   = 0;
$notMarked     = 0;
foreach ($attendanceReport as $row) {
    if ($row['attendance_status'] === 'Present') {
        $totalPresent++;
    } elseif ($row['attendance_status'] === 'Absent') {
        $totalAbsent++;
    } else {
        $notMarked++;
    }
}

// ================================================================
// TAB 4: Cancelled Registrations
// ================================================================
$stmtCancelled = $pdo->query(
    "SELECT u.Name       AS participant_name,
            u.Email      AS participant_email,
            e.Event_Name AS event_name,
            r.Registration_Date,
            r.Status,
            r.created_at AS cancellation_date
     FROM registrations r
     JOIN users  u ON r.User_ID  = u.User_ID
     JOIN events e ON r.Event_ID = e.Event_ID
     WHERE r.Status = 'Cancelled'
     ORDER BY r.created_at DESC"
);
$cancelledReport = $stmtCancelled->fetchAll(PDO::FETCH_ASSOC);

// ── Page title for header include ──────────────────────────────
$pageTitle = "System Reports";
require_once '../includes/header.php';
?>

<!-- ============================================================
     PAGE HEADER
     ============================================================ -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">📊 System Reports</h2>
</div>

<!-- ============================================================
     TAB NAVIGATION
     ============================================================ -->
<ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist"
    style="border-bottom-color: var(--border-color);">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="event-tab"
                data-bs-toggle="tab" data-bs-target="#eventReport"
                type="button" role="tab" aria-controls="eventReport" aria-selected="true"
                style="color: var(--text-primary);">
            📅 Event-wise
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="participant-tab"
                data-bs-toggle="tab" data-bs-target="#participantReport"
                type="button" role="tab" aria-controls="participantReport" aria-selected="false"
                style="color: var(--text-primary);">
            👥 Participants
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="attendance-tab"
                data-bs-toggle="tab" data-bs-target="#attendanceReport"
                type="button" role="tab" aria-controls="attendanceReport" aria-selected="false"
                style="color: var(--text-primary);">
            ✅ Attendance
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="cancelled-tab"
                data-bs-toggle="tab" data-bs-target="#cancelledReport"
                type="button" role="tab" aria-controls="cancelledReport" aria-selected="false"
                style="color: var(--text-primary);">
            ❌ Cancelled
        </button>
    </li>
</ul>

<!-- ============================================================
     TAB CONTENT
     ============================================================ -->
<div class="tab-content" id="reportTabContent">

    <!-- ========================================================
         TAB 1: Event-wise Registration Report
         ======================================================== -->
    <div class="tab-pane fade show active" id="eventReport" role="tabpanel" aria-labelledby="event-tab">
        <div class="card glass-card" style="background: var(--bg-card);">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Event-wise Registration Report</h5>
                <div class="d-flex gap-2">
                    <a href="export_reports.php?type=event_wise" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                    </a>
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print();">
                        🖨️ Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($eventReport)): ?>
                    <div class="alert alert-info text-center">No events found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Event Name</th>
                                    <th>Venue</th>
                                    <th>Date</th>
                                    <th>Capacity</th>
                                    <th>Registered</th>
                                    <th>Available</th>
                                    <th>Usage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eventReport as $index => $event): ?>
                                    <?php
                                        $capacity   = (int) $event['Capacity'];
                                        $registered = (int) $event['reg_count'];
                                        $available  = max(0, $capacity - $registered);
                                        $usagePct   = $capacity > 0
                                                    ? round(($registered / $capacity) * 100, 1)
                                                    : 0;

                                        // Color the bar based on usage
                                        $barColor = 'bg-success';
                                        if ($usagePct >= 90) {
                                            $barColor = 'bg-danger';
                                        } elseif ($usagePct >= 70) {
                                            $barColor = 'bg-warning';
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($event['Venue'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($event['Event_Date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo $capacity; ?></td>
                                        <td><span class="badge bg-info"><?php echo $registered; ?></span></td>
                                        <td><span class="badge bg-secondary"><?php echo $available; ?></span></td>
                                        <td style="min-width: 150px;">
                                            <div class="capacity-bar">
                                                <div class="progress" style="height: 20px; background: var(--bg-secondary);">
                                                    <div class="progress-bar <?php echo $barColor; ?>"
                                                         role="progressbar"
                                                         style="width: <?php echo $usagePct; ?>%"
                                                         aria-valuenow="<?php echo $usagePct; ?>"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">
                                                        <?php echo $usagePct; ?>%
                                                    </div>
                                                </div>
                                            </div>
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

    <!-- ========================================================
         TAB 2: Participant Details Report
         ======================================================== -->
    <div class="tab-pane fade" id="participantReport" role="tabpanel" aria-labelledby="participant-tab">
        <div class="card glass-card" style="background: var(--bg-card);">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Participant Details Report</h5>
                <div class="d-flex gap-2">
                    <a href="export_reports.php?type=participant_details" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                    </a>
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print();">
                        🖨️ Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($participantReport)): ?>
                    <div class="alert alert-info text-center">No participant registrations found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Participant Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Event Name</th>
                                    <th>Registration Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participantReport as $index => $part): ?>
                                    <?php
                                        $statusBadge = 'bg-secondary';
                                        switch ($part['Status']) {
                                            case 'Confirmed': $statusBadge = 'bg-success'; break;
                                            case 'Pending':   $statusBadge = 'bg-warning text-dark'; break;
                                            case 'Cancelled': $statusBadge = 'bg-danger'; break;
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($part['participant_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($part['participant_email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($part['participant_mobile'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($part['event_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($part['Registration_Date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $statusBadge; ?>">
                                                <?php echo htmlspecialchars($part['Status'], ENT_QUOTES, 'UTF-8'); ?>
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

    <!-- ========================================================
         TAB 3: Attendance Report
         ======================================================== -->
    <div class="tab-pane fade" id="attendanceReport" role="tabpanel" aria-labelledby="attendance-tab">
        <div class="card glass-card" style="background: var(--bg-card);">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Attendance Report</h5>
                <div class="d-flex gap-2">
                    <a href="export_reports.php?type=attendance" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                    </a>
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print();">
                        🖨️ Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Summary Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="text-center p-3 rounded" style="background: var(--bg-secondary);">
                            <h4 class="fw-bold text-success mb-1"><?php echo $totalPresent; ?></h4>
                            <small class="text-muted">Total Present</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 rounded" style="background: var(--bg-secondary);">
                            <h4 class="fw-bold text-danger mb-1"><?php echo $totalAbsent; ?></h4>
                            <small class="text-muted">Total Absent</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 rounded" style="background: var(--bg-secondary);">
                            <h4 class="fw-bold text-warning mb-1"><?php echo $notMarked; ?></h4>
                            <small class="text-muted">Not Marked</small>
                        </div>
                    </div>
                </div>

                <?php if (empty($attendanceReport)): ?>
                    <div class="alert alert-info text-center">No attendance data found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Event Name</th>
                                    <th>Participant Name</th>
                                    <th>Registration Status</th>
                                    <th>Attendance Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendanceReport as $index => $att): ?>
                                    <?php
                                        // Registration status badge
                                        $regBadge = 'bg-secondary';
                                        switch ($att['reg_status']) {
                                            case 'Confirmed': $regBadge = 'bg-success'; break;
                                            case 'Pending':   $regBadge = 'bg-warning text-dark'; break;
                                            case 'Cancelled': $regBadge = 'bg-danger'; break;
                                        }

                                        // Attendance status badge
                                        $attBadge = 'bg-secondary';
                                        $attLabel = 'Not Marked';
                                        if ($att['attendance_status'] === 'Present') {
                                            $attBadge = 'bg-success';
                                            $attLabel = 'Present';
                                        } elseif ($att['attendance_status'] === 'Absent') {
                                            $attBadge = 'bg-danger';
                                            $attLabel = 'Absent';
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($att['event_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($att['participant_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $regBadge; ?>">
                                                <?php echo htmlspecialchars($att['reg_status'], ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $attBadge; ?>">
                                                <?php echo htmlspecialchars($attLabel, ENT_QUOTES, 'UTF-8'); ?>
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

    <!-- ========================================================
         TAB 4: Cancelled Registrations Report
         ======================================================== -->
    <div class="tab-pane fade" id="cancelledReport" role="tabpanel" aria-labelledby="cancelled-tab">
        <div class="card glass-card" style="background: var(--bg-card);">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Cancelled Registrations Report</h5>
                <div class="d-flex gap-2">
                    <a href="export_reports.php?type=cancelled_registrations" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                    </a>
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print();">
                        🖨️ Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($cancelledReport)): ?>
                    <div class="alert alert-info text-center">No cancelled registrations found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Participant Name</th>
                                    <th>Email</th>
                                    <th>Event Name</th>
                                    <th>Registration Date</th>
                                    <th>Cancellation Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cancelledReport as $index => $cancel): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($cancel['participant_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($cancel['participant_email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($cancel['event_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($cancel['Registration_Date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($cancel['cancellation_date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge bg-danger">
                                                <?php echo htmlspecialchars($cancel['Status'], ENT_QUOTES, 'UTF-8'); ?>
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

</div>
<!-- End Tab Content -->

<!-- ============================================================
     ACTIVE TAB STYLING
     ============================================================ -->
<style>
    /* Style the active tab with the accent color */
    #reportTabs .nav-link.active {
        background-color: var(--bg-card) !important;
        color: var(--accent) !important;
        border-color: var(--border-color) var(--border-color) var(--bg-card) !important;
        border-bottom: 2px solid var(--accent) !important;
        font-weight: 600;
    }

    #reportTabs .nav-link:not(.active):hover {
        border-color: transparent;
        color: var(--accent) !important;
    }

    /* Print-friendly styles */
    @media print {
        .navbar, .footer-custom, .nav-tabs, .btn, .search-wrapper {
            display: none !important;
        }
        /* Only show the active tab when printing */
        .tab-pane:not(.active) {
            display: none !important;
        }
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
    }
</style>

<?php require_once '../includes/footer.php'; ?>
