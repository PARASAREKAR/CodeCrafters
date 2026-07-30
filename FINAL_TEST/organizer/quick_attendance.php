<?php
require_once '../includes/auth_check.php';
requireRole(['Organizer']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

$user_id = $_SESSION['user_id'];

// Fetch events with registrations > 0 for this organizer
$stmt = $pdo->prepare(
    "SELECT e.Event_ID,
            e.Event_Name,
            e.Event_Date,
            e.Venue,
            COUNT(r.Registration_ID) AS reg_count
     FROM events e
     LEFT JOIN registrations r ON e.Event_ID = r.Event_ID
     WHERE e.created_by = ?
     GROUP BY e.Event_ID
     HAVING reg_count > 0
     ORDER BY e.Event_Date DESC"
);
$stmt->execute([$user_id]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Quick Attendance";
require_once '../includes/header.php';
?>

<div class="container-fluid py-4" data-aos="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="bi bi-check2-square me-2 text-accent"></i>Quick Attendance
        </h2>
        <a href="organizer_dashboard.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <p class="text-muted mb-4">Select an event below to quickly mark participant attendance or manage their registrations.</p>

    <!-- Events Grid -->
    <?php if (empty($events)): ?>
        <div class="alert alert-info text-center py-5 glass-card" style="background: var(--bg-card); border-color: var(--border-color);">
            <i class="bi bi-calendar-x fs-1 mb-3 d-block text-muted"></i>
            <h5>No events with registrations yet.</h5>
            <p class="mb-0 text-muted">Once participants register for your events, they will appear here for quick access.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card glass-card h-100" style="background: var(--bg-card); transition: transform 0.2s;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title fw-bold text-truncate mb-0" style="max-width: 80%;" title="<?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?>
                                </h5>
                                <span class="badge bg-accent-light text-accent rounded-pill">
                                    <i class="bi bi-people me-1"></i><?php echo $event['reg_count']; ?>
                                </span>
                            </div>
                            
                            <p class="card-text text-muted small mb-2">
                                <i class="bi bi-calendar-event me-2"></i><?php echo htmlspecialchars(date('d M Y', strtotime($event['Event_Date'])), ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <p class="card-text text-muted small mb-4">
                                <i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($event['Venue'] ?? 'TBA', ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            
                            <a href="manage_participants.php?event_id=<?php echo (int) $event['Event_ID']; ?>" class="btn btn-accent w-100">
                                <i class="bi bi-person-lines-fill me-2"></i> Manage Participants
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
