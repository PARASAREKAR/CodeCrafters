<?php
/**
 * Organizer Dashboard
 * 
 * Displays organizer-specific statistics and a management table
 * of all events created by the logged-in organizer.
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

// ── Page title for header include ──────────────────────────────
$pageTitle = "Organizer Dashboard";
require_once '../includes/header.php';
?>

<!-- ============================================================
     STAT CARDS
     ============================================================ -->
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="bi bi-speedometer2 me-2"></i>Organizer Dashboard
        </h2>
        <a href="create_event.php" class="btn btn-accent">
            <i class="bi bi-plus-circle me-1"></i> Create New Event
        </a>
    </div>

    <?php echo getFlashMessage(); ?>

    <div class="row g-4 mb-5">
        <!-- My Events -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-value"><?php echo (int) $totalEvents; ?></div>
                <div class="stat-label">My Events</div>
            </div>
        </div>

        <!-- Total Registrations -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value"><?php echo (int) $totalRegistrations; ?></div>
                <div class="stat-label">Total Registrations</div>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card">
                <div class="stat-icon">⏰</div>
                <div class="stat-value"><?php echo (int) $upcomingEvents; ?></div>
                <div class="stat-label">Upcoming Events</div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         EVENTS TABLE
         ============================================================ -->
    <div class="card card-custom glass-card">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i>My Events</h5>
        </div>
        <div class="card-body">
            <?php if (empty($events)): ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-1"></i>
                    You haven't created any events yet.
                    <a href="create_event.php" class="alert-link">Create your first event</a>.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-custom align-middle searchable-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Event Name</th>
                                <th>Date</th>
                                <th>Venue</th>
                                <th>Capacity</th>
                                <th>Registrations</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $index => $event): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($event['Event_Date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($event['Venue'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo (int) $event['Capacity']; ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo (int) $event['reg_count']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <!-- View -->
                                        <a href="view_event.php?event_id=<?php echo (int) $event['Event_ID']; ?>"
                                           class="btn btn-sm btn-outline-info me-1" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <!-- Edit -->
                                        <a href="edit_event.php?event_id=<?php echo (int) $event['Event_ID']; ?>"
                                           class="btn btn-sm btn-outline-warning me-1" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <!-- Delete -->
                                        <a href="delete_event.php?event_id=<?php echo (int) $event['Event_ID']; ?>"
                                           class="btn btn-sm btn-outline-danger me-1" title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this event?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <!-- Manage Participants -->
                                        <a href="manage_participants.php?event_id=<?php echo (int) $event['Event_ID']; ?>"
                                           class="btn btn-sm btn-outline-success" title="Manage Participants">
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

<?php require_once '../includes/footer.php'; ?>
