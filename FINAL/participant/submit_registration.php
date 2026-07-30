<?php
/**
 * ============================================================
 *  SUBMIT REGISTRATION
 * ============================================================
 *  GET  → display event details + registration form
 *  POST → validate, check capacity (race-condition safe),
 *          prevent duplicates, then INSERT the registration.
 *
 *  All queries use PDO prepared statements; every output value
 *  is escaped with htmlspecialchars().
 * ============================================================
 */

/* ── Bootstrap: auth, DB, helpers ─────────────────────────── */
require_once '../includes/auth_check.php';
requireRole(['Participant']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

require_once '../src/PHPMailer.php';
require_once '../src/SMTP.php';
require_once '../src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ── Current user ─────────────────────────────────────────── */
$user_id = $_SESSION['user_id'];

/* ==============================================================
   POST – Process Registration
   ============================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ── 1. CSRF token validation ─────────────────────────── */
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        setFlashMessage('error', 'Invalid CSRF token. Please try again.');
        header('Location: browse_events.php');
        exit;
    }

    /* ── 2. Sanitise inputs ───────────────────────────────── */
    $event_id             = (int) ($_POST['event_id'] ?? 0);
    $mobile               = trim($_POST['mobile'] ?? '');
    $college_organization = trim($_POST['college_organization'] ?? '');

    /* Basic validation */
    if ($event_id <= 0) {
        setFlashMessage('error', 'Invalid event selected.');
        header('Location: browse_events.php');
        exit;
    }

    /* ── 3. Race-condition-safe capacity re-check ─────────── */
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS cnt FROM registrations WHERE Event_ID = ? AND Status IN ("Confirmed", "Pending")'
    );
    $stmt->execute([$event_id]);
    $count = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    $stmt2 = $pdo->prepare('SELECT Capacity, Event_Name, Status FROM events WHERE Event_ID = ?');
    $stmt2->execute([$event_id]);
    $event_row = $stmt2->fetch(PDO::FETCH_ASSOC);

    if (!$event_row) {
        setFlashMessage('error', 'Event not found.');
        header('Location: browse_events.php');
        exit;
    }

    if ($event_row['Status'] !== 'Approved') {
        setFlashMessage('error', 'This event is not yet approved for registration.');
        header('Location: browse_events.php');
        exit;
    }

    $capacity   = (int) $event_row['Capacity'];
    $event_name = $event_row['Event_Name'];

    if ($count >= $capacity) {
        setFlashMessage('error', 'This event is full. Registration cannot be completed.');
        header('Location: browse_events.php');
        exit;
    }

    /* ── 4. Prevent duplicate registration ────────────────── */
    $stmt_dup = $pdo->prepare(
        "SELECT Registration_ID FROM registrations
          WHERE User_ID = ? AND Event_ID = ? AND Status != 'Cancelled'"
    );
    $stmt_dup->execute([$user_id, $event_id]);

    if ($stmt_dup->fetch()) {
        setFlashMessage('info', 'You are already registered for this event.');
        header('Location: my_registrations.php');
        exit;
    }

    /* ── 5. INSERT registration ───────────────────────────── */
    $stmt_insert = $pdo->prepare(
        "INSERT INTO registrations (User_ID, Event_ID, Registration_Date, Status, College_Organization)
         VALUES (?, ?, CURDATE(), 'Pending', ?)"
    );
    $stmt_insert->execute([$user_id, $event_id, $college_organization]);

    /* ── 6. Update user mobile if changed ─────────────────── */
    if ($mobile !== '') {
        $stmt_mobile = $pdo->prepare(
            'UPDATE users SET Mobile = ? WHERE User_ID = ?'
        );
        $stmt_mobile->execute([$mobile, $user_id]);
    }



    /* ── 8. Store modal session data & redirect ────────────── */
    $_SESSION['registration_success_modal'] = [
        'event_name' => $event_name,
        'user_email' => $user_email
    ];

    setFlashMessage(
        'success',
        'Registration requested! Please wait for the organizer to accept your request.'
    );
    header('Location: my_registrations.php');
    exit;
}

/* ==============================================================
   GET – Display Registration Form
   ============================================================== */

/* ── Read event_id from query string ──────────────────────── */
$event_id = (int) ($_GET['event_id'] ?? 0);

if ($event_id <= 0) {
    setFlashMessage('error', 'No event specified.');
    header('Location: browse_events.php');
    exit;
}

/* ── Fetch event details ──────────────────────────────────── */
$stmt_event = $pdo->prepare('
    SELECT e.*, u.Name as Organizer_Name, u.Email as Organizer_Email, u.Mobile as Organizer_Mobile 
    FROM events e 
    JOIN users u ON e.created_by = u.User_ID 
    WHERE e.Event_ID = ?
');
$stmt_event->execute([$event_id]);
$event = $stmt_event->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    setFlashMessage('error', 'Event not found.');
    header('Location: browse_events.php');
    exit;
}

if ($event['Status'] !== 'Approved') {
    setFlashMessage('error', 'This event is not yet approved for registration.');
    header('Location: browse_events.php');
    exit;
}

/* ── Check capacity ───────────────────────────────────────── */
$stmt_cap = $pdo->prepare(
    "SELECT COUNT(*) AS cnt FROM registrations
      WHERE Event_ID = ? AND Status IN ('Confirmed', 'Pending')"
);
$stmt_cap->execute([$event_id]);
$filled   = (int) $stmt_cap->fetch(PDO::FETCH_ASSOC)['cnt'];
$capacity = (int) $event['Capacity'];
$available_spots = max(0, $capacity - $filled);

if ($filled >= $capacity) {
    setFlashMessage('error', 'This event is full.');
    header('Location: browse_events.php');
    exit;
}

/* ── Check if already registered ──────────────────────────── */
$stmt_already = $pdo->prepare(
    "SELECT Registration_ID FROM registrations
      WHERE User_ID = ? AND Event_ID = ? AND Status != 'Cancelled'"
);
$stmt_already->execute([$user_id, $event_id]);

if ($stmt_already->fetch()) {
    setFlashMessage('info', 'You are already registered for this event.');
    header('Location: my_registrations.php');
    exit;
}

/* ── Fetch current user details for pre-fill ──────────────── */
$stmt_user = $pdo->prepare(
    'SELECT Name, Email, Mobile FROM users WHERE User_ID = ?'
);
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

/* ── Generate CSRF token ──────────────────────────────────── */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

/* ── Render page ──────────────────────────────────────────── */
$page_title = 'Register for Event';
require_once '../includes/header.php';
?>

<!-- ============================================================
     REGISTRATION FORM
     ============================================================ -->
<div class="fade-in">

    <!-- Page header -->
    <div class="page-header mb-4">
        <h2><i class="bi bi-pencil-square me-2"></i>Register for Event</h2>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card-custom glass-card p-4">

                <!-- ── Event Details Summary ──────────────── -->
                <div class="mb-4 p-3 rounded" style="background: var(--bg-secondary, #f8f9fa);">
                    <h5 class="fw-bold mb-2">
                        <?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?>
                    </h5>
                    <ul class="list-unstyled mb-0 small">
                        <li><i class="bi bi-calendar3 me-1"></i>
                            <strong>Date:</strong>
                            <?php echo htmlspecialchars($event['Event_Date'], ENT_QUOTES, 'UTF-8'); ?>
                        </li>
                        <li><i class="bi bi-clock me-1"></i>
                            <strong>Time:</strong>
                            <?php echo htmlspecialchars($event['Event_Time'] ?? 'TBD', ENT_QUOTES, 'UTF-8'); ?>
                        </li>
                        <li><i class="bi bi-geo-alt me-1"></i>
                            <strong>Venue:</strong>
                            <?php echo htmlspecialchars($event['Venue'], ENT_QUOTES, 'UTF-8'); ?>
                        </li>
                        <li><i class="bi bi-people me-1"></i>
                            <strong>Available Spots:</strong>
                            <?php echo $available_spots; ?> / <?php echo $capacity; ?>
                        </li>
                        <hr class="my-2" style="border-color: rgba(0,0,0,0.1);">
                        <li><i class="bi bi-person-badge me-1"></i>
                            <strong>Organizer:</strong>
                            <?php echo htmlspecialchars($event['Organizer_Name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                        </li>
                        <li><i class="bi bi-envelope-at me-1"></i>
                            <strong>Contact Email:</strong>
                            <a href="mailto:<?php echo htmlspecialchars($event['Organizer_Email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($event['Organizer_Email'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- ── Registration Form ──────────────────── -->
                <form method="POST" action="submit_registration.php">

                    <!-- Hidden fields -->
                    <input type="hidden" name="event_id" value="<?php echo (int) $event['Event_ID']; ?>">
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

                    <!-- Name (readonly, pre-filled from session/users) -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text"
                               id="name"
                               class="form-control form-control-custom"
                               value="<?php echo htmlspecialchars($user['Name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                               readonly>
                    </div>

                    <!-- Email (readonly, pre-filled) -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email"
                               id="email"
                               class="form-control form-control-custom"
                               value="<?php echo htmlspecialchars($user['Email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                               readonly>
                    </div>

                    <!-- Mobile (editable, pre-filled) -->
                    <div class="mb-3">
                        <label for="mobile" class="form-label">Mobile Number</label>
                        <input type="text"
                               id="mobile"
                               name="mobile"
                               class="form-control form-control-custom"
                               placeholder="Enter your mobile number"
                               value="<?php echo htmlspecialchars($user['Mobile'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <!-- College / Organization (editable) -->
                    <div class="mb-3">
                        <label for="college_organization" class="form-label">College / Organization</label>
                        <input type="text"
                               id="college_organization"
                               name="college_organization"
                               class="form-control form-control-custom"
                               placeholder="Enter your college or organization name">
                    </div>

                    <!-- Submit button -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="browse_events.php" class="btn btn-outline-accent me-md-2">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                        <button type="submit" class="btn btn-accent">
                            <i class="bi bi-check-lg me-1"></i>Confirm Registration
                        </button>
                    </div>
                </form>

            </div><!-- /.card-custom -->
        </div>
    </div>

</div><!-- /.fade-in -->

<?php
/* ── Footer ───────────────────────────────────────────────── */
require_once '../includes/footer.php';
?>
