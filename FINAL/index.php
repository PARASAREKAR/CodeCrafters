<?php
/**
 * Application Entry Point — Public Landing Page
 * -----------------------------------------------
 * If logged in  → Redirect to role-specific dashboard
 * If not logged → Show public landing page with all live events
 *
 * Features:
 * - Category tabs (Technology, Business, Education, etc.)
 * - Text search, date filter, venue filter
 * - Responsive event card grid
 * - Login / Register buttons in top-right navbar
 */

session_start();
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/config/db_connect.php';

// ── If user is logged in, send them to their dashboard ──────
if (isLoggedIn()) {
    $role = getUserRole();

    switch ($role) {
        case 'Admin':
            redirectTo('admin/admin_dashboard.php');
            break;
        case 'Organizer':
            redirectTo('organizer/organizer_dashboard.php');
            break;
        case 'Participant':
            redirectTo('participant/participant_dashboard.php');
            break;
        default:
            session_destroy();
            redirectTo('auth/login.php');
            break;
    }
}

// ══════════════════════════════════════════════════════════════
//  PUBLIC LANDING PAGE — Fetch and display all upcoming events
// ══════════════════════════════════════════════════════════════

// ── Category Definitions ────────────────────────────────────
$categories = [
    'All',
    'Technology',
    'Business',
    'Education',
    'Health & Wellness',
    'Arts & Culture',
    'Sports',
    'Networking',
    'Workshop',
    'General'
];

// ── Read Filter Parameters ──────────────────────────────────
$filter_search   = trim($_GET['search']   ?? '');
$filter_date     = trim($_GET['date']     ?? '');
$filter_venue    = trim($_GET['venue']    ?? '');
$filter_category = trim($_GET['category'] ?? 'All');

// ── Venue Dropdown – distinct venues ────────────────────────
$stmt_venues = $pdo->prepare(
    "SELECT DISTINCT Venue FROM events WHERE Event_Date >= CURDATE() AND Status = 'Approved' ORDER BY Venue ASC"
);
$stmt_venues->execute();
$venue_list = $stmt_venues->fetchAll(PDO::FETCH_COLUMN);

// ── Build Dynamic WHERE Clause ──────────────────────────────
$where  = " WHERE e.Event_Date >= CURDATE() AND e.Status = 'Approved' ";
$params = [];

// Category filter
if ($filter_category !== '' && $filter_category !== 'All') {
    $where   .= ' AND e.Event_Category = ? ';
    $params[] = $filter_category;
}

// Text search – matches Event_Name, Description, or Venue
if ($filter_search !== '') {
    $where   .= ' AND (e.Event_Name LIKE ? OR e.Description LIKE ? OR e.Venue LIKE ?) ';
    $like_val = '%' . $filter_search . '%';
    $params[] = $like_val;
    $params[] = $like_val;
    $params[] = $like_val;
}

// Date filter
if ($filter_date !== '') {
    $where   .= ' AND e.Event_Date = ? ';
    $params[] = $filter_date;
}

// Venue filter
if ($filter_venue !== '') {
    $where   .= ' AND e.Venue = ? ';
    $params[] = $filter_venue;
}

// ── Main Query – fetch events with filled count ─────────────
$sql = "SELECT e.*,
               (SELECT COUNT(*)
                  FROM registrations r
                 WHERE r.Event_ID = e.Event_ID
                   AND r.Status IN ('Confirmed', 'Pending')
               ) AS filled
          FROM events e
        $where
         ORDER BY e.Event_Date ASC";

$stmt_events = $pdo->prepare($sql);
$stmt_events->execute($params);
$events = $stmt_events->fetchAll(PDO::FETCH_ASSOC);

// ── Count events per category (for badge counts) ────────────
$stmt_counts = $pdo->prepare(
    "SELECT Event_Category, COUNT(*) as cnt
       FROM events
      WHERE Event_Date >= CURDATE() AND Status = 'Approved'
      GROUP BY Event_Category"
);
$stmt_counts->execute();
$category_counts_raw = $stmt_counts->fetchAll(PDO::FETCH_ASSOC);
$category_counts = [];
$total_upcoming = 0;
foreach ($category_counts_raw as $row) {
    $category_counts[$row['Event_Category']] = (int) $row['cnt'];
    $total_upcoming += (int) $row['cnt'];
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub - Discover & Register for Amazing Events</title>
    <meta name="description" content="Browse and register for upcoming events — technology, business, education, sports and more. EventHub is your one-stop platform for event discovery.">

    <!-- Bootstrap 5.3.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom Stylesheets -->
    <link rel="stylesheet" href="assets/css/themes.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>
    <!-- Theme Initialization -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'midnight-dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <!-- ============================================================ -->
    <!-- Navbar — Login / Register on the right                       -->
    <!-- ============================================================ -->
    <nav class="navbar navbar-expand-lg navbar-custom" data-aos="fade-down" data-aos-delay="100">
        <div class="container">
            <!-- Brand -->
            <a class="navbar-brand fw-bold" href="index.php">
                <span style="color: var(--accent);">🎯</span> EventHub
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarLanding" aria-controls="navbarLanding"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Nav Links -->
            <div class="collapse navbar-collapse" id="navbarLanding">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#events-section">
                            <i class="bi bi-calendar-event me-1"></i>Browse Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">
                            <i class="bi bi-info-circle me-1"></i>About Us
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="bi bi-envelope me-1"></i>Contact Us
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="nav-link" href="auth/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item ms-lg-1">
                        <a class="nav-link btn btn-accent btn-sm px-3 text-white" href="auth/register.php">
                            <i class="bi bi-person-plus me-1"></i>Register
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ============================================================ -->
    <!-- Hero Section                                                  -->
    <!-- ============================================================ -->
    <section class="landing-hero">
        <div class="hero-bg-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
        <div class="container text-center" data-aos="zoom-in" data-aos-duration="1000">
            <h1 class="hero-title">
                Experience <span class="gradient-text">World-Class Events</span>
            </h1>
            <p class="hero-subtitle">
                Unlock your potential. Discover exclusive tech, business, and creative events hosted by industry leaders—all in one seamless platform.
            </p>

            <!-- Quick Search in Hero -->
            <div class="hero-search-wrapper">
                <form method="GET" action="index.php#events-section" class="hero-search-form">
                    <div class="hero-search-box">
                        <i class="bi bi-search hero-search-icon"></i>
                        <input type="text" name="search" class="hero-search-input"
                               placeholder="Search events by name, venue, or keyword…"
                               value="<?php echo htmlspecialchars($filter_search, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-accent hero-search-btn">
                            <i class="bi bi-search me-1"></i>Search
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Stats -->
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-value"><?php echo $total_upcoming; ?></span>
                    <span class="hero-stat-label">Upcoming Events</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <span class="hero-stat-value"><?php echo count($venue_list); ?></span>
                    <span class="hero-stat-label">Venues</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <span class="hero-stat-value"><?php echo count($categories) - 1; ?></span>
                    <span class="hero-stat-label">Categories</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Events Section                                                -->
    <!-- ============================================================ -->
    <section id="events-section" class="py-5">
        <div class="container">

            <!-- Category Tabs -->
            <div class="category-tabs-wrapper mb-4">
                <div class="category-tabs" id="categoryTabs">
                    <?php foreach ($categories as $cat): ?>
                        <?php
                            $is_active = ($filter_category === $cat);
                            $count = ($cat === 'All') ? $total_upcoming : ($category_counts[$cat] ?? 0);
                            // Build URL preserving other filters
                            $tab_params = $_GET;
                            $tab_params['category'] = $cat;
                            $tab_url = 'index.php?' . http_build_query($tab_params) . '#events-section';
                        ?>
                        <a href="<?php echo htmlspecialchars($tab_url, ENT_QUOTES, 'UTF-8'); ?>"
                           class="category-tab <?php echo $is_active ? 'active' : ''; ?>">
                            <span class="category-tab-icon"><?php echo getCategoryIcon($cat); ?></span>
                            <span class="category-tab-name"><?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="category-tab-count"><?php echo $count; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Advanced Filters -->
            <div class="filters-bar card-custom glass-card p-3 mb-4">
                <form method="GET" action="index.php#events-section" class="row g-3 align-items-end">
                    <!-- Preserve category selection -->
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($filter_category, ENT_QUOTES, 'UTF-8'); ?>">

                    <!-- Text Search -->
                    <div class="col-md-4">
                        <label for="search" class="form-label small fw-semibold">
                            <i class="bi bi-search me-1"></i>Search
                        </label>
                        <input type="text" id="search" name="search"
                               class="form-control form-control-custom"
                               placeholder="Event name, description, venue…"
                               value="<?php echo htmlspecialchars($filter_search, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <!-- Date Filter -->
                    <div class="col-md-3">
                        <label for="date" class="form-label small fw-semibold">
                            <i class="bi bi-calendar3 me-1"></i>Date
                        </label>
                        <input type="date" id="date" name="date"
                               class="form-control form-control-custom"
                               value="<?php echo htmlspecialchars($filter_date, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <!-- Venue Dropdown -->
                    <div class="col-md-3">
                        <label for="venue" class="form-label small fw-semibold">
                            <i class="bi bi-geo-alt me-1"></i>Venue
                        </label>
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
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-accent flex-fill btn-sm">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <a href="index.php#events-section" class="btn btn-outline-accent flex-fill btn-sm">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Results Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">
                    <?php if ($filter_category !== 'All'): ?>
                        <?php echo getCategoryIcon($filter_category); ?>
                        <?php echo htmlspecialchars($filter_category, ENT_QUOTES, 'UTF-8'); ?> Events
                    <?php else: ?>
                        🎯 All Upcoming Events
                    <?php endif; ?>
                </h4>
                <span class="badge bg-secondary rounded-pill px-3 py-2">
                    <?php echo count($events); ?> event<?php echo count($events) !== 1 ? 's' : ''; ?> found
                </span>
            </div>

            <!-- Event Cards Grid -->
            <?php if (empty($events)): ?>
                <div class="empty-state card-custom glass-card text-center py-5">
                    <span class="empty-icon">📭</span>
                    <h5 class="empty-title">No Events Found</h5>
                    <p class="empty-text">
                        No upcoming events match your current filters. Try adjusting your search or selecting a different category.
                    </p>
                    <a href="index.php" class="btn btn-outline-accent mt-3">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Clear All Filters
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($events as $index => $event):
                        // Capacity calculations
                        $capacity   = (int) $event['Capacity'];
                        $filled     = (int) $event['filled'];
                        $remaining  = max(0, $capacity - $filled);
                        $pct_filled = $capacity > 0 ? round(($filled / $capacity) * 100) : 100;
                        $is_full    = ($filled >= $capacity);

                        // Description excerpt
                        $description_full = $event['Description'] ?? '';
                        $words = explode(' ', strip_tags($description_full));
                        $excerpt = implode(' ', array_slice($words, 0, 30));
                        if (count($words) > 30) $excerpt .= '…';

                        // Category info
                        $event_cat = $event['Event_Category'] ?? 'General';

                        // Capacity bar color
                        $cap_class = 'capacity-low';
                        if ($pct_filled >= 80) $cap_class = 'capacity-high';
                        elseif ($pct_filled >= 50) $cap_class = 'capacity-medium';
                    ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($index % 3) * 100; ?>">
                        <div class="card-custom glass-card landing-event-card h-100 d-flex flex-column">

                            <!-- Event Image Placeholder -->
                            <div class="event-card-image-wrapper">
                                <?php $placeholder_id = ($index % 3) + 1; ?>
                                <img src="assets/images/placeholder-<?php echo $placeholder_id; ?>.png" alt="Event Image" class="event-card-image">
                            </div>

                            <div class="event-card-body">
                                <!-- Category Badge -->
                                <div class="event-card-header">
                                    <span class="event-category-badge">
                                        <?php echo getCategoryIcon($event_cat); ?>
                                        <?php echo htmlspecialchars($event_cat, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <?php if ($is_full): ?>
                                        <span class="event-full-badge">FULL</span>
                                    <?php endif; ?>
                                </div>

                            <!-- Event Name -->
                            <h5 class="event-card-title">
                                <?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?>
                            </h5>

                            <!-- Description Excerpt -->
                            <?php if (!empty($excerpt)): ?>
                                <p class="event-card-desc">
                                    <?php echo htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            <?php endif; ?>

                            <!-- Meta Info -->
                            <ul class="event-card-meta">
                                <li>
                                    <i class="bi bi-calendar3"></i>
                                    <?php echo date('M d, Y', strtotime($event['Event_Date'])); ?>
                                </li>
                                <li>
                                    <i class="bi bi-clock"></i>
                                    <?php echo $event['Event_Time'] ? date('g:i A', strtotime($event['Event_Time'])) : 'TBD'; ?>
                                </li>
                                <li>
                                    <i class="bi bi-geo-alt"></i>
                                    <?php echo htmlspecialchars($event['Venue'], ENT_QUOTES, 'UTF-8'); ?>
                                </li>
                                <?php if (!empty($event['Organizer'])): ?>
                                <li>
                                    <i class="bi bi-person"></i>
                                    <?php echo htmlspecialchars($event['Organizer'], ENT_QUOTES, 'UTF-8'); ?>
                                </li>
                                <?php endif; ?>
                            </ul>

                            <!-- Capacity Bar -->
                            <div class="event-card-capacity mt-auto">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted"><?php echo $filled; ?> / <?php echo $capacity; ?> registered</small>
                                    <small class="text-muted"><?php echo $remaining; ?> left</small>
                                </div>
                                <div class="capacity-bar">
                                    <div class="capacity-fill <?php echo $cap_class; ?>"
                                         style="width: <?php echo $pct_filled; ?>%"></div>
                                </div>
                            </div>

                            <!-- CTA -->
                            <div class="event-card-cta mt-3">
                                <?php if ($is_full): ?>
                                    <span class="btn btn-outline-secondary w-100 disabled">
                                        <i class="bi bi-x-circle me-1"></i>Event Full
                                    </span>
                                <?php else: ?>
                                    <a href="auth/login.php" class="btn btn-accent w-100">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>Login to Register
                                    </a>
                                <?php endif; ?>
                                </div>
                            </div> <!-- End event-card-body -->
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Company Showcase / Why Choose EventHub                        -->
    <!-- ============================================================ -->
    <section class="py-5 bg-glass">
        <div class="container py-5" data-aos="fade-up">
            <div class="row align-items-center mb-5">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <span class="badge bg-accent-light text-accent mb-2 px-3 py-2" style="border-radius: 20px;">INNOVATIVE PLATFORM</span>
                    <h2 class="fw-bold mb-4" style="font-size: 2.5rem; line-height: 1.2;">
                        The ultimate destination for <span style="color: var(--accent);">event management</span>.
                    </h2>
                    <p class="text-muted" style="font-size: 1.1rem; line-height: 1.8;">
                        EventHub is trusted by top creators and professionals globally to deliver memorable and highly organized gathering experiences. From advanced participant tracking to verified host systems, we build platforms that foster genuine, secure connections.
                    </p>
                    <div class="d-flex align-items-center gap-3 mt-4">
                        <div class="stat-bubble text-center p-3" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 16px; min-width: 110px;">
                            <h4 class="fw-bold mb-0 text-accent font-monospace">10k+</h4>
                            <small class="text-muted">Users</small>
                        </div>
                        <div class="stat-bubble text-center p-3" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 16px; min-width: 110px;">
                            <h4 class="fw-bold mb-0 text-accent font-monospace">500+</h4>
                            <small class="text-muted">Events</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="glass-card p-3" style="border-radius: 28px; overflow: hidden; transform: rotate(2deg);">
                        <img src="assets/images/company_showcase_1.png" alt="Collaborative tech workspace" class="img-fluid" style="border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.3);">
                    </div>
                </div>
            </div>

            <div class="row align-items-center mt-5 pt-lg-5 flex-lg-row-reverse">
                <div class="col-lg-6 mb-4 mb-lg-0 ps-lg-5">
                    <span class="badge bg-accent-light text-accent mb-2 px-3 py-2" style="border-radius: 20px;">EMPOWERING ORGANIZERS</span>
                    <h2 class="fw-bold mb-4" style="font-size: 2.5rem; line-height: 1.2;">
                        Host events with absolute <span style="color: var(--accent);">confidence</span>.
                    </h2>
                    <p class="text-muted" style="font-size: 1.1rem; line-height: 1.8;">
                        Our administration framework protects participants and ensures host verification is pristine. With real-time capacity monitoring and automated registration approval states, managing attendees has never been this smooth.
                    </p>
                    <a href="about.php" class="btn btn-accent px-4 py-2 mt-3">Learn More About Us <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="glass-card p-3" style="border-radius: 28px; overflow: hidden; transform: rotate(-2deg);">
                        <img src="assets/images/company_showcase_2.png" alt="High-tech event hall presentation" class="img-fluid" style="border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.3);">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Theme Switcher Widget                                         -->
    <!-- ============================================================ -->
    <div class="theme-switcher">
        <button class="theme-switcher-btn" title="Switch Theme" aria-label="Switch Theme">
            🎨
        </button>
        <div class="theme-switcher-menu">
            <button class="theme-option" data-theme="midnight-dark">
                <span class="theme-swatch" style="background: #0f0f1a; border: 1px solid rgba(255,255,255,0.2);"></span>
                Midnight Dark
            </button>
            <button class="theme-option" data-theme="ocean-blue">
                <span class="theme-swatch" style="background: #f0f4f8; border: 1px solid rgba(0,0,0,0.1);"></span>
                Ocean Blue
            </button>
            <button class="theme-option" data-theme="forest-green">
                <span class="theme-swatch" style="background: #0a1410; border: 1px solid rgba(255,255,255,0.2);"></span>
                Forest Green
            </button>
            <button class="theme-option" data-theme="sunset-warm">
                <span class="theme-swatch" style="background: #fdf8f0; border: 1px solid rgba(0,0,0,0.1);"></span>
                Sunset Warm
            </button>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- Modern Footer                                                 -->
    <!-- ============================================================ -->
    <footer class="modern-footer">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-4 col-md-6">
                    <span class="footer-brand" style="color: var(--accent);">🎯 EventHub</span>
                    <p class="footer-desc">
                        Discover and register for world-class tech, business, and creative events. Elevate your potential today.
                    </p>
                    <div class="footer-socials">
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                        <a href="#"><i class="bi bi-github"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="footer-title">Quick Actions</h5>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="bi bi-house me-2"></i>Home</a></li>
                        <li><a href="about.php"><i class="bi bi-info-circle me-2"></i>About Us</a></li>
                        <li><a href="contact.php"><i class="bi bi-envelope me-2"></i>Contact Us</a></li>
                        <li><a href="auth/login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a></li>
                        <li><a href="auth/register.php"><i class="bi bi-person-plus me-2"></i>Register</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Contact Support</h5>
                    <p class="footer-desc">Need help? Get in touch with our team.</p>
                    <div class="footer-links mt-3">
                        <li><a href="contact.php"><i class="bi bi-chat-dots me-2"></i>Support Center</a></li>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; 2026 EventHub. All rights reserved.</span>
                <div>
                    <a href="#" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-muted text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3.2 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous"></script>

    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true, // whether animation should happen only once - while scrolling down
            offset: 50, // offset (in px) from the original trigger point
            duration: 800, // values from 0 to 3000, with step 50ms
            easing: 'ease-out-cubic', // default easing for AOS animations
        });
    </script>

    <!-- Custom JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>

<?php
/**
 * Helper: Map category name to an emoji icon
 */
function getCategoryIcon($category) {
    $icons = [
        'All'               => '🌐',
        'Technology'        => '💻',
        'Business'          => '💼',
        'Education'         => '📚',
        'Health & Wellness' => '🏥',
        'Arts & Culture'    => '🎨',
        'Sports'            => '⚽',
        'Networking'        => '🤝',
        'Workshop'          => '🔧',
        'General'           => '📌',
    ];
    return $icons[$category] ?? '📌';
}
?>
