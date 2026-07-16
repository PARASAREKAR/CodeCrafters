<?php
/**
 * Manage Participants
 * 
 * Lists all registrations for a given event with inline
 * approve / remove actions via GET parameters.
 * Includes a client-side search bar, and links to
 * attendance marking and CSV export.
 *
 * @requires auth_check.php  – session bootstrap & role guard
 * @requires db_connect.php  – PDO $pdo connection
 * @requires helpers.php     – flash(), etc.
 */

require_once '../includes/auth_check.php';
requireRole(['Organizer']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

$user_id  = $_SESSION['user_id'];
$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

// ── Verify event ownership ─────────────────────────────────────
$stmtEvent = $pdo->prepare("SELECT * FROM events WHERE Event_ID = ? AND created_by = ?");
$stmtEvent->execute([$event_id, $user_id]);
$event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    setFlashMessage('Event not found or access denied.', 'danger');
    header('Location: organizer_dashboard.php');
    exit;
}

// ── Handle approve / remove actions ────────────────────────────
$action = $_GET['action'] ?? '';
$reg_id = isset($_GET['reg_id']) ? (int) $_GET['reg_id'] : 0;

if ($action && $reg_id > 0) {

    // Ensure the registration belongs to this event
    $stmtCheck = $pdo->prepare(
        "SELECT r.Registration_ID FROM registrations r
         JOIN events e ON r.Event_ID = e.Event_ID
         WHERE r.Registration_ID = ? AND r.Event_ID = ? AND e.created_by = ?"
    );
    $stmtCheck->execute([$reg_id, $event_id, $user_id]);

    if ($stmtCheck->fetch()) {
        if ($action === 'approve') {
            $stmtAction = $pdo->prepare(
                "UPDATE registrations SET Status = 'Confirmed' WHERE Registration_ID = ? AND Status = 'Pending'"
            );
            $stmtAction->execute([$reg_id]);
            setFlashMessage('Registration approved successfully.', 'success');
        } elseif ($action === 'remove') {
            $stmtAction = $pdo->prepare(
                "UPDATE registrations SET Status = 'Cancelled' WHERE Registration_ID = ?"
            );
            $stmtAction->execute([$reg_id]);
            setFlashMessage('Registration cancelled successfully.', 'warning');
        }
    } else {
        setFlashMessage('Invalid registration.', 'danger');
    }

    header("Location: manage_participants.php?event_id=$event_id");
    exit;
}

// ── Fetch participants ─────────────────────────────────────────
$stmtParts = $pdo->prepare(
    "SELECT r.Registration_ID, u.Full_Name, u.Email, u.Mobile,
            u.College_Organization, r.Registration_Date, r.Status
     FROM registrations r
     JOIN users u ON r.User_ID = u.User_ID
     WHERE r.Event_ID = ?
     ORDER BY r.Registration_Date DESC"
);
$stmtParts->execute([$event_id]);
$participants = $stmtParts->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manage Participants";
require_once '../includes/header.php';
?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="bi bi-person-lines-fill me-2"></i>Manage Participants
        </h2>
        <a href="organizer_dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <!-- Event Info Banner -->
    <div class="alert alert-info d-flex align-items-center mb-4">
        <i class="bi bi-calendar-event me-2 fs-5"></i>
        <strong>Event:</strong>&nbsp;
        <?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?>
        &nbsp;|&nbsp;
        <strong>Date:</strong>&nbsp;
        <?php echo htmlspecialchars(date('d M Y', strtotime($event['Event_Date'])), ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <?php displayFlashMessage(); ?>

    <!-- Toolbar: Search + Action Links -->
    <div class="card card-custom glass-card">
        <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <input type="text" id="searchInput" class="form-control form-control-custom"
                       placeholder="Search participants..." style="min-width: 250px;">
            </div>
            <div class="d-flex gap-2">
                <a href="toggle_attendance.php?event_id=<?php echo (int) $event_id; ?>" class="btn btn-accent btn-sm">
                    <i class="bi bi-check2-square me-1"></i> Mark Attendance
                </a>
                <a href="export_participants.php?event_id=<?php echo (int) $event_id; ?>" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($participants)): ?>
                <div class="alert alert-info text-center mb-0">
                    <i class="bi bi-info-circle me-1"></i> No participants registered for this event.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-custom align-middle searchable-table" id="participantsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>College / Org</th>
                                <th>Registration Date</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participants as $i => $p): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars($p['Full_Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($p['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($p['Mobile'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($p['College_Organization'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($p['Registration_Date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-pending';
                                        if ($p['Status'] === 'Confirmed')  $badgeClass = 'badge-confirmed';
                                        if ($p['Status'] === 'Cancelled')  $badgeClass = 'badge-cancelled';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars($p['Status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($p['Status'] === 'Pending'): ?>
                                            <a href="manage_participants.php?event_id=<?php echo (int) $event_id; ?>&action=approve&reg_id=<?php echo (int) $p['Registration_ID']; ?>"
                                               class="btn btn-sm btn-outline-success me-1" title="Approve"
                                               onclick="return confirm('Approve this registration?');">
                                                <i class="bi bi-check-circle"></i> Approve
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($p['Status'] !== 'Cancelled'): ?>
                                            <a href="manage_participants.php?event_id=<?php echo (int) $event_id; ?>&action=remove&reg_id=<?php echo (int) $p['Registration_ID']; ?>"
                                               class="btn btn-sm btn-outline-danger" title="Remove"
                                               onclick="return confirm('Cancel this registration?');">
                                                <i class="bi bi-x-circle"></i> Remove
                                            </a>
                                        <?php endif; ?>
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

<!-- Client-side search filter -->
<script>
document.getElementById('searchInput')?.addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    const rows   = document.querySelectorAll('#participantsTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
