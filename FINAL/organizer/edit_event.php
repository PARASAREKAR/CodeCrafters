<?php
/**
 * Edit Event
 * 
 * Fetches an existing event (ownership-verified), pre-populates
 * the edit form, and processes the UPDATE on POST submission.
 *
 * @requires auth_check.php  – session bootstrap & role guard
 * @requires db_connect.php  – PDO $pdo connection
 * @requires helpers.php     – flash(), sanitize(), generateCsrfToken(), etc.
 */

require_once '../includes/auth_check.php';
requireRole(['Organizer']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

$user_id  = $_SESSION['user_id'];
$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

// ── Fetch event (ownership check) ──────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM events WHERE Event_ID = ? AND created_by = ?");
$stmt->execute([$event_id, $user_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    setFlashMessage('Event not found or access denied.', 'danger');
    header('Location: organizer_dashboard.php');
    exit;
}

// ── Handle POST submission ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF validation
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Invalid CSRF token. Please try again.', 'danger');
        header("Location: edit_event.php?event_id=$event_id");
        exit;
    }

    // Sanitise inputs
    $eventName   = trim(htmlspecialchars($_POST['Event_Name']   ?? '', ENT_QUOTES, 'UTF-8'));
    $description = trim(htmlspecialchars($_POST['Description']  ?? '', ENT_QUOTES, 'UTF-8'));
    $venue       = trim(htmlspecialchars($_POST['Venue']        ?? '', ENT_QUOTES, 'UTF-8'));
    $eventDate   = trim($_POST['Event_Date'] ?? '');
    $eventTime   = trim($_POST['Event_Time'] ?? '');
    $organizer   = trim(htmlspecialchars($_POST['Organizer']    ?? '', ENT_QUOTES, 'UTF-8'));
    $capacity    = (int) ($_POST['Capacity'] ?? 0);

    // ── Validation ─────────────────────────────────────────────
    $errors = [];

    if (empty($eventName))  $errors[] = 'Event name is required.';
    if (empty($venue))      $errors[] = 'Venue is required.';
    if (empty($eventDate))  $errors[] = 'Event date is required.';
    if (empty($eventTime))  $errors[] = 'Event time is required.';
    if ($capacity < 1)      $errors[] = 'Capacity must be at least 1.';

    if (!empty($errors)) {
        setFlashMessage(implode('<br>', $errors), 'danger');
        header("Location: edit_event.php?event_id=$event_id");
        exit;
    }

    // ── UPDATE event ───────────────────────────────────────────
    $stmtUpdate = $pdo->prepare(
        "UPDATE events
         SET Event_Name = ?, Description = ?, Venue = ?, Event_Date = ?,
             Event_Time = ?, Organizer = ?, Capacity = ?
         WHERE Event_ID = ? AND created_by = ?"
    );
    $stmtUpdate->execute([
        $eventName, $description, $venue, $eventDate,
        $eventTime, $organizer, $capacity,
        $event_id, $user_id
    ]);

    setFlashMessage('Event updated successfully!', 'success');
    header('Location: organizer_dashboard.php');
    exit;
}

// ── Generate CSRF token ────────────────────────────────────────
$csrfToken = generateCsrfToken();

$pageTitle = "Edit Event";
require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">
                    <i class="bi bi-pencil-square me-2"></i>Edit Event
                </h2>
                <a href="organizer_dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <?php displayFlashMessage(); ?>

            <div class="card card-custom glass-card">
                <div class="card-body p-4">
                    <form method="POST" action="edit_event.php?event_id=<?php echo (int) $event_id; ?>" novalidate>
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                        <!-- Event Name -->
                        <div class="mb-3">
                            <label for="Event_Name" class="form-label fw-semibold">
                                <i class="bi bi-bookmark me-1"></i>Event Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-custom" id="Event_Name"
                                   name="Event_Name" required
                                   value="<?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="Description" class="form-label fw-semibold">
                                <i class="bi bi-text-paragraph me-1"></i>Description
                            </label>
                            <textarea class="form-control form-control-custom" id="Description"
                                      name="Description" rows="4"><?php echo htmlspecialchars($event['Description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <!-- Venue -->
                        <div class="mb-3">
                            <label for="Venue" class="form-label fw-semibold">
                                <i class="bi bi-geo-alt me-1"></i>Venue <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-custom" id="Venue"
                                   name="Venue" required
                                   value="<?php echo htmlspecialchars($event['Venue'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="row">
                            <!-- Event Date -->
                            <div class="col-md-6 mb-3">
                                <label for="Event_Date" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-date me-1"></i>Event Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control form-control-custom" id="Event_Date"
                                       name="Event_Date" required
                                       value="<?php echo htmlspecialchars($event['Event_Date'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <!-- Event Time -->
                            <div class="col-md-6 mb-3">
                                <label for="Event_Time" class="form-label fw-semibold">
                                    <i class="bi bi-clock me-1"></i>Event Time <span class="text-danger">*</span>
                                </label>
                                <input type="time" class="form-control form-control-custom" id="Event_Time"
                                       name="Event_Time" required
                                       value="<?php echo htmlspecialchars($event['Event_Time'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Organizer Name -->
                            <div class="col-md-6 mb-3">
                                <label for="Organizer" class="form-label fw-semibold">
                                    <i class="bi bi-person-badge me-1"></i>Organizer
                                </label>
                                <input type="text" class="form-control form-control-custom" id="Organizer"
                                       name="Organizer"
                                       value="<?php echo htmlspecialchars($event['Organizer'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <!-- Capacity -->
                            <div class="col-md-6 mb-3">
                                <label for="Capacity" class="form-label fw-semibold">
                                    <i class="bi bi-people me-1"></i>Capacity <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control form-control-custom" id="Capacity"
                                       name="Capacity" required min="1"
                                       value="<?php echo (int) $event['Capacity']; ?>">
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <a href="organizer_dashboard.php" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-accent">
                                <i class="bi bi-check-circle me-1"></i>Update Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
