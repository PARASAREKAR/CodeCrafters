<?php
/**
 * ============================================================
 *  PARTICIPANT DASHBOARD
 * ============================================================
 *  Displays key statistics, dynamic registration reports, and
 *  featured upcoming events for the logged-in participant.
 *  All queries use PDO; output is escaped with htmlspecialchars().
 * ============================================================
 */

/* ── Bootstrap: auth, DB, helpers ─────────────────────────── */
require_once '../includes/auth_check.php';
requireRole(['Participant']);          // Only participants may access
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

/* ── Current user ID from session ─────────────────────────── */
$user_id = $_SESSION['user_id'];

/* ── STAT 1 – My Registrations ── */
$stmt_my_regs = $pdo->prepare(
    'SELECT COUNT(*) AS total FROM registrations WHERE User_ID = ?'
);
$stmt_my_regs->execute([$user_id]);
$my_registrations = (int) $stmt_my_regs->fetch(PDO::FETCH_ASSOC)['total'];

/* ── STAT 2 – Upcoming Events (confirmed registrations in future) ── */
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

/* ── STAT 3 – Available Events (events with remaining capacity) ── */
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

/* ── Chart 1: My Registration Status Breakdown ─────────────────── */
$stmtRegStatus = $pdo->prepare(
    "SELECT Status, COUNT(*) AS count
     FROM registrations
     WHERE User_ID = ?
     GROUP BY Status"
);
$stmtRegStatus->execute([$user_id]);
$regStatusRaw = $stmtRegStatus->fetchAll(PDO::FETCH_ASSOC);
$regStatusLabels = [];
$regStatusData = [];
foreach ($regStatusRaw as $row) {
    $regStatusLabels[] = $row['Status'];
    $regStatusData[] = (int) $row['count'];
}

/* ── Chart 2: My Bookings by Category ──────────────────────────── */
$stmtCatDist = $pdo->prepare(
    "SELECT e.Event_Category, COUNT(*) AS count
     FROM registrations r
     JOIN events e ON r.Event_ID = e.Event_ID
     WHERE r.User_ID = ? AND r.Status != 'Cancelled'
     GROUP BY e.Event_Category"
);
$stmtCatDist->execute([$user_id]);
$catDistRaw = $stmtCatDist->fetchAll(PDO::FETCH_ASSOC);
$catDistLabels = [];
$catDistData = [];
foreach ($catDistRaw as $row) {
    $catDistLabels[] = $row['Event_Category'];
    $catDistData[] = (int) $row['count'];
}

/* ── FEATURED / UPCOMING EVENTS – next 6 events ── */
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

<!-- Custom CSS for Participant Dashboard Refinement -->
<style>
    .part-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        box-shadow: var(--shadow);
    }
    .part-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
        border-color: var(--accent);
    }
    .part-stat-icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: var(--accent-light);
        color: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
    }
    .part-stat-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.2;
        color: var(--text-primary);
        font-family: monospace;
    }
    .part-stat-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-secondary);
    }
    .chart-container {
        position: relative;
        min-height: 280px;
        width: 100%;
    }
    .event-card-custom {
        transition: transform 0.3s ease, border-color 0.3s ease;
    }
    .event-card-custom:hover {
        transform: translateY(-4px);
        border-color: var(--accent);
    }
</style>

<div class="fade-in">
    <!-- Page header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4" data-aos="fade-down">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-speedometer2 me-2"></i>My Dashboard</h2>
            <p class="text-muted mb-0">Browse schedules, track registrations, and discover new learning opportunities.</p>
        </div>
        <span class="badge bg-glass border text-muted px-3 py-2 fs-7" style="border-radius: 12px;">
            Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest', ENT_QUOTES, 'UTF-8'); ?></strong>!
        </span>
    </div>

    <!-- Stat Cards -->
    <div class="row g-4 mb-4">
        <!-- My Registrations -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
            <div class="part-stat-card">
                <div class="part-stat-icon-wrapper">
                    <i class="bi bi-journal-check"></i>
                </div>
                <div>
                    <div class="part-stat-value"><?php echo (int) $my_registrations; ?></div>
                    <div class="part-stat-label">Total Bookings</div>
                </div>
            </div>
        </div>

        <!-- Upcoming Confirmed Events -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="part-stat-card">
                <div class="part-stat-icon-wrapper">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div>
                    <div class="part-stat-value"><?php echo (int) $upcoming_events; ?></div>
                    <div class="part-stat-label">Upcoming Confirmed</div>
                </div>
            </div>
        </div>

        <!-- Available seats elsewhere -->
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="part-stat-card">
                <div class="part-stat-icon-wrapper">
                    <i class="bi bi-ticket-perforated"></i>
                </div>
                <div>
                    <div class="part-stat-value"><?php echo (int) $available_events; ?></div>
                    <div class="part-stat-label">Active Events Available</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         CHARTS PANEL
         ============================================================ -->
    <div class="row g-4 mb-5">
        <!-- My Bookings Status Breakdown (Doughnut) -->
        <div class="col-lg-5" data-aos="fade-right" data-aos-delay="100">
            <div class="card glass-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-pie-chart-fill me-2 text-accent"></i>My Booking Status</h5>
                    <small class="text-muted">Proportion of Confirmed, Pending, and Cancelled slots.</small>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center">
                    <div class="chart-container">
                        <?php if (empty($regStatusData)): ?>
                            <div class="h-100 d-flex align-items-center justify-content-center text-muted">No registration stats available.</div>
                        <?php else: ?>
                            <canvas id="partStatusChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Bookings by Category (Bar) -->
        <div class="col-lg-7" data-aos="fade-left" data-aos-delay="100">
            <div class="card glass-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-line-fill me-2 text-accent"></i>My Event Categories</h5>
                    <small class="text-muted">Breakdown of events you registered for by classification.</small>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center">
                    <div class="chart-container">
                        <?php if (empty($catDistData)): ?>
                            <div class="h-100 d-flex align-items-center justify-content-center text-muted">No category breakdown available.</div>
                        <?php else: ?>
                            <canvas id="partCategoryChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         FEATURED / UPCOMING EVENTS
         ============================================================ -->
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
        <h4 class="fw-bold mb-0"><i class="bi bi-stars me-2 text-accent"></i>Featured &amp; Upcoming Events</h4>
        <a href="browse_events.php" class="btn btn-sm btn-outline-accent px-3 py-1.5" style="border-radius: 10px;">
            View All <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <?php if (empty($featured_events)): ?>
        <div class="p-5 text-center text-muted glass-card mb-4" data-aos="fade-up">
            <i class="bi bi-calendar-x fs-1 mb-2"></i>
            <p class="mb-0">No upcoming events at the moment. Check back soon!</p>
        </div>
    <?php else: ?>
        <div class="row g-4 mb-4" data-aos="fade-up">
            <?php foreach ($featured_events as $event):
                $capacity   = (int) $event['Capacity'];
                $filled     = (int) $event['filled'];
                $remaining  = max(0, $capacity - $filled);
                $pct_filled = $capacity > 0 ? round(($filled / $capacity) * 100) : 100;
                $is_full    = ($filled >= $capacity);
                $is_registered = ((int) $event['already_registered'] > 0);
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card glass-card event-card-custom h-100 d-flex flex-column overflow-hidden" style="border-radius: 20px;">
                    
                    <!-- Event Banner Image -->
                    <div class="event-card-img-wrap position-relative" style="height: 150px; width: 100%;">
                        <?php
                        $event_cat = $event['Event_Category'] ?? 'General';
                        $imgPath = $event['Image_Path'] ?? '';
                        if (!empty($imgPath) && file_exists('../' . $imgPath)): ?>
                            <img src="../<?php echo htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8'); ?>" alt="Event Banner" class="event-card-img h-100">
                        <?php elseif (!empty($imgPath) && file_exists($imgPath)): ?>
                            <img src="<?php echo htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8'); ?>" alt="Event Banner" class="event-card-img h-100">
                        <?php else: ?>
                            <img src="<?php echo htmlspecialchars(getCategoryImage($event_cat, '../'), ENT_QUOTES, 'UTF-8'); ?>" alt="Event Banner" class="event-card-img h-100">
                        <?php endif; ?>
                        <?php if (isset($event['Event_Fee']) && $event['Event_Fee'] > 0): ?>
                            <span class="event-fee-badge" style="font-size: 11px; padding: 3px 10px;">₹<?php echo number_format($event['Event_Fee'], 0); ?></span>
                        <?php else: ?>
                            <span class="event-fee-badge bg-success text-white border-0" style="font-size: 11px; padding: 3px 10px;">Free</span>
                        <?php endif; ?>
                    </div>

                    <div class="p-4 d-flex flex-column flex-grow-1">
                        <!-- Category Badge -->
                        <div class="mb-2">
                            <span class="badge bg-glass border text-muted px-2.5 py-1.5" style="border-radius: 8px; font-size: 0.75rem;">
                                🎯 <?php echo htmlspecialchars($event['Event_Category'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>

                        <!-- Event name -->
                        <h5 class="fw-bold mb-2 text-primary" style="line-height: 1.4;">
                            <?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?>
                        </h5>

                    <!-- Date & Venue -->
                    <p class="text-muted small mb-1">
                        <i class="bi bi-calendar3 me-2 text-accent"></i>
                        <?php echo htmlspecialchars(date('d M Y', strtotime($event['Event_Date'])), ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-geo-alt me-2 text-accent"></i>
                        <?php echo htmlspecialchars($event['Venue'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>

                    <!-- Capacity bar -->
                    <div class="mb-3 mt-auto">
                        <div class="d-flex justify-content-between mb-1.5">
                            <span class="small text-muted"><?php echo $filled; ?> / <?php echo $capacity; ?> slots filled</span>
                            <span class="small fw-semibold text-primary"><?php echo $pct_filled; ?>%</span>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 3px; background: rgba(255,255,255,0.05);">
                            <div class="progress-bar <?php echo $is_full ? 'bg-danger' : 'bg-success'; ?>"
                                 role="progressbar"
                                 style="width: <?php echo $pct_filled; ?>%">
                            </div>
                        </div>
                    </div>

                    <!-- Action button / badge -->
                    <?php if ($is_registered): ?>
                        <span class="badge bg-success-light text-success w-100 py-2.5" style="border-radius: 12px; font-size: 0.85rem; font-weight: 600;">
                            <i class="bi bi-check-circle-fill me-2"></i>Already Registered
                        </span>
                    <?php elseif ($is_full): ?>
                        <span class="badge bg-secondary-light text-muted w-100 py-2.5" style="border-radius: 12px; font-size: 0.85rem; font-weight: 600;">
                            <i class="bi bi-x-circle-fill me-2"></i>Event Full
                        </span>
                    <?php else: ?>
                        <a href="submit_registration.php?event_id=<?php echo (int) $event['Event_ID']; ?>"
                           class="btn btn-accent w-100 py-2" style="border-radius: 12px;">
                            <i class="bi bi-pencil-square me-2"></i>Register Now
                        </a>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Include Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // ── Get computed theme styles for Chart config ───────────────
    const style = getComputedStyle(document.documentElement);
    const textColor = style.getPropertyValue('--text-muted').trim() || '#707080';
    const accentColor = style.getPropertyValue('--accent').trim() || '#00e5ff';
    const borderColor = style.getPropertyValue('--border').trim() || 'rgba(255, 255, 255, 0.05)';

    // Global Font Configuration
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = textColor;

    // ── Chart 1: Registration Status Breakdown ───────────────────
    const statusCtx = document.getElementById('partStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($regStatusLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($regStatusData); ?>,
                    backgroundColor: [
                        '#10b981', // Confirmed - green
                        '#f59e0b', // Pending - amber
                        '#ef4444'  // Cancelled - red
                    ],
                    borderWidth: 2,
                    borderColor: style.getPropertyValue('--bg-card').trim() || '#12121a'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            padding: 15,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    // ── Chart 2: Bookings by Category ────────────────────────────
    const catCtx = document.getElementById('partCategoryChart');
    if (catCtx) {
        new Chart(catCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($catDistLabels); ?>,
                datasets: [{
                    label: 'Registrations',
                    data: <?php echo json_encode($catDistData); ?>,
                    backgroundColor: accentColor,
                    borderRadius: 8,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: textColor
                        }
                    },
                    y: {
                        grid: {
                            color: borderColor
                        },
                        ticks: {
                            color: textColor,
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
