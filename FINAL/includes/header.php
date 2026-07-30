<?php
/**
 * Shared Header Template
 * ----------------------
 * Included at the top of every page. Provides:
 * - HTML head with Bootstrap 5.3 CSS, Google Fonts, and custom stylesheets
 * - Theme initialization script (prevents flash of wrong theme)
 * - Responsive navbar with role-conditional menu items
 * - Flash message display area
 */

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent browser caching (back-button security after logout)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Include helpers for flash messages and auth checks
require_once __DIR__ . '/helpers.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
        $displayTitle = $pageTitle ?? $page_title ?? null;
        echo $displayTitle ? htmlspecialchars($displayTitle, ENT_QUOTES, 'UTF-8') . ' | EventHub' : 'EventHub - Online Event Registration'; 
    ?></title>

    <!-- Bootstrap 5.3.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom Stylesheets -->
    <link rel="stylesheet" href="../assets/css/themes.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Theme Initialization: Read stored theme BEFORE render to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <!-- Animated Background Orbs (Feature 4) -->
    <div class="bg-orb bg-orb-1" aria-hidden="true"></div>
    <div class="bg-orb bg-orb-2" aria-hidden="true"></div>
    <div class="bg-orb bg-orb-3" aria-hidden="true"></div>

    <!-- ============================================================ -->
    <!-- Navbar -->
    <!-- ============================================================ -->
    <nav class="navbar navbar-expand-lg navbar-custom" data-aos="fade-down" data-aos-duration="600">
        <div class="container">
            <!-- Brand -->
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="../index.php">
                <img src="../assets/images/logo.png" alt="EventHub Logo" class="rounded-circle shadow-sm" style="width: 38px; height: 38px; object-fit: cover; border: 2px solid var(--accent);"> EventHub
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarMain" aria-controls="navbarMain"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Nav Links -->
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto align-items-center">

                    <?php if (isLoggedIn()): ?>
                        <?php $role = getUserRole(); ?>

                        <!-- ====== Admin Menu ====== -->
                        <?php if ($role === 'Admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../admin/admin_dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../admin/manage_users.php">Manage Users</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../admin/manage_requests.php">Manage Requests</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../admin/view_payments.php">Payments</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../admin/view_messages.php">Messages</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../admin/reports.php">Reports</a>
                            </li>

                        <!-- ====== Organizer Menu ====== -->
                        <?php elseif ($role === 'Organizer'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../organizer/organizer_dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../organizer/create_event.php">Create Event</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../organizer/participant_requests.php">Participant Requests</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../organizer/reports.php">Reports</a>
                            </li>

                        <!-- ====== Participant Menu ====== -->
                        <?php elseif ($role === 'Participant'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../participant/participant_dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../participant/browse_events.php">Browse Events</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../participant/my_registrations.php">My Registrations</a>
                            </li>
                        <?php endif; ?>

                        <!-- User info & Logout (all logged-in roles) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <?php if (!empty($_SESSION['user_avatar'])): ?>
                                    <img src="../<?php echo htmlspecialchars($_SESSION['user_avatar'], ENT_QUOTES, 'UTF-8'); ?>" 
                                         alt="Avatar" class="rounded-circle" style="width: 28px; height: 28px; object-fit: cover; border: 1.5px solid var(--accent);">
                                <?php else: ?>
                                    <i class="bi bi-person-circle fs-5" style="color: var(--accent);"></i>
                                <?php endif; ?>
                                <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;">
                                <li>
                                    <span class="dropdown-item-text text-muted small">
                                        Role: <?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </li>
                                <li><hr class="dropdown-divider" style="background-color: var(--border);"></li>
                                <li>
                                    <a class="dropdown-item" href="edit_profile.php" style="color: var(--text-primary);">
                                        <i class="bi bi-person-gear me-2 text-accent"></i>Edit Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider" style="background-color: var(--border);"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="../auth/logout.php">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </li>

                    <?php else: ?>
                        <!-- ====== Guest Menu ====== -->
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-sm btn-outline-light ms-2 px-3" href="../auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>

    <!-- ============================================================ -->
    <!-- Flash Messages -->
    <!-- ============================================================ -->
    <div class="container mt-3">
        <?php echo getFlashMessage(); ?>
    </div>

    <!-- ============================================================ -->
    <!-- Main Content Begins -->
    <!-- ============================================================ -->
    <main class="container py-4">
