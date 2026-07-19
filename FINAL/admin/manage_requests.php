<?php
/**
 * Manage Requests
 * ---------------
 * Admin panel to review and approve/reject pending Organizer accounts
 * and pending Event creations.
 */

require_once '../includes/auth_check.php';
requireRole(['Admin']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

// Fetch pending organizers
$stmt_orgs = $pdo->query("SELECT User_ID, Name, Email, Mobile, created_at FROM users WHERE Role = 'Organizer' AND Account_Status = 'Pending' ORDER BY created_at DESC");
$pending_organizers = $stmt_orgs->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending events
$stmt_events = $pdo->query(
    "SELECT e.Event_ID, e.Event_Name, e.Description, e.Venue, e.Event_Date, e.Event_Time, e.Capacity, u.Name as OrganizerName, e.created_at
     FROM events e
     LEFT JOIN users u ON e.created_by = u.User_ID
     WHERE e.Status = 'Pending'
     ORDER BY e.created_at DESC"
);
$pending_events = $stmt_events->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manage Requests";
require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        <i class="bi bi-inbox me-2"></i>Manage Requests
    </h2>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="requestTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold" id="organizers-tab" data-bs-toggle="tab" data-bs-target="#organizers" type="button" role="tab" aria-controls="organizers" aria-selected="true">
            Organizer Requests
            <?php if (count($pending_organizers) > 0): ?>
                <span class="badge bg-danger ms-1"><?php echo count($pending_organizers); ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold" id="events-tab" data-bs-toggle="tab" data-bs-target="#events" type="button" role="tab" aria-controls="events" aria-selected="false">
            Event Requests
            <?php if (count($pending_events) > 0): ?>
                <span class="badge bg-danger ms-1"><?php echo count($pending_events); ?></span>
            <?php endif; ?>
        </button>
    </li>
</ul>

<div class="tab-content" id="requestTabsContent">
    <!-- Organizers Tab -->
    <div class="tab-pane fade show active" id="organizers" role="tabpanel" aria-labelledby="organizers-tab">
        <div class="card glass-card">
            <div class="card-body">
                <?php if (empty($pending_organizers)): ?>
                    <div class="alert alert-info mb-0 text-center">
                        <i class="bi bi-check-circle me-1"></i> No pending organizer requests.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Date Registered</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_organizers as $org): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($org['Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($org['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($org['Mobile'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y H:i', strtotime($org['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-end">
                                            <form action="process_request.php" method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="type" value="organizer">
                                                <input type="hidden" name="id" value="<?php echo $org['User_ID']; ?>">
                                                
                                                <button type="submit" name="action" value="approve" class="btn btn-sm btn-success me-1">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </button>
                                                <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this organizer?');">
                                                    <i class="bi bi-x-lg"></i> Reject
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
    </div>

    <!-- Events Tab -->
    <div class="tab-pane fade" id="events" role="tabpanel" aria-labelledby="events-tab">
        <div class="card glass-card">
            <div class="card-body">
                <?php if (empty($pending_events)): ?>
                    <div class="alert alert-info mb-0 text-center">
                        <i class="bi bi-check-circle me-1"></i> No pending event requests.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Event Name</th>
                                    <th>Organizer</th>
                                    <th>Date / Time</th>
                                    <th>Venue</th>
                                    <th>Capacity</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_events as $event): ?>
                                    <tr>
                                        <td class="fw-bold">
                                            <?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?><br>
                                            <small class="text-muted text-truncate d-inline-block" style="max-width: 200px;" title="<?php echo htmlspecialchars($event['Description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($event['Description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </small>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['OrganizerName'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars(date('d M Y', strtotime($event['Event_Date'])), ENT_QUOTES, 'UTF-8'); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($event['Event_Time'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['Venue'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($event['Capacity'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-end text-nowrap">
                                            <form action="process_request.php" method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="type" value="event">
                                                <input type="hidden" name="id" value="<?php echo $event['Event_ID']; ?>">
                                                
                                                <button type="submit" name="action" value="approve" class="btn btn-sm btn-success me-1">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </button>
                                                <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this event?');">
                                                    <i class="bi bi-x-lg"></i> Reject
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
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
