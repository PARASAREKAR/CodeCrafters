<?php
/**
 * Admin Support Messages Viewer
 * ----------------------------
 * Displays and allows management of user contact messages recorded
 * from the Contact Us page.
 */

require_once '../includes/auth_check.php';
requireRole(['Admin']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

// ── Handle POST Action (Delete message) ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // CSRF validation
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Invalid CSRF token. Please try again.', 'danger');
        header('Location: view_messages.php');
        exit;
    }

    $message_id = isset($_POST['message_id']) ? (int) $_POST['message_id'] : 0;
    if ($message_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$message_id]);
        setFlashMessage('Support message deleted successfully.', 'success');
    }
    header('Location: view_messages.php');
    exit;
}

// ── Fetch Support Messages ───────────────────────────────────
$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY submitted_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate CSRF token
$csrfToken = generateCsrfToken();

$pageTitle = "Support Messages";
require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-chat-dots-fill me-2 text-accent"></i>Support Messages</h2>
        <p class="text-muted mb-0">Manage customer inquiries submitted via the Contact Us form.</p>
    </div>
</div>

<div class="card glass-card" data-aos="fade-up">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3 border-0">
        <h5 class="fw-bold mb-0">📋 Inbox</h5>
        <span class="badge bg-accent-light text-accent px-3 py-2" style="border-radius: 12px; font-weight: 600;">
            <?php echo count($messages); ?> messages
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($messages)): ?>
            <div class="p-5 text-center text-muted">
                <i class="bi bi-envelope-open fs-1 mb-2"></i>
                <p class="mb-0">Inbox is empty. No user messages recorded.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="min-width:130px;">Sender</th>
                            <th style="min-width:160px;">Email</th>
                            <th style="max-width:200px;">Subject</th>
                            <th style="min-width:150px;">Submitted At</th>
                            <th class="pe-4 text-end" style="min-width:180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                            <tr class="table-custom-row">
                                <td class="ps-4 fw-semibold" style="max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo htmlspecialchars($msg['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td style="max-width:170px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <a href="mailto:<?php echo htmlspecialchars($msg['email'], ENT_QUOTES, 'UTF-8'); ?>" class="text-muted-link" title="<?php echo htmlspecialchars($msg['email'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($msg['email'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </td>
                                <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo htmlspecialchars($msg['subject'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($msg['subject'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-muted"><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($msg['submitted_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="pe-4 text-end">
                                    <!-- View Details Button -->
                                    <button class="btn btn-sm btn-outline-accent me-2" 
                                            onclick="openMessageModal(<?php echo htmlspecialchars(json_encode($msg), ENT_QUOTES, 'UTF-8'); ?>)">
                                        <i class="bi bi-eye me-1"></i>View
                                    </button>

                                    <!-- Reply Button -->
                                    <a href="mailto:<?php echo htmlspecialchars($msg['email'], ENT_QUOTES, 'UTF-8'); ?>?subject=Re: <?php echo rawurlencode($msg['subject']); ?>" 
                                       class="btn btn-sm btn-accent me-2">
                                        <i class="bi bi-reply me-1"></i>Reply
                                    </a>

                                    <!-- Delete Button Form -->
                                    <form action="view_messages.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================
     MESSAGE DETAIL MODAL
     ============================================================ -->
<div class="modal fade" id="messageDetailModal" tabindex="-1" aria-labelledby="messageDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-card border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="messageDetailModalLabel"><i class="bi bi-envelope-paper me-2 text-accent"></i>Message details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="small text-muted fw-bold d-block">Sender Name</label>
                    <div id="modalSenderName" class="fw-semibold text-primary"></div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted fw-bold d-block">Sender Email</label>
                    <a id="modalSenderEmail" href="#" class="text-accent text-decoration-none"></a>
                </div>
                <div class="mb-3">
                    <label class="small text-muted fw-bold d-block">Subject</label>
                    <div id="modalSubject" class="fw-semibold text-primary"></div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted fw-bold d-block">Message Content</label>
                    <div id="modalMessage" class="p-3 bg-glass text-muted" style="border-radius: 12px; border: 1px solid var(--border); max-height: 300px; overflow-y: auto; white-space: pre-wrap; word-break: break-word; overflow-wrap: break-word; word-wrap: break-word; line-height: 1.6;"></div>
                </div>
                <div class="mb-0">
                    <label class="small text-muted fw-bold d-block">Submitted At</label>
                    <div id="modalSubmittedAt" class="text-muted small"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a id="modalReplyBtn" href="#" class="btn btn-accent px-4">
                    <i class="bi bi-reply me-1"></i>Reply via Mail
                </a>
            </div>
        </div>
    </div>
</div>

<script>
let detailModal;
document.addEventListener("DOMContentLoaded", function() {
    detailModal = new bootstrap.Modal(document.getElementById('messageDetailModal'));
});

function openMessageModal(msg) {
    document.getElementById('modalSenderName').textContent = msg.name;
    
    const emailEl = document.getElementById('modalSenderEmail');
    emailEl.textContent = msg.email;
    emailEl.href = 'mailto:' + encodeURIComponent(msg.email);
    
    document.getElementById('modalSubject').textContent = msg.subject;
    document.getElementById('modalMessage').textContent = msg.message;
    document.getElementById('modalSubmittedAt').textContent = msg.submitted_at;
    
    // Update reply link
    document.getElementById('modalReplyBtn').href = 'mailto:' + encodeURIComponent(msg.email) + '?subject=Re: ' + encodeURIComponent(msg.subject);
    
    // Show modal
    detailModal.show();
}
</script>

<?php require_once '../includes/footer.php'; ?>
