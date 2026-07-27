<?php
/**
 * Admin Dashboard
 * ---------------
 * Central hub for administrators showing:
 * - System-wide statistics (users, events, registrations)
 * - Interactive charts representing event categories & registration states
 * - Recent registrations table (last 10)
 *
 * @requires auth_check.php  – session bootstrap & role guard
 * @requires db_connect.php  – PDO $pdo connection
 * @requires helpers.php     – flash messages, sanitize, etc.
 */

require_once '../includes/auth_check.php';
requireRole(['Admin']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

// ── Stat 1: Total Users ────────────────────────────────────────
$stmtUsers = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmtUsers->fetchColumn();

// ── Stat 2: Total Events ───────────────────────────────────────
$stmtEvents = $pdo->query("SELECT COUNT(*) FROM events");
$totalEvents = $stmtEvents->fetchColumn();

// ── Stat 3: Total Registrations ────────────────────────────────
$stmtRegs = $pdo->query("SELECT COUNT(*) FROM registrations");
$totalRegistrations = $stmtRegs->fetchColumn();

// ── Stat 4: Completed Payments ───────────────────
$stmtPayments = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'Paid'");
$completedPayments = $stmtPayments->fetchColumn();

// ── Chart 1: Registration Status Breakdown ───────────────────
$stmtRegStatus = $pdo->query("SELECT Status, COUNT(*) AS count FROM registrations GROUP BY Status");
$regStatusRaw = $stmtRegStatus->fetchAll(PDO::FETCH_ASSOC);
$regStatusLabels = [];
$regStatusData = [];
foreach ($regStatusRaw as $row) {
    $regStatusLabels[] = $row['Status'];
    $regStatusData[] = (int) $row['count'];
}

// ── Chart 2: Events per Category ──────────────────────────────
$stmtCatDist = $pdo->query("SELECT Event_Category, COUNT(*) AS count FROM events GROUP BY Event_Category");
$catDistRaw = $stmtCatDist->fetchAll(PDO::FETCH_ASSOC);
$catDistLabels = [];
$catDistData = [];
foreach ($catDistRaw as $row) {
    $catDistLabels[] = $row['Event_Category'];
    $catDistData[] = (int) $row['count'];
}

// ── Recent 10 Registrations with User & Event names ────────────
$stmtRecent = $pdo->query(
    "SELECT r.Registration_ID,
            u.Name       AS user_name,
            e.Event_Name AS event_name,
            r.Registration_Date,
            r.Status
     FROM registrations r
     JOIN users  u ON r.User_ID  = u.User_ID
     JOIN events e ON r.Event_ID = e.Event_ID
     ORDER BY r.created_at DESC
     LIMIT 10"
);
$recentRegistrations = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

// ── Page title for header include ──────────────────────────────
$pageTitle = "Admin Dashboard";
require_once '../includes/header.php';
?>

<!-- Custom CSS for Admin Dashboard Refinement -->
<style>
    .admin-stat-card {
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
    .admin-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
        border-color: var(--accent);
    }
    .admin-stat-icon-wrapper {
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
    .admin-stat-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.2;
        color: var(--text-primary);
        font-family: monospace;
    }
    .admin-stat-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-secondary);
    }
    .chart-container {
        position: relative;
        min-height: 300px;
        width: 100%;
    }
    .table-custom-row:hover {
        background: rgba(var(--accent-rgb), 0.02) !important;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <div>
        <h2 class="fw-bold mb-1">📊 Admin Console</h2>
        <p class="text-muted mb-0">System metrics, event distributions, and recent registrations overview.</p>
    </div>
</div>

<!-- ============================================================
     STAT CARDS
     ============================================================ -->
<div class="row g-4 mb-4">
    <!-- Total Users -->
    <div class="col-xl-3 col-sm-6" data-aos="fade-up" data-aos-delay="0">
        <a href="manage_users.php" class="text-decoration-none" title="Manage Users">
        <div class="admin-stat-card" style="cursor:pointer;">
            <div class="admin-stat-icon-wrapper">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="admin-stat-value"><?php echo (int) $totalUsers; ?></div>
                <div class="admin-stat-label">Registered Users <i class="bi bi-arrow-right-circle ms-1 small"></i></div>
            </div>
        </div>
        </a>
    </div>

    <!-- Total Events -->
    <div class="col-xl-3 col-sm-6" data-aos="fade-up" data-aos-delay="100">
        <a href="manage_requests.php" class="text-decoration-none" title="Manage Events">
        <div class="admin-stat-card" style="cursor:pointer;">
            <div class="admin-stat-icon-wrapper">
                <i class="bi bi-calendar-event-fill"></i>
            </div>
            <div>
                <div class="admin-stat-value"><?php echo (int) $totalEvents; ?></div>
                <div class="admin-stat-label">Total Events <i class="bi bi-arrow-right-circle ms-1 small"></i></div>
            </div>
        </div>
        </a>
    </div>

    <!-- Total Registrations -->
    <div class="col-xl-3 col-sm-6" data-aos="fade-up" data-aos-delay="200">
        <a href="reports.php" class="text-decoration-none" title="View Reports">
        <div class="admin-stat-card" style="cursor:pointer;">
            <div class="admin-stat-icon-wrapper">
                <i class="bi bi-ticket-detailed-fill"></i>
            </div>
            <div>
                <div class="admin-stat-value"><?php echo (int) $totalRegistrations; ?></div>
                <div class="admin-stat-label">Registrations <i class="bi bi-arrow-right-circle ms-1 small"></i></div>
            </div>
        </div>
        </a>
    </div>

    <!-- Active Registrations / Payments -->
    <div class="col-xl-3 col-sm-6" data-aos="fade-up" data-aos-delay="300">
        <a href="view_payments.php" class="text-decoration-none" title="View Payments">
        <div class="admin-stat-card" style="cursor:pointer;">
            <div class="admin-stat-icon-wrapper">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <div class="admin-stat-value"><?php echo (int) $completedPayments; ?></div>
                <div class="admin-stat-label">Total Payments <i class="bi bi-arrow-right-circle ms-1 small"></i></div>
            </div>
        </div>
        </a>
    </div>
</div>

<!-- ============================================================
     CHARTS SECTION
     ============================================================ -->
<div class="row g-4 mb-4">
    <!-- Registration Breakdown Doughnut -->
    <div class="col-lg-5" data-aos="fade-right" data-aos-delay="100">
        <div class="card glass-card h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-pie-chart-fill me-2 text-accent"></i>Registration Status</h5>
                <small class="text-muted">Proportion of Confirmed, Pending, and Cancelled slots.</small>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center">
                <div class="chart-container">
                    <?php if (empty($regStatusData)): ?>
                        <div class="h-100 d-flex align-items-center justify-content-center text-muted">No registration data available.</div>
                    <?php else: ?>
                        <canvas id="regStatusChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Categories Distribution Bar -->
    <div class="col-lg-7" data-aos="fade-left" data-aos-delay="100">
        <div class="card glass-card h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-line-fill me-2 text-accent"></i>Events by Category</h5>
                <small class="text-muted">Volume of events classified across the 9 primary categories.</small>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center">
                <div class="chart-container">
                    <?php if (empty($catDistData)): ?>
                        <div class="h-100 d-flex align-items-center justify-content-center text-muted">No event category data available.</div>
                    <?php else: ?>
                        <canvas id="catDistChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     RECENT REGISTRATIONS TABLE
     ============================================================ -->
<div class="card glass-card mb-4" data-aos="fade-up" data-aos-delay="200">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3 border-0">
        <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-accent"></i>Recent Registrations</h5>
        <span class="badge bg-accent-light text-accent px-3 py-2" style="border-radius: 12px; font-weight: 600;">
            <?php echo count($recentRegistrations); ?> latest
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentRegistrations)): ?>
            <div class="p-5 text-center text-muted">
                <i class="bi bi-ticket-perforated fs-1 mb-2"></i>
                <p class="mb-0">No registrations recorded in the system yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>User Name</th>
                            <th>Event Name</th>
                            <th>Registration Date</th>
                            <th class="pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRegistrations as $index => $reg): ?>
                            <?php
                                // Determine status badge styling
                                $badgeClass = 'bg-secondary';
                                switch ($reg['Status']) {
                                    case 'Confirmed': $badgeClass = 'bg-success'; break;
                                    case 'Pending':   $badgeClass = 'bg-warning text-dark'; break;
                                    case 'Cancelled': $badgeClass = 'bg-danger'; break;
                                }
                            ?>
                            <tr class="table-custom-row">
                                <td class="ps-4 font-monospace text-muted"><?php echo $index + 1; ?></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($reg['user_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($reg['event_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-muted"><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($reg['Registration_Date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="pe-4">
                                    <span class="badge <?php echo $badgeClass; ?> px-2.5 py-1.5" style="border-radius: 8px;">
                                        <?php echo htmlspecialchars($reg['Status'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
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
    Chart.defaults.font.size = 12;
    Chart.defaults.color = textColor;

    // ── Chart 1: Registration Status Breakdown ───────────────────
    const regCtx = document.getElementById('regStatusChart');
    if (regCtx) {
        new Chart(regCtx, {
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

    // ── Chart 2: Events per Category ──────────────────────────────
    const catCtx = document.getElementById('catDistChart');
    if (catCtx) {
        new Chart(catCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($catDistLabels); ?>,
                datasets: [{
                    label: 'Event Volume',
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
