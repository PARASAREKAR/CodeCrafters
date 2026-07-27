<?php
/**
 * View Event Details
 * 
 * Displays a read-only card of event information including
 * a visual capacity bar and a table of registered participants.
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

// ── Fetch event with ownership verification ────────────────────
$stmt = $pdo->prepare("SELECT * FROM events WHERE Event_ID = ? AND created_by = ?");
$stmt->execute([$event_id, $user_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    setFlashMessage('Event not found or access denied.', 'danger');
    header('Location: organizer_dashboard.php');
    exit;
}

// ── Count confirmed registrations ──────────────────────────────
$stmtCount = $pdo->prepare(
    "SELECT COUNT(*) FROM registrations WHERE Event_ID = ? AND Status = 'Confirmed'"
);
$stmtCount->execute([$event_id]);
$confirmedCount = (int) $stmtCount->fetchColumn();

// ── Capacity percentage ────────────────────────────────────────
$capacity   = (int) $event['Capacity'];
$percentage = $capacity > 0 ? round(($confirmedCount / $capacity) * 100) : 0;
$barClass   = $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success');

// ── Fetch registered participants ──────────────────────────────
$stmtParts = $pdo->prepare(
    "SELECT u.Name, u.Email, u.Mobile, u.College_Organization,
            r.Registration_Date, r.Status
     FROM registrations r
     JOIN users u ON r.User_ID = u.User_ID
     WHERE r.Event_ID = ?
     ORDER BY r.Registration_Date DESC"
);
$stmtParts->execute([$event_id]);
$participants = $stmtParts->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "View Event";
require_once '../includes/header.php';
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="bi bi-eye me-2"></i>Event Details
        </h2>
        <a href="organizer_dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <?php displayFlashMessage(); ?>

    <!-- ── Event Details Card ───────────────────────────────────── -->
    <div class="card card-custom glass-card mb-4">
        <div class="card-header bg-transparent">
            <h4 class="mb-0">
                <i class="bi bi-calendar-event me-2"></i>
                <?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?>
            </h4>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <p><strong><i class="bi bi-text-paragraph me-1"></i>Description:</strong><br>
                        <?php echo nl2br(htmlspecialchars($event['Description'] ?? 'N/A', ENT_QUOTES, 'UTF-8')); ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong><i class="bi bi-geo-alt me-1"></i>Venue:</strong><br>
                        <?php echo htmlspecialchars($event['Venue'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
                <div class="col-md-3">
                    <p><strong><i class="bi bi-calendar-date me-1"></i>Date:</strong><br>
                        <?php echo htmlspecialchars(date('d M Y', strtotime($event['Event_Date'])), ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
                <div class="col-md-3">
                    <p><strong><i class="bi bi-clock me-1"></i>Time:</strong><br>
                        <?php echo htmlspecialchars(date('h:i A', strtotime($event['Event_Time'])), ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
                <div class="col-md-3">
                    <p><strong><i class="bi bi-person-badge me-1"></i>Organizer:</strong><br>
                        <?php echo htmlspecialchars($event['Organizer'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
                <div class="col-md-3">
                    <p><strong><i class="bi bi-people me-1"></i>Capacity:</strong><br>
                        <?php echo (int) $event['Capacity']; ?>
                    </p>
                </div>
            </div>

            <!-- ── Capacity Bar ─────────────────────────────────── -->
            <div class="mt-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-bar-chart me-1"></i>Registration Capacity
                    (<?php echo $confirmedCount; ?> / <?php echo $capacity; ?> — <?php echo $percentage; ?>%)
                </label>
                <div class="progress capacity-bar" style="height: 24px;">
                    <div class="progress-bar <?php echo $barClass; ?>" role="progressbar"
                         style="width: <?php echo $percentage; ?>%"
                         aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                        <?php echo $percentage; ?>%
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Registered Participants ───────────────────────────────── -->
    <div class="card card-custom glass-card">
        <div class="card-header bg-transparent">
            <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Registered Participants</h5>
        </div>
        <div class="card-body">
            <?php if (empty($participants)): ?>
                <div class="alert alert-info text-center mb-0">
                    <i class="bi bi-info-circle me-1"></i> No participants have registered yet.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>College / Org</th>
                                <th>Registered On</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participants as $i => $p): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars($p['Name'], ENT_QUOTES, 'UTF-8'); ?></td>
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
