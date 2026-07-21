<?php
/**
 * Organizer Dashboard
 * -------------------
 * Displays organizer-specific statistics, visual reports (attendance per event,
 * registration status proportions), and a management table of all events 
 * created by the logged-in organizer.
 * 
 * @requires auth_check.php  – session bootstrap & role guard
 * @requires db_connect.php  – PDO $pdo connection
 * @requires helpers.php     – flash(), sanitize(), etc.
 */

require_once '../includes/auth_check.php';
requireRole(['Organizer']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

// ── Current organizer ID from session ──────────────────────────
$user_id = $_SESSION['user_id'];

// ── Stat 1: Total events created by this organizer ─────────────
$stmtEvents = $pdo->prepare("SELECT COUNT(*) FROM events WHERE created_by = ?");
$stmtEvents->execute([$user_id]);
$totalEvents = $stmtEvents->fetchColumn();

// ── Stat 2: Total confirmed registrations across my events ─────
$stmtRegs = $pdo->prepare(
    "SELECT COUNT(*) FROM registrations r
     JOIN events e ON r.Event_ID = e.Event_ID
     WHERE e.created_by = ? AND r.Status = 'Confirmed'"
);
$stmtRegs->execute([$user_id]);
$totalRegistrations = $stmtRegs->fetchColumn();

// ── Stat 3: Upcoming events (event date >= today) ──────────────
$stmtUpcoming = $pdo->prepare(
    "SELECT COUNT(*) FROM events
     WHERE created_by = ? AND Event_Date >= CURDATE()"
);
$stmtUpcoming->execute([$user_id]);
$upcomingEvents = $stmtUpcoming->fetchColumn();

// ── Fetch all organizer events with registration counts ────────
$stmtList = $pdo->prepare(
    "SELECT e.*,
            (SELECT COUNT(*) FROM registrations r
             WHERE r.Event_ID = e.Event_ID AND r.Status = 'Confirmed') AS reg_count
     FROM events e
     WHERE e.created_by = ?
     ORDER BY e.Event_Date DESC"
);
$stmtList->execute([$user_id]);
$events = $stmtList->fetchAll(PDO::FETCH_ASSOC);

// Map event names and attendee counts for bar chart
$chartEventNames = [];
$chartRegCounts = [];
foreach ($events as $e) {
    // Truncate long names for chart readability
    $chartEventNames[] = strlen($e['Event_Name']) > 15 ? substr($e['Event_Name'], 0, 12) . '...' : $e['Event_Name'];
    $chartRegCounts[] = (int) $e['reg_count'];
}

// ── Chart 2: Registration Status Breakdown for My Events ────────
$stmtStatusDist = $pdo->prepare(
    "SELECT r.Status, COUNT(*) AS count
     FROM registrations r
     JOIN events e ON r.Event_ID = e.Event_ID
     WHERE e.created_by = ?
     GROUP BY r.Status"
);
$stmtStatusDist->execute([$user_id]);
$statusDistRaw = $stmtStatusDist->fetchAll(PDO::FETCH_ASSOC);
$statusLabels = [];
$statusData = [];
foreach ($statusDistRaw as $row) {
    $statusLabels[] = $row['Status'];
    $statusData[] = (int) $row['count'];
}

// ── Page title for header include ──────────────────────────────
$pageTitle = "Organizer Dashboard";
require_once '../includes/header.php';
?>

<!-- Custom CSS for Organizer Dashboard Refinement -->
<style>
    .org-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        box-shadow: var(--shadow);
    }
    .org-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
        border-color: var(--accent);
    }
    .org-stat-icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: var(--accent-light);
        color: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
    }
    .org-stat-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.2;
        color: var(--text-primary);
        font-family: monospace;
    }
    .org-stat-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-secondary);
    }
    .chart-container {
        position: relative;
        min-height: 280px;
        width: 100%;
    }
    .table-custom-row:hover {
        background: rgba(var(--accent-rgb), 0.02) !important;
    }
</style>

<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4" data-aos="fade-down">
        <div>
            <h2 class="fw-bold mb-1">💼 Organizer Console</h2>
            <p class="text-muted mb-0">Track engagement, manage your created events, and organize sign-ups.</p>
        </div>
        <a href="create_event.php" class="btn btn-accent px-4 py-2">
            <i class="bi bi-plus-circle me-2"></i>Create New Event
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <!-- Created Events -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
            <div class="org-stat-card">
                <div class="org-stat-icon-wrapper">
                    <i class="bi bi-journal-album"></i>
                </div>
                <div>
                    <div class="org-stat-value"><?php echo (int) $totalEvents; ?></div>
                    <div class="org-stat-label">Total Events Created</div>
                </div>
            </div>
        </div>

        <!-- Confirmed registrations -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="org-stat-card">
                <div class="org-stat-icon-wrapper">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="org-stat-value"><?php echo (int) $totalRegistrations; ?></div>
                    <div class="org-stat-label">Confirmed Registrations</div>
                </div>
            </div>
        </div>

        <!-- Upcoming schedules -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="org-stat-card">
                <div class="org-stat-icon-wrapper">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div class="org-stat-value"><?php echo (int) $upcomingEvents; ?></div>
                    <div class="org-stat-label">Upcoming Event Dates</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         CHARTS PANEL
         ============================================================ -->
    <div class="row g-4 mb-4">
        <!-- Event Attendance Breakdown -->
        <div class="col-lg-7" data-aos="fade-right" data-aos-delay="100">
            <div class="card glass-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-line-fill me-2 text-accent"></i>Attendance per Event</h5>
                    <small class="text-muted">Total confirmed slots registered across each of your events.</small>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center">
                    <div class="chart-container">
                        <?php if (empty($chartRegCounts) || array_sum($chartRegCounts) === 0): ?>
                            <div class="h-100 d-flex align-items-center justify-content-center text-muted">No attendance data recorded yet.</div>
                        <?php else: ?>
                            <canvas id="eventAttendanceChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration Status Proportions -->
        <div class="col-lg-5" data-aos="fade-left" data-aos-delay="100">
            <div class="card glass-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-pie-chart-fill me-2 text-accent"></i>Booking Breakdown</h5>
                    <small class="text-muted">Confirmed, Pending, and Cancelled slots for your events.</small>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center">
                    <div class="chart-container">
                        <?php if (empty($statusData)): ?>
                            <div class="h-100 d-flex align-items-center justify-content-center text-muted">No registration records found.</div>
                        <?php else: ?>
                            <canvas id="eventStatusChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         EVENTS TABLE
         ============================================================ -->
    <div class="card glass-card mb-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3 border-0">
            <h5 class="fw-bold mb-0"><i class="bi bi-table me-2 text-accent"></i>My Event Catalog</h5>
            <span class="badge bg-accent-light text-accent px-3 py-2" style="border-radius: 12px; font-weight: 600;">
                <?php echo count($events); ?> events
            </span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($events)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-journal-x fs-1 mb-2"></i>
                    <p class="mb-3">You haven't created any events yet.</p>
                    <a href="create_event.php" class="btn btn-accent px-4">Create your first event</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Event Name</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Venue</th>
                                <th>Capacity</th>
                                <th>Seats Filled</th>
                                <th class="pe-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $index => $event): ?>
                                <tr class="table-custom-row">
                                    <td class="ps-4 font-monospace text-muted"><?php echo $index + 1; ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="badge bg-glass border text-muted px-2 py-1.5" style="border-radius: 6px;">
                                            <?php echo htmlspecialchars($event['Event_Category'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted"><?php echo htmlspecialchars(date('d M Y', strtotime($event['Event_Date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-muted"><?php echo htmlspecialchars($event['Venue'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo (int) $event['Capacity']; ?></td>
                                    <td>
                                        <span class="badge bg-accent-light text-accent px-2.5 py-1.5" style="border-radius: 8px; font-weight: 600;">
                                            <?php echo (int) $event['reg_count']; ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <!-- View -->
                                        <a href="view_event.php?event_id=<?php echo (int) $event['Event_ID']; ?>"
                                           class="btn btn-sm btn-outline-accent me-1" title="View details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <!-- Edit -->
                                        <a href="edit_event.php?event_id=<?php echo (int) $event['Event_ID']; ?>"
                                           class="btn btn-sm btn-outline-warning me-1" title="Edit details">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <!-- Delete -->
                                        <a href="delete_event.php?event_id=<?php echo (int) $event['Event_ID']; ?>"
                                           class="btn btn-sm btn-outline-danger me-1" title="Delete event"
                                           onclick="return confirm('Are you sure you want to delete this event? This will delete all registrations related to it.');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <!-- Manage Participants -->
                                        <a href="manage_participants.php?event_id=<?php echo (int) $event['Event_ID']; ?>"
                                           class="btn btn-sm btn-accent" title="Manage registrations and attendance">
                                            <i class="bi bi-person-lines-fill"></i>
                                        </a>
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

<!-- Include Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // ── Get computed theme styles for Chart config ───────────────
    const style = getComputedStyle(document.documentElement);
    const textColor = style.getPropertyValue('--text-muted').trim() || '#707080';
    const accentColor = style.getPropertyValue('--accent').trim() || '#00e5ff';
    const borderColor = style.getPropertyValue('--border').trim() || 'rgba(255, 255, 255, 0.05)';

    // Global Font Configuration
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = textColor;

    // ── Chart 1: Attendance per Event ──────────────────────────
    const attendCtx = document.getElementById('eventAttendanceChart');
    if (attendCtx) {
        new Chart(attendCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chartEventNames); ?>,
                datasets: [{
                    label: 'Registrations',
                    data: <?php echo json_encode($chartRegCounts); ?>,
                    backgroundColor: accentColor,
                    borderRadius: 8,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: textColor
                        }
                    },
                    y: {
                        grid: {
                            color: borderColor
                        },
                        ticks: {
                            color: textColor,
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // ── Chart 2: Registration Status Proportions ─────────────────
    const statusCtx = document.getElementById('eventStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($statusLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($statusData); ?>,
                    backgroundColor: [
                        '#10b981', // Confirmed - green
                        '#f59e0b', // Pending - amber
                        '#ef4444'  // Cancelled - red
                    ],
                    borderWidth: 2,
                    borderColor: style.getPropertyValue('--bg-card').trim() || '#12121a'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            padding: 15,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
