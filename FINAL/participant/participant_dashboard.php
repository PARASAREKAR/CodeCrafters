<?php
/**
 * ============================================================
 *  PARTICIPANT DASHBOARD
 * ============================================================
 *  Displays key statistics and featured upcoming events for the
 *  logged-in participant. All database queries use PDO prepared
 *  statements; every output value is escaped with htmlspecialchars().
 * ============================================================
 */

/* ── Bootstrap: auth, DB, helpers ─────────────────────────── */
require_once '../includes/auth_check.php';
requireRole(['Participant']);          // Only participants may access
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

/* ── Current user ID from session ─────────────────────────── */
$user_id = $_SESSION['user_id'];

/* ──────────────────────────────────────────────────────────
   STAT 1 – My Registrations
   Total number of registrations this user has ever made.
   ────────────────────────────────────────────────────────── */
$stmt_my_regs = $pdo->prepare(
    'SELECT COUNT(*) AS total FROM registrations WHERE User_ID = ?'
);
$stmt_my_regs->execute([$user_id]);
$my_registrations = (int) $stmt_my_regs->fetch(PDO::FETCH_ASSOC)['total'];

/* ──────────────────────────────────────────────────────────
   STAT 2 – Upcoming Events (confirmed registrations for
   events whose date is in the future)
   ────────────────────────────────────────────────────────── */
$stmt_upcoming = $pdo->prepare(
    "SELECT COUNT(*) AS total
       FROM registrations r
       JOIN events e ON r.Event_ID = e.Event_ID
      WHERE r.User_ID = ?
        AND r.Status   = 'Confirmed'
        AND e.Event_Date >= CURDATE()"
);
$stmt_upcoming->execute([$user_id]);
$upcoming_events = (int) $stmt_upcoming->fetch(PDO::FETCH_ASSOC)['total'];

/* ──────────────────────────────────────────────────────────
   STAT 3 – Available Events (events with remaining capacity)
   Sub-query counts confirmed/pending registrations per event
   and compares against the event's capacity.
   ────────────────────────────────────────────────────────── */
$stmt_available = $pdo->prepare(
    "SELECT COUNT(*) AS total
       FROM events e
      WHERE e.Event_Date >= CURDATE()
        AND (
              SELECT COUNT(*)
                FROM registrations r
               WHERE r.Event_ID = e.Event_ID
                 AND r.Status IN ('Confirmed', 'Pending')
            ) < e.Capacity"
);
$stmt_available->execute();
$available_events = (int) $stmt_available->fetch(PDO::FETCH_ASSOC)['total'];

/* ──────────────────────────────────────────────────────────
   FEATURED / UPCOMING EVENTS – next 6 events with capacity
   information plus whether the current user already registered.
   ────────────────────────────────────────────────────────── */
$stmt_featured = $pdo->prepare(
    "SELECT e.*,
            (SELECT COUNT(*)
               FROM registrations r
              WHERE r.Event_ID = e.Event_ID
                AND r.Status IN ('Confirmed', 'Pending')
            ) AS filled,
            (SELECT COUNT(*)
               FROM registrations r2
              WHERE r2.Event_ID = e.Event_ID
                AND r2.User_ID  = ?
                AND r2.Status  != 'Cancelled'
            ) AS already_registered
       FROM events e
      WHERE e.Event_Date >= CURDATE()
      ORDER BY e.Event_Date ASC
      LIMIT 6"
);
$stmt_featured->execute([$user_id]);
$featured_events = $stmt_featured->fetchAll(PDO::FETCH_ASSOC);

/* ── Render page ──────────────────────────────────────────── */
$page_title = 'Participant Dashboard';
require_once '../includes/header.php';
?>

<!-- ============================================================
     DASHBOARD CONTENT
     ============================================================ -->
<div class="fade-in">

    <!-- Page header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </h2>
        <span class="text-muted">
            Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>!
        </span>
    </div>

    <!-- ── Stat Cards ─────────────────────────────────────── -->
    <div class="row g-4 mb-5">
        <!-- My Registrations -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-value"><?php echo (int) $my_registrations; ?></div>
                <div class="stat-label">My Registrations</div>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon">⏰</div>
                <div class="stat-value"><?php echo (int) $upcoming_events; ?></div>
                <div class="stat-label">Upcoming Events</div>
            </div>
        </div>

        <!-- Available Events -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card">
                <div class="stat-icon">🎟️</div>
                <div class="stat-value"><?php echo (int) $available_events; ?></div>
                <div class="stat-label">Available Events</div>
            </div>
        </div>
    </div>

    <!-- ── Featured / Upcoming Events ─────────────────────── -->
    <div class="page-header mb-3">
        <h4><i class="bi bi-stars me-2"></i>Featured &amp; Upcoming Events</h4>
    </div>

    <?php if (empty($featured_events)): ?>
        <!-- No upcoming events message -->
        <div class="empty-state text-center py-5">
            <i class="bi bi-calendar-x fs-1 text-muted"></i>
            <p class="mt-3 text-muted">No upcoming events at the moment. Check back soon!</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($featured_events as $event):
                /* Calculate capacity percentage for progress bar */
                $capacity   = (int) $event['Capacity'];
                $filled     = (int) $event['filled'];
                $remaining  = max(0, $capacity - $filled);
                $pct_filled = $capacity > 0 ? round(($filled / $capacity) * 100) : 100;
                $is_full    = ($filled >= $capacity);
                $is_registered = ((int) $event['already_registered'] > 0);
            ?>
            <div class="col-md-4">
                <div class="card-custom glass-card h-100 p-4 d-flex flex-column">

                    <!-- Event name -->
                    <h5 class="fw-bold mb-2">
                        <?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?>
                    </h5>

                    <!-- Date & Venue -->
                    <p class="text-muted mb-1">
                        <i class="bi bi-calendar3 me-1"></i>
                        <?php echo htmlspecialchars($event['Event_Date'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <p class="text-muted mb-3">
                        <i class="bi bi-geo-alt me-1"></i>
                        <?php echo htmlspecialchars($event['Venue'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>

                    <!-- Capacity bar -->
                    <div class="mb-3 mt-auto">
                        <small class="text-muted d-block mb-1">
                            <?php echo $filled; ?> / <?php echo $capacity; ?> slots filled
                        </small>
                        <div class="capacity-bar">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar <?php echo $is_full ? 'bg-danger' : 'bg-success'; ?>"
                                     role="progressbar"
                                     style="width: <?php echo $pct_filled; ?>%"
                                     aria-valuenow="<?php echo $pct_filled; ?>"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action button / badge -->
                    <?php if ($is_registered): ?>
                        <span class="badge badge-confirmed w-100 py-2">
                            <i class="bi bi-check-circle me-1"></i>Already Registered
                        </span>
                    <?php elseif ($is_full): ?>
                        <span class="badge bg-secondary w-100 py-2">
                            <i class="bi bi-x-circle me-1"></i>Event Full
                        </span>
                    <?php else: ?>
                        <a href="submit_registration.php?event_id=<?php echo (int) $event['Event_ID']; ?>"
                           class="btn btn-accent w-100">
                            <i class="bi bi-pencil-square me-1"></i>Register Now
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div><!-- /.fade-in -->

<?php
/* ── Footer ───────────────────────────────────────────────── */
require_once '../includes/footer.php';
?>
