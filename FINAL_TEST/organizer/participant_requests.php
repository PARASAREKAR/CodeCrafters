<?php
/**
 * Participant Requests
 * 
 * Centralized dashboard for an organizer to view and accept/reject 
 * pending participant registrations across all their events.
 */

require_once '../includes/auth_check.php';
requireRole(['Organizer']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

$user_id = $_SESSION['user_id'];

// ── Generate CSRF Token ─────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// ── Handle Reject Action ───────────────────────────────────────
$action = $_GET['action'] ?? '';
$reg_id = isset($_GET['reg_id']) ? (int) $_GET['reg_id'] : 0;

if ($action === 'reject' && $reg_id > 0) {
    // Verify ownership of the event linked to this registration
    $stmtCheck = $pdo->prepare(
        "SELECT r.Registration_ID FROM registrations r
         JOIN events e ON r.Event_ID = e.Event_ID
         WHERE r.Registration_ID = ? AND e.created_by = ?"
    );
    $stmtCheck->execute([$reg_id, $user_id]);

    if ($stmtCheck->fetch()) {
        $stmtReject = $pdo->prepare(
            "UPDATE registrations SET Status = 'Cancelled' WHERE Registration_ID = ? AND Status = 'Pending'"
        );
        $stmtReject->execute([$reg_id]);
        setFlashMessage('Participant request rejected successfully.', 'warning');
    } else {
        setFlashMessage('Access denied or invalid request.', 'danger');
    }
    
    header('Location: participant_requests.php');
    exit;
}

// ── Fetch Pending Requests ─────────────────────────────────────
$stmt = $pdo->prepare(
    "SELECT r.*, 
            u.Name AS participant_name, u.Email AS participant_email, u.Mobile,
            e.Event_Name, e.Event_Date
     FROM registrations r
     JOIN users u  ON r.User_ID = u.User_ID
     JOIN events e ON r.Event_ID = e.Event_ID
     WHERE r.Status = 'Pending' AND r.organizer_approved = 0 AND e.created_by = ?
     ORDER BY r.Registration_Date DESC, r.Registration_ID DESC"
);
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Participant Requests';
require_once '../includes/header.php';
?>

<div class="fade-in">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-inbox me-2"></i>Participant Requests</h2>
    </div>

    <?php displayFlashMessage(); ?>

    <div class="card-custom glass-card mb-4">
        <div class="card-body p-0">
            <?php if (empty($requests)): ?>
                <div class="text-center p-5 text-muted">
                    <i class="bi bi-inbox fs-1"></i>
                    <p class="mt-3">No pending participant requests found for any of your events.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>#</th>
                                <th>Event Name</th>
                                <th>Participant Name</th>
                                <th>Email</th>
                                <th>Date Requested</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $i => $req): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($req['Event_Name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($req['participant_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($req['participant_email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($req['Registration_Date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                    
                                    <td class="text-center">
                                        <!-- Accept Registration Form -->
                                        <form method="POST" action="accept_registration.php" class="d-inline"
                                              onsubmit="return confirm('Accept this registration?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="registration_id" value="<?php echo (int)$req['Registration_ID']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success me-1" title="Accept Request">
                                                <i class="bi bi-check-circle"></i> Accept
                                            </button>
                                        </form>
                                        
                                        <!-- Reject Registration -->
                                        <a href="participant_requests.php?action=reject&reg_id=<?php echo (int) $req['Registration_ID']; ?>"
                                           class="btn btn-sm btn-outline-danger" title="Reject Request"
                                           onclick="return confirm('Are you sure you want to reject this request?');">
                                            <i class="bi bi-x-circle"></i> Reject
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
