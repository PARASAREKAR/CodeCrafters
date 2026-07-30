<?php
/**
 * ============================================================
 *  BROWSE EVENTS
 * ============================================================
 *  Allows participants to search, filter, and browse all
 *  available events. Filters are applied via GET parameters and
 *  every query uses PDO prepared statements.
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
   FILTER INPUT – read and sanitise GET parameters
   ────────────────────────────────────────────────────────── */
$filter_search = trim($_GET['search'] ?? '');
$filter_date   = trim($_GET['date']   ?? '');
$filter_venue  = trim($_GET['venue']  ?? '');

/* ──────────────────────────────────────────────────────────
   VENUE DROPDOWN – populate with distinct venue values
   ────────────────────────────────────────────────────────── */
$stmt_venues = $pdo->prepare(
    'SELECT DISTINCT Venue FROM events ORDER BY Venue ASC'
);
$stmt_venues->execute();
$venue_list = $stmt_venues->fetchAll(PDO::FETCH_COLUMN);

/* ──────────────────────────────────────────────────────────
   BUILD DYNAMIC WHERE CLAUSE
   Parameters are collected in $params array for the
   prepared statement; conditions appended to $where.
   ────────────────────────────────────────────────────────── */
$where  = " WHERE e.Status = 'Approved' ";   // base condition only approved events
$params = [];

/* Text search – matches Event_Name, Description, or Venue */
if ($filter_search !== '') {
    $where   .= ' AND (e.Event_Name LIKE ? OR e.Description LIKE ? OR e.Venue LIKE ?) ';
    $like_val = '%' . $filter_search . '%';
    $params[] = $like_val;
    $params[] = $like_val;
    $params[] = $like_val;
}

/* Date filter – exact match on Event_Date */
if ($filter_date !== '') {
    $where   .= ' AND e.Event_Date = ? ';
    $params[] = $filter_date;
}

/* Venue filter – exact match */
if ($filter_venue !== '') {
    $where   .= ' AND e.Venue = ? ';
    $params[] = $filter_venue;
}

/* ──────────────────────────────────────────────────────────
   MAIN QUERY – fetch events with filled-count and
   already-registered flag for the current user
   ────────────────────────────────────────────────────────── */
$sql = "SELECT e.*,
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
        $where
         ORDER BY e.Event_Date ASC";

/* The user_id is the first positional param (for the subquery) */
array_unshift($params, $user_id);

$stmt_events = $pdo->prepare($sql);
$stmt_events->execute($params);
$events = $stmt_events->fetchAll(PDO::FETCH_ASSOC);

/* ── Render page ──────────────────────────────────────────── */
$page_title = 'Browse Events';
require_once '../includes/header.php';
?>

<!-- ============================================================
     BROWSE EVENTS CONTENT
     ============================================================ -->
<div class="fade-in">

    <!-- Page header -->
    <div class="page-header mb-4">
        <h2><i class="bi bi-search me-2"></i>Browse Events</h2>
    </div>

    <!-- ── Search / Filter Section ────────────────────────── -->
    <div class="search-wrapper card-custom glass-card p-4 mb-4">
        <form method="GET" action="browse_events.php" class="row g-3 align-items-end">

            <!-- Text search -->
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text"
                       id="search"
                       name="search"
                       class="form-control form-control-custom search-input"
                       placeholder="Event name, description, venue…"
                       value="<?php echo htmlspecialchars($filter_search, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <!-- Date filter -->
            <div class="col-md-3">
                <label for="date" class="form-label">Date</label>
                <input type="date"
                       id="date"
                       name="date"
                       class="form-control form-control-custom"
                       value="<?php echo htmlspecialchars($filter_date, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <!-- Venue dropdown -->
            <div class="col-md-3">
                <label for="venue" class="form-label">Venue</label>
                <select id="venue" name="venue" class="form-select form-control-custom">
                    <option value="">All Venues</option>
                    <?php foreach ($venue_list as $venue_option): ?>
                        <option value="<?php echo htmlspecialchars($venue_option, ENT_QUOTES, 'UTF-8'); ?>"
                            <?php echo ($filter_venue === $venue_option) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($venue_option, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-accent flex-fill">
                    <i class="bi bi-funnel me-1"></i>Search / Filter
                </button>
                <a href="browse_events.php" class="btn btn-outline-accent flex-fill">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- ── Events Grid ────────────────────────────────────── -->
    <div class="searchable-table">
        <?php if (empty($events)): ?>
            <div class="empty-state text-center py-5">
                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                <p class="mt-3 text-muted">No events match your search criteria.</p>
                <a href="browse_events.php" class="btn btn-outline-accent">Clear Filters</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($events as $event):
                    /* Capacity calculations */
                    $capacity   = (int) $event['Capacity'];
                    $filled     = (int) $event['filled'];
                    $remaining  = max(0, $capacity - $filled);
                    $pct_filled = $capacity > 0 ? round(($filled / $capacity) * 100) : 100;
                    $is_full    = ($filled >= $capacity);
                    $is_registered = ((int) $event['already_registered'] > 0);

                    /* Description excerpt – limit to ~50 words */
                    $description_full = $event['Description'] ?? '';
                    $words = explode(' ', strip_tags($description_full));
                    $excerpt = implode(' ', array_slice($words, 0, 50));
                    if (count($words) > 50) {
                        $excerpt .= '…';
                    }
                ?>
                <div class="col-md-4">
                    <div class="card-custom glass-card h-100 d-flex flex-column overflow-hidden" style="border-radius: 20px;">
                        
                        <!-- Event Banner Image -->
                        <div class="event-card-img-wrap position-relative">
                            <?php
                            $event_cat = $event['Event_Category'] ?? 'General';
                            $imgPath = $event['Image_Path'] ?? '';
                            if (!empty($imgPath) && file_exists('../' . $imgPath)): ?>
                                <img src="../<?php echo htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8'); ?>" alt="Event Image" class="event-card-img">
                            <?php elseif (!empty($imgPath) && file_exists($imgPath)): ?>
                                <img src="<?php echo htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8'); ?>" alt="Event Image" class="event-card-img">
                            <?php else: ?>
                                <img src="<?php echo htmlspecialchars(getCategoryImage($event_cat, '../'), ENT_QUOTES, 'UTF-8'); ?>" alt="Event Image" class="event-card-img">
                            <?php endif; ?>
                            <?php if (isset($event['Event_Fee']) && $event['Event_Fee'] > 0): ?>
                                <span class="event-fee-badge">₹<?php echo number_format($event['Event_Fee'], 0); ?></span>
                            <?php else: ?>
                                <span class="event-fee-badge bg-success text-white border-0">Free</span>
                            <?php endif; ?>
                        </div>

                        <div class="p-4 d-flex flex-column flex-grow-1">

                            <!-- Event name -->
                            <h5 class="fw-bold mb-2">
                                <?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?>
                            </h5>

                            <!-- Description excerpt -->
                            <p class="text-muted small mb-2">
                                <?php echo htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8'); ?>
                            </p>

                        <!-- Meta: date, time, venue, organizer -->
                        <ul class="list-unstyled small mb-3">
                            <li><i class="bi bi-calendar3 me-1"></i>
                                <?php echo htmlspecialchars($event['Event_Date'], ENT_QUOTES, 'UTF-8'); ?>
                            </li>
                            <li><i class="bi bi-clock me-1"></i>
                                <?php echo htmlspecialchars($event['Event_Time'] ?? 'TBD', ENT_QUOTES, 'UTF-8'); ?>
                            </li>
                            <li><i class="bi bi-geo-alt me-1"></i>
                                <?php echo htmlspecialchars($event['Venue'], ENT_QUOTES, 'UTF-8'); ?>
                            </li>
                            <?php if (!empty($event['Organizer'])): ?>
                            <li><i class="bi bi-person me-1"></i>
                                <?php echo htmlspecialchars($event['Organizer'], ENT_QUOTES, 'UTF-8'); ?>
                            </li>
                            <?php endif; ?>
                        </ul>

                        <!-- Capacity indicator -->
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

                        <!-- Action -->
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
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div><!-- /.searchable-table -->

</div><!-- /.fade-in -->

<?php
/* ── Footer ───────────────────────────────────────────────── */
require_once '../includes/footer.php';
?>
