<?php
/**
 * Manage Users
 * ------------
 * Admin-only page for viewing, deleting, and editing system users.
 * Features:
 * - Tabs for Participants, Organizers, and Admins
 * - Searchable tables of all users
 * - Role-based colored badges
 * - Profile Edit Modal (Name, Email, Mobile, Role, Account Status)
 * - Safe Delete actions (Current Admin protected from self-deletion)
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
    if (!validateCsrfToken($_GET['csrf_token'] ?? '')) {
        setFlashMessage('danger', 'Invalid security token for deletion.');
        redirectTo('manage_users.php');
    }

    $deleteId = (int) $_GET['delete_id'];
    $currentAdminId = (int) ($_SESSION['user_id'] ?? 0);

    try {
        if ($deleteId === $currentAdminId) {
            setFlashMessage('danger', 'You cannot delete your own Administrator account.');
        } else {
            // Delete user
            $stmtDelete = $pdo->prepare("DELETE FROM users WHERE User_ID = ?");
            $stmtDelete->execute([$deleteId]);

            if ($stmtDelete->rowCount() > 0) {
                setFlashMessage('success', 'User account deleted successfully.');
            } else {
                setFlashMessage('danger', 'Failed to delete user. User not found.');
            }
        }
    } catch (PDOException $e) {
        error_log("User deletion error: " . $e->getMessage());
        setFlashMessage('danger', 'An error occurred while deleting the user.');
    }

    redirectTo('manage_users.php');
}

// ── Handle POST request for EDITING user details ───────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_user') {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('danger', 'Invalid security token. Please try again.');
        redirectTo('manage_users.php');
    }

    $editId = (int) ($_POST['user_id'] ?? 0);
    $name = trim(htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'));
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL));
    $mobile = trim(htmlspecialchars($_POST['mobile'] ?? '', ENT_QUOTES, 'UTF-8'));
    $role = trim(htmlspecialchars($_POST['role'] ?? '', ENT_QUOTES, 'UTF-8'));
    $status = trim(htmlspecialchars($_POST['status'] ?? '', ENT_QUOTES, 'UTF-8'));
    $currentAdminId = (int) ($_SESSION['user_id'] ?? 0);

    $errors = [];
    if (empty($name)) {
        $errors[] = "Name cannot be empty.";
    }
    if (!$email) {
        $errors[] = "Please enter a valid email address.";
    }
    if (!in_array($role, ['Participant', 'Organizer', 'Admin'])) {
        $errors[] = "Invalid role selected.";
    }
    if (!in_array($status, ['Approved', 'Pending', 'Rejected'])) {
        $errors[] = "Invalid account status selected.";
    }

    // Prevent changing own role away from Admin for safety
    if ($editId === $currentAdminId && $role !== 'Admin') {
        $errors[] = "You cannot change your own role. Demotion of self is blocked.";
    }

    // Check if email already in use by another user
    if ($email) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE Email = ? AND User_ID != ?");
        $stmtCheck->execute([$email, $editId]);
        if ($stmtCheck->fetchColumn() > 0) {
            $errors[] = "Email address is already registered to another user.";
        }
    }

    if (empty($errors)) {
        try {
            $stmtUpdate = $pdo->prepare(
                "UPDATE users SET Name = ?, Email = ?, Mobile = ?, Role = ?, Account_Status = ? WHERE User_ID = ?"
            );
            $stmtUpdate->execute([$name, $email, $mobile, $role, $status, $editId]);
            
            // If editing current logged-in admin's profile, update active session name
            if ($editId === $currentAdminId) {
                $_SESSION['user_name'] = $name;
            }
            
            setFlashMessage('success', 'User profile updated successfully.');
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Failed to update profile: ' . $e->getMessage());
        }
    } else {
        setFlashMessage('danger', implode('<br>', $errors));
    }

    redirectTo('manage_users.php');
}

// ── Fetch Participants ───────────────────────────────────────────
$stmtParticipants = $pdo->query(
    "SELECT User_ID, Name, Email, Mobile, Role, Account_Status, Profile_Pic, created_at
     FROM users
     WHERE Role = 'Participant'
     ORDER BY created_at DESC"
);
$participants = $stmtParticipants->fetchAll(PDO::FETCH_ASSOC);

// ── Fetch Organizers ────────────────────────────────────────────
$stmtOrganizers = $pdo->query(
    "SELECT User_ID, Name, Email, Mobile, Role, Account_Status, Profile_Pic, created_at
     FROM users
     WHERE Role = 'Organizer'
     ORDER BY created_at DESC"
);
$organizers = $stmtOrganizers->fetchAll(PDO::FETCH_ASSOC);

// ── Fetch Admins ────────────────────────────────────────────────
$stmtAdmins = $pdo->query(
    "SELECT User_ID, Name, Email, Mobile, Role, Account_Status, Profile_Pic, created_at
     FROM users
     WHERE Role = 'Admin'
     ORDER BY created_at DESC"
);
$admins = $stmtAdmins->fetchAll(PDO::FETCH_ASSOC);

// Generate CSRF token
$csrfToken = generateCsrfToken();

// ── Page title for header include ──────────────────────────────
$pageTitle = "Manage Users";
require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-people-fill me-2 text-accent"></i>Manage Users</h2>
        <p class="text-muted mb-0">Monitor platform accounts, modify profile details, and set roles.</p>
    </div>
</div>

<!-- Search Bar -->
<div class="search-wrapper mb-4" data-aos="fade-up">
    <div class="input-group" style="max-width: 400px;">
        <span class="input-group-text" style="background: var(--bg-card); border-color: var(--border); color: var(--text-muted);">
            <i class="bi bi-search"></i>
        </span>
        <input type="text"
               id="userSearchInput"
               class="form-control form-control-custom"
               placeholder="Search users by name, email..."
               onkeyup="filterUsersTable()">
    </div>
</div>

<!-- ============================================================
     USER TABS
     ============================================================ -->
<ul class="nav nav-tabs mb-4" id="userTabs" role="tablist" data-aos="fade-up">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold" id="participants-tab" data-bs-toggle="tab" data-bs-target="#participants" type="button" role="tab" aria-controls="participants" aria-selected="true">
            <i class="bi bi-person me-1"></i>Participants <span class="badge bg-accent-light text-accent ms-1"><?php echo count($participants); ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold" id="organizers-tab" data-bs-toggle="tab" data-bs-target="#organizers" type="button" role="tab" aria-controls="organizers" aria-selected="false">
            <i class="bi bi-person-workspace me-1"></i>Organizers <span class="badge bg-accent-light text-accent ms-1"><?php echo count($organizers); ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold" id="admins-tab" data-bs-toggle="tab" data-bs-target="#admins" type="button" role="tab" aria-controls="admins" aria-selected="false">
            <i class="bi bi-shield-lock me-1"></i>Admins <span class="badge bg-accent-light text-accent ms-1"><?php echo count($admins); ?></span>
        </button>
    </li>
</ul>

<div class="tab-content" id="userTabsContent" data-aos="fade-up" data-aos-delay="100">
    
    <!-- Participants Tab -->
    <div class="tab-pane fade show active" id="participants" role="tabpanel" aria-labelledby="participants-tab">
        <div class="card glass-card">
            <div class="card-body p-0">
                <?php if (empty($participants)): ?>
                    <div class="p-5 text-center text-muted mb-0">
                        <i class="bi bi-people fs-1 mb-2"></i>
                        <p class="mb-0">No participants registered in the system.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0 user-searchable-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                    <th>Joined Date</th>
                                    <th class="pe-4 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $index => $user): ?>
                                    <tr class="table-custom-row">
                                        <td class="ps-4 font-monospace text-muted"><?php echo $index + 1; ?></td>
                                        <td class="fw-semibold">
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if (!empty($user['Profile_Pic']) && file_exists('../' . $user['Profile_Pic'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($user['Profile_Pic'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                         alt="Avatar" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover; border: 1px solid var(--border);">
                                                <?php else: ?>
                                                    <i class="bi bi-person-circle fs-5 text-muted"></i>
                                                <?php endif; ?>
                                                <span><?php echo htmlspecialchars($user['Name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-muted"><?php echo htmlspecialchars($user['Mobile'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge bg-success-light text-success px-2 py-1.5" style="border-radius: 6px;">
                                                <?php echo htmlspecialchars($user['Account_Status'] ?? 'Approved', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted"><?php echo htmlspecialchars(date('d M Y', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="pe-4 text-end">
                                            <!-- Edit Profile -->
                                            <button class="btn btn-sm btn-outline-accent me-1" 
                                                    onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>)">
                                                <i class="bi bi-pencil-square me-1"></i>Edit
                                            </button>
                                            
                                            <!-- Delete -->
                                            <a href="manage_users.php?delete_id=<?php echo (int) $user['User_ID']; ?>&csrf_token=<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this participant? This action cannot be undone.');">
                                                <i class="bi bi-trash"></i>
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
            <div class="card-body p-0">
                <?php if (empty($organizers)): ?>
                    <div class="p-5 text-center text-muted mb-0">
                        <i class="bi bi-people fs-1 mb-2"></i>
                        <p class="mb-0">No organizers registered in the system.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0 user-searchable-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                    <th>Joined Date</th>
                                    <th class="pe-4 text-end">Actions</th>
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
                                    <tr class="table-custom-row">
                                        <td class="ps-4 font-monospace text-muted"><?php echo $index + 1; ?></td>
                                        <td class="fw-semibold">
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if (!empty($user['Profile_Pic']) && file_exists('../' . $user['Profile_Pic'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($user['Profile_Pic'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                         alt="Avatar" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover; border: 1px solid var(--border);">
                                                <?php else: ?>
                                                    <i class="bi bi-person-circle fs-5 text-muted"></i>
                                                <?php endif; ?>
                                                <span><?php echo htmlspecialchars($user['Name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-muted"><?php echo htmlspecialchars($user['Mobile'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $statusBadge; ?> px-2 py-1.5" style="border-radius: 6px;">
                                                <?php echo htmlspecialchars($user['Account_Status'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted"><?php echo htmlspecialchars(date('d M Y', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="pe-4 text-end">
                                            <!-- Edit Profile -->
                                            <button class="btn btn-sm btn-outline-accent me-1" 
                                                    onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>)">
                                                <i class="bi bi-pencil-square me-1"></i>Edit
                                            </button>
                                            
                                            <!-- Delete -->
                                            <a href="manage_users.php?delete_id=<?php echo (int) $user['User_ID']; ?>&csrf_token=<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this organizer? This action cannot be undone.');">
                                                <i class="bi bi-trash"></i>
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

    <!-- Admins Tab -->
    <div class="tab-pane fade" id="admins" role="tabpanel" aria-labelledby="admins-tab">
        <div class="card glass-card">
            <div class="card-body p-0">
                <?php if (empty($admins)): ?>
                    <div class="p-5 text-center text-muted mb-0">
                        <i class="bi bi-shield fs-1 mb-2"></i>
                        <p class="mb-0">No administrators registered in the system.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0 user-searchable-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                    <th>Joined Date</th>
                                    <th class="pe-4 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $index => $user): ?>
                                    <?php $isSelf = ($user['User_ID'] == ($_SESSION['user_id'] ?? 0)); ?>
                                    <tr class="table-custom-row">
                                        <td class="ps-4 font-monospace text-muted"><?php echo $index + 1; ?></td>
                                        <td class="fw-semibold">
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if (!empty($user['Profile_Pic']) && file_exists('../' . $user['Profile_Pic'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($user['Profile_Pic'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                         alt="Avatar" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover; border: 1px solid var(--border);">
                                                <?php else: ?>
                                                    <i class="bi bi-person-circle fs-5 text-muted"></i>
                                                <?php endif; ?>
                                                <span>
                                                    <?php echo htmlspecialchars($user['Name'], ENT_QUOTES, 'UTF-8'); ?>
                                                    <?php if ($isSelf): ?>
                                                        <span class="badge bg-accent-light text-accent ms-1.5 small">You</span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-muted"><?php echo htmlspecialchars($user['Mobile'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge bg-success-light text-success px-2 py-1.5" style="border-radius: 6px;">
                                                <?php echo htmlspecialchars($user['Account_Status'] ?? 'Approved', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted"><?php echo htmlspecialchars(date('d M Y', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="pe-4 text-end">
                                            <!-- Edit Profile -->
                                            <button class="btn btn-sm btn-outline-accent me-1" 
                                                    onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>)">
                                                <i class="bi bi-pencil-square me-1"></i>Edit
                                            </button>
                                            
                                            <!-- Delete -->
                                            <?php if ($isSelf): ?>
                                                <button class="btn btn-sm btn-outline-secondary" disabled title="You cannot delete yourself">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <a href="manage_users.php?delete_id=<?php echo (int) $user['User_ID']; ?>&csrf_token=<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to delete this administrator account? This action cannot be undone.');">
                                                    <i class="bi bi-trash"></i>
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

</div>

<!-- ============================================================
     EDIT PROFILE MODAL
     ============================================================ -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0 shadow-lg" style="border-radius: 20px;">
            <form action="manage_users.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" id="edit_user_id" name="user_id">
                
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="editUserModalLabel"><i class="bi bi-pencil-square me-2 text-accent"></i>Edit User Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label small text-muted fw-bold">Full Name</label>
                        <input type="text" class="form-control form-control-custom" id="edit_name" name="name" required placeholder="Enter name">
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label small text-muted fw-bold">Email Address</label>
                        <input type="email" class="form-control form-control-custom" id="edit_email" name="email" required placeholder="Enter email">
                    </div>
                    <div class="mb-3">
                        <label for="edit_mobile" class="form-label small text-muted fw-bold">Mobile Number</label>
                        <input type="text" class="form-control form-control-custom" id="edit_mobile" name="mobile" placeholder="Enter mobile">
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label small text-muted fw-bold">System Role</label>
                        <select class="form-select form-control-custom" id="edit_role" name="role" required>
                            <option value="Participant">Participant</option>
                            <option value="Organizer">Organizer</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label for="edit_status" class="form-label small text-muted fw-bold">Account Status</label>
                        <select class="form-select form-control-custom" id="edit_status" name="status" required>
                            <option value="Approved">Approved</option>
                            <option value="Pending">Pending</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-header border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent px-4">
                        <i class="bi bi-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let editModal;
document.addEventListener("DOMContentLoaded", function() {
    editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
});

function openEditModal(user) {
    document.getElementById('edit_user_id').value = user.User_ID;
    document.getElementById('edit_name').value = user.Name;
    document.getElementById('edit_email').value = user.Email;
    document.getElementById('edit_mobile').value = user.Mobile || '';
    document.getElementById('edit_role').value = user.Role;
    document.getElementById('edit_status').value = user.Account_Status || 'Approved';
    
    editModal.show();
}

function filterUsersTable() {
    const input = document.getElementById("userSearchInput");
    const filter = input.value.toLowerCase();
    
    // Search within the active tab's table rows
    const activePane = document.querySelector(".tab-pane.show.active");
    if (!activePane) return;
    
    const rows = activePane.querySelectorAll("tbody tr");
    rows.forEach(row => {
        const nameCell = row.cells[1];
        const emailCell = row.cells[2];
        const mobileCell = row.cells[3];
        
        const nameText = nameCell ? nameCell.textContent.toLowerCase() : '';
        const emailText = emailCell ? emailCell.textContent.toLowerCase() : '';
        const mobileText = mobileCell ? mobileCell.textContent.toLowerCase() : '';
        
        if (nameText.includes(filter) || emailText.includes(filter) || mobileText.includes(filter)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
