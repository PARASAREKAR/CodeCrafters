<?php
/**
 * Toggle / Mark Attendance
 * 
 * Displays confirmed participants for an event with a dropdown
 * (Present / Absent) per row.  On POST, performs an UPSERT:
 *  - UPDATE if an attendance record already exists
 *  - INSERT if no record exists yet
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

// ── Handle POST – save attendance ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $attendanceData = $_POST['attendance'] ?? [];

    // Prepared statements for check / update / insert
    $stmtExists = $pdo->prepare(
        "SELECT Attendance_ID FROM attendance WHERE Registration_ID = ?"
    );
    $stmtUpdate = $pdo->prepare(
        "UPDATE attendance SET Status = ? WHERE Registration_ID = ?"
    );
    $stmtInsert = $pdo->prepare(
        "INSERT INTO attendance (Registration_ID, Status) VALUES (?, ?)"
    );

    foreach ($attendanceData as $reg_id => $status) {
        $reg_id = (int) $reg_id;
        $status = trim($status);

        // Only process valid statuses
        if (!in_array($status, ['Present', 'Absent'], true)) {
            continue;
        }

        // Check if attendance record already exists
        $stmtExists->execute([$reg_id]);

        if ($stmtExists->fetch()) {
            // ── UPDATE existing record ─────────────────────────
            $stmtUpdate->execute([$status, $reg_id]);
        } else {
            // ── INSERT new record ──────────────────────────────
            $stmtInsert->execute([$reg_id, $status]);
        }
    }

    setFlashMessage('Attendance saved successfully!', 'success');
    header("Location: toggle_attendance.php?event_id=$event_id");
    exit;
}

// ── Fetch confirmed participants with existing attendance ──────
$stmtParts = $pdo->prepare(
    "SELECT r.Registration_ID, u.Name, u.Email,
            a.Status AS Attendance_Status
     FROM registrations r
     JOIN users u ON r.User_ID = u.User_ID
     LEFT JOIN attendance a ON r.Registration_ID = a.Registration_ID
     WHERE r.Event_ID = ? AND r.Status = 'Confirmed'
     ORDER BY u.Name ASC"
);
$stmtParts->execute([$event_id]);
$participants = $stmtParts->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Mark Attendance";
require_once '../includes/header.php';
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="bi bi-check2-square me-2"></i>Mark Attendance
        </h2>
        <a href="manage_participants.php?event_id=<?php echo (int) $event_id; ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Participants
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

    <div class="card card-custom glass-card">
        <div class="card-body">
            <?php if (empty($participants)): ?>
                <div class="alert alert-warning text-center mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    No confirmed participants found for this event.
                </div>
            <?php else: ?>
                <form method="POST" action="toggle_attendance.php?event_id=<?php echo (int) $event_id; ?>">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th style="width: 200px;">Attendance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $i => $p): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><?php echo htmlspecialchars($p['Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($p['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <select name="attendance[<?php echo (int) $p['Registration_ID']; ?>]"
                                                    class="form-select form-control-custom">
                                                <option value="">--Select--</option>
                                                <option value="Present"
                                                    <?php echo ($p['Attendance_Status'] === 'Present') ? 'selected' : ''; ?>>
                                                    Present
                                                </option>
                                                <option value="Absent"
                                                    <?php echo ($p['Attendance_Status'] === 'Absent') ? 'selected' : ''; ?>>
                                                    Absent
                                                </option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-accent">
                            <i class="bi bi-save me-1"></i> Save Attendance
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
