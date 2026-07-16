<?php
/**
 * ============================================================
 *  MY REGISTRATIONS
 * ============================================================
 *  Lists every registration the logged-in participant has made,
 *  ordered by most recent first. Displays status badges and a
 *  cancel action for active (Confirmed / Pending) registrations.
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

/* ── Current user ID ──────────────────────────────────────── */
$user_id = $_SESSION['user_id'];

/* ──────────────────────────────────────────────────────────
   Fetch all registrations for this user, joined with event
   details so we can display event name, date, time, venue.
   ────────────────────────────────────────────────────────── */
$stmt_regs = $pdo->prepare(
    "SELECT r.Registration_ID,
            r.Registration_Date,
            r.Status,
            e.Event_Name,
            e.Event_Date,
            e.Event_Time,
            e.Venue
       FROM registrations r
       JOIN events e ON r.Event_ID = e.Event_ID
      WHERE r.User_ID = ?
      ORDER BY r.Registration_Date DESC"
);
$stmt_regs->execute([$user_id]);
$registrations = $stmt_regs->fetchAll(PDO::FETCH_ASSOC);

/* ── Render page ──────────────────────────────────────────── */
$page_title = 'My Registrations';
require_once '../includes/header.php';
?>

<!-- ============================================================
     MY REGISTRATIONS CONTENT
     ============================================================ -->
<div class="fade-in">

    <!-- Page header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-journal-text me-2"></i>My Registrations</h2>
        <a href="browse_events.php" class="btn btn-accent">
            <i class="bi bi-plus-circle me-1"></i>Browse Events
        </a>
    </div>

    <!-- ── Search bar for filtering table rows via JS ─────── -->
    <div class="search-wrapper mb-3">
        <input type="text"
               class="form-control form-control-custom search-input"
               placeholder="Search registrations…"
               id="registrationSearch">
    </div>

    <?php if (empty($registrations)): ?>
        <!-- ── Empty state ────────────────────────────────── -->
        <div class="empty-state text-center py-5">
            <i class="bi bi-inbox fs-1 text-muted"></i>
            <p class="mt-3 text-muted">No registrations yet. Browse events to get started!</p>
            <a href="browse_events.php" class="btn btn-accent">
                <i class="bi bi-search me-1"></i>Browse Events
            </a>
        </div>
    <?php else: ?>
        <!-- ── Registrations Table ────────────────────────── -->
        <div class="table-responsive">
            <table class="table table-custom searchable-table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Event Name</th>
                        <th>Venue</th>
                        <th>Event Date</th>
                        <th>Event Time</th>
                        <th>Registration Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $row_number = 0;
                    foreach ($registrations as $reg):
                        $row_number++;

                        /* Determine badge class based on status */
                        $status = $reg['Status'] ?? 'Unknown';
                        switch ($status) {
                            case 'Confirmed':
                                $badge_class = 'badge-confirmed';
                                break;
                            case 'Cancelled':
                                $badge_class = 'badge-cancelled';
                                break;
                            case 'Pending':
                                $badge_class = 'badge-pending';
                                break;
                            default:
                                $badge_class = 'bg-secondary';
                        }

                        /* Can cancel only if Confirmed or Pending */
                        $can_cancel = in_array($status, ['Confirmed', 'Pending'], true);
                    ?>
                    <tr>
                        <td><?php echo $row_number; ?></td>
                        <td><?php echo htmlspecialchars($reg['Event_Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($reg['Venue'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($reg['Event_Date'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($reg['Event_Time'] ?? 'TBD', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($reg['Registration_Date'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <span class="badge <?php echo $badge_class; ?>">
                                <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($can_cancel): ?>
                                <a href="cancel_registration.php?registration_id=<?php echo (int) $reg['Registration_ID']; ?>"
                                   class="btn btn-sm btn-outline-danger confirm-action"
                                   data-confirm-message="Are you sure you want to cancel this registration?"
                                   title="Cancel Registration">
                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div><!-- /.fade-in -->

<?php
/* ── Footer ───────────────────────────────────────────────── */
require_once '../includes/footer.php';
?>
