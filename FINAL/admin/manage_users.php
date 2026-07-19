<?php
/**
 * Manage Users
 * ------------
 * Admin-only page for viewing and deleting system users.
 * Features:
 * - Searchable table of all users
 * - Role-based colored badges
 * - Delete action (Admin accounts protected from deletion)
 * - Flash messages for feedback
 *
 * @requires auth_check.php  – session bootstrap & role guard
 * @requires db_connect.php  – PDO $pdo connection
 * @requires helpers.php     – flash messages, sanitize, etc.
 */

require_once '../includes/auth_check.php';
requireRole(['Admin']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

// ── Handle DELETE request via GET parameter ────────────────────
if (isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];

    try {
        // Prevent deletion of Admin accounts for safety
        $stmtDelete = $pdo->prepare(
            "DELETE FROM users WHERE User_ID = ? AND Role != 'Admin'"
        );
        $stmtDelete->execute([$deleteId]);

        if ($stmtDelete->rowCount() > 0) {
            setFlashMessage('success', 'User deleted successfully.');
        } else {
            setFlashMessage('danger', 'Cannot delete this user. Admin accounts are protected.');
        }
    } catch (PDOException $e) {
        error_log("User deletion error: " . $e->getMessage());
        setFlashMessage('danger', 'An error occurred while deleting the user.');
    }

    // Redirect back to remove the delete_id from the URL
    redirectTo('manage_users.php');
}

// ── Fetch Participants ───────────────────────────────────────────
$stmtParticipants = $pdo->query(
    "SELECT User_ID, Name, Email, Mobile, Role, created_at
     FROM users
     WHERE Role = 'Participant'
     ORDER BY created_at DESC"
);
$participants = $stmtParticipants->fetchAll(PDO::FETCH_ASSOC);

// ── Fetch Organizers ────────────────────────────────────────────
$stmtOrganizers = $pdo->query(
    "SELECT User_ID, Name, Email, Mobile, Role, Account_Status, created_at
     FROM users
     WHERE Role = 'Organizer'
     ORDER BY created_at DESC"
);
$organizers = $stmtOrganizers->fetchAll(PDO::FETCH_ASSOC);

// ── Page title for header include ──────────────────────────────
$pageTitle = "Manage Users";
require_once '../includes/header.php';
?>

<!-- ============================================================
     PAGE HEADER & SEARCH BAR
     ============================================================ -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">👤 Manage Users</h2>
</div>

<!-- Search Bar -->
<div class="search-wrapper mb-4">
    <div class="input-group" style="max-width: 400px;">
        <span class="input-group-text" style="background: var(--bg-card); border-color: var(--border-color); color: var(--text-muted);">
            🔍
        </span>
        <input type="text"
               class="form-control search-input"
               placeholder="Search users by name, email..."
               style="background: var(--bg-card); border-color: var(--border-color); color: var(--text-primary);">
    </div>
</div>

<!-- ============================================================
     USER TABS
     ============================================================ -->
<ul class="nav nav-tabs mb-4" id="userTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold" id="participants-tab" data-bs-toggle="tab" data-bs-target="#participants" type="button" role="tab" aria-controls="participants" aria-selected="true">
            Participants <span class="badge bg-secondary ms-1"><?php echo count($participants); ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold" id="organizers-tab" data-bs-toggle="tab" data-bs-target="#organizers" type="button" role="tab" aria-controls="organizers" aria-selected="false">
            Organizers <span class="badge bg-secondary ms-1"><?php echo count($organizers); ?></span>
        </button>
    </li>
</ul>

<div class="tab-content" id="userTabsContent">
    
    <!-- Participants Tab -->
    <div class="tab-pane fade show active" id="participants" role="tabpanel" aria-labelledby="participants-tab">
        <div class="card glass-card">
            <div class="card-body">
                <?php if (empty($participants)): ?>
                    <div class="alert alert-info text-center mb-0">
                        No participants found in the system.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle searchable-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Joined Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $index => $user): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($user['Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($user['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($user['Mobile'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center">
                                            <a href="manage_users.php?delete_id=<?php echo (int) $user['User_ID']; ?>"
                                               class="btn btn-sm btn-outline-danger confirm-action"
                                               title="Delete User"
                                               onclick="return confirm('Are you sure you want to delete this participant? This action cannot be undone.');">
                                                🗑️ Delete
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

    <!-- Organizers Tab -->
    <div class="tab-pane fade" id="organizers" role="tabpanel" aria-labelledby="organizers-tab">
        <div class="card glass-card">
            <div class="card-body">
                <?php if (empty($organizers)): ?>
                    <div class="alert alert-info text-center mb-0">
                        No organizers found in the system.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle searchable-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                    <th>Joined Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($organizers as $index => $user): ?>
                                    <?php
                                        $statusBadge = 'bg-secondary';
                                        if (isset($user['Account_Status'])) {
                                            switch ($user['Account_Status']) {
                                                case 'Approved': $statusBadge = 'bg-success'; break;
                                                case 'Pending':  $statusBadge = 'bg-warning text-dark'; break;
                                                case 'Rejected': $statusBadge = 'bg-danger'; break;
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($user['Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($user['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($user['Mobile'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $statusBadge; ?>">
                                                <?php echo htmlspecialchars($user['Account_Status'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center">
                                            <a href="manage_users.php?delete_id=<?php echo (int) $user['User_ID']; ?>"
                                               class="btn btn-sm btn-outline-danger confirm-action"
                                               title="Delete User"
                                               onclick="return confirm('Are you sure you want to delete this organizer? This action cannot be undone.');">
                                                🗑️ Delete
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

</div>

<?php require_once '../includes/footer.php'; ?>
