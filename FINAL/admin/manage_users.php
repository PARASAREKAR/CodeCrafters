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

// ── Fetch all users ordered by creation date ───────────────────
$stmtUsers = $pdo->query(
    "SELECT User_ID, Name, Email, Mobile, Role, created_at
     FROM users
     ORDER BY created_at DESC"
);
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// ── Page title for header include ──────────────────────────────
$pageTitle = "Manage Users";
require_once '../includes/header.php';
?>

<!-- ============================================================
     PAGE HEADER & SEARCH BAR
     ============================================================ -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">👤 Manage Users</h2>
    <span class="badge bg-secondary fs-6"><?php echo count($users); ?> users</span>
</div>

<!-- Search Bar -->
<div class="search-wrapper mb-4">
    <div class="input-group" style="max-width: 400px;">
        <span class="input-group-text" style="background: var(--bg-card); border-color: var(--border-color); color: var(--text-muted);">
            🔍
        </span>
        <input type="text"
               class="form-control search-input"
               placeholder="Search users by name, email, or role..."
               style="background: var(--bg-card); border-color: var(--border-color); color: var(--text-primary);">
    </div>
</div>

<!-- ============================================================
     USERS TABLE
     ============================================================ -->
<div class="card glass-card">
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="alert alert-info text-center">
                No users found in the system.
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
                            <th>Role</th>
                            <th>Joined Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                            <?php
                                // Determine role badge class
                                $roleBadge = 'bg-secondary';
                                switch ($user['Role']) {
                                    case 'Admin':       $roleBadge = 'bg-info';    break;
                                    case 'Organizer':   $roleBadge = 'bg-warning text-dark'; break;
                                    case 'Participant': $roleBadge = 'bg-success'; break;
                                }
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($user['Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($user['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($user['Mobile'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="badge <?php echo $roleBadge; ?>">
                                        <?php echo htmlspecialchars($user['Role'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars(
                                        date('d M Y', strtotime($user['created_at'])),
                                        ENT_QUOTES, 'UTF-8'
                                    ); ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($user['Role'] !== 'Admin'): ?>
                                        <!-- Delete button – protected against Admin deletion -->
                                        <a href="manage_users.php?delete_id=<?php echo (int) $user['User_ID']; ?>"
                                           class="btn btn-sm btn-outline-danger confirm-action"
                                           title="Delete User"
                                           onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                            🗑️ Delete
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Protected</span>
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

<?php require_once '../includes/footer.php'; ?>
