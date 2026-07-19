<?php
/**
 * Admin Dashboard
 * ---------------
 * Central hub for administrators showing:
 * - System-wide statistics (users, events, registrations)
 * - Quick links to management pages
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

// ── Stat 4: Active (Confirmed) Registrations ───────────────────
$stmtActive = $pdo->query("SELECT COUNT(*) FROM registrations WHERE Status = 'Confirmed'");
$activeRegistrations = $stmtActive->fetchColumn();

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

<!-- ============================================================
     STAT CARDS
     ============================================================ -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
        📊 Admin Dashboard
    </h2>
</div>

<div class="row g-4 mb-4">
    <!-- Total Users -->
    <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="0">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-value"><?php echo (int) $totalUsers; ?></div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>

    <!-- Total Events -->
    <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="100">
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-value"><?php echo (int) $totalEvents; ?></div>
            <div class="stat-label">Total Events</div>
        </div>
    </div>

    <!-- Total Registrations -->
    <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="200">
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-value"><?php echo (int) $totalRegistrations; ?></div>
            <div class="stat-label">Total Registrations</div>
        </div>
    </div>

    <!-- Active Registrations -->
    <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="300">
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-value"><?php echo (int) $activeRegistrations; ?></div>
            <div class="stat-label">Active Registrations</div>
        </div>
    </div>
</div>



<!-- ============================================================
     RECENT REGISTRATIONS TABLE
     ============================================================ -->
<div class="card glass-card">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h5 class="mb-0">📋 Recent Registrations</h5>
        <span class="badge bg-secondary"><?php echo count($recentRegistrations); ?> latest</span>
    </div>
    <div class="card-body">
        <?php if (empty($recentRegistrations)): ?>
            <div class="alert alert-info text-center">
                No registrations found in the system yet.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User Name</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRegistrations as $index => $reg): ?>
                            <?php
                                // Determine badge class based on status
                                $badgeClass = 'bg-secondary';
                                switch ($reg['Status']) {
                                    case 'Confirmed': $badgeClass = 'bg-success'; break;
                                    case 'Pending':   $badgeClass = 'bg-warning text-dark'; break;
                                    case 'Cancelled': $badgeClass = 'bg-danger'; break;
                                }
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($reg['user_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($reg['event_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($reg['Registration_Date'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="badge <?php echo $badgeClass; ?>">
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

<?php require_once '../includes/footer.php'; ?>
