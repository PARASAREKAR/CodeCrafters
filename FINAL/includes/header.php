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

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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

    <!-- ============================================================ -->
    <!-- Navbar -->
    <!-- ============================================================ -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <!-- Brand -->
            <a class="navbar-brand fw-bold" href="../index.php">
                <span style="color: var(--accent);">🎯</span> EventHub
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
                                <a class="nav-link" href="../admin/manage_requests.php">Requests</a>
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
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <span class="dropdown-item-text text-muted small">
                                        Role: <?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="../auth/logout.php">Logout</a>
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
