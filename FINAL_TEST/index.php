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
    'Tech',
    'Business',
    'Creative',
    'Sports',
    'Music',
    'Art',
    'Food',
    'Science',
    'Health'
];

// ── Read Venue list ──────────────────────────────────────────
$stmt_venues = $pdo->prepare(
    "SELECT DISTINCT Venue FROM events WHERE Event_Date >= CURDATE() AND Status = 'Approved' ORDER BY Venue ASC"
);
$stmt_venues->execute();
$venue_list = $stmt_venues->fetchAll(PDO::FETCH_COLUMN);

// ── Read Total upcoming events ───────────────────────────────
$stmt_total = $pdo->prepare(
    "SELECT COUNT(*) FROM events 
     WHERE Event_Date >= CURDATE() AND Status = 'Approved'
     AND Event_Category IN ('Tech', 'Business', 'Creative', 'Sports', 'Music', 'Art', 'Food', 'Science', 'Health')"
);
$stmt_total->execute();
$total_upcoming = (int) $stmt_total->fetchColumn();
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
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
                <img src="assets/images/logo.png" alt="EventHub Logo" class="rounded-circle shadow-sm" style="width: 38px; height: 38px; object-fit: cover; border: 2px solid var(--accent);"> EventHub
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
                        <a class="nav-link" href="about.php">
                            <i class="bi bi-info-circle me-1"></i>About Us
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="browse_events.php">
                            <i class="bi bi-calendar-event me-1"></i>Browse Events
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
                <form method="GET" action="browse_events.php" class="hero-search-form">
                    <div class="hero-search-box">
                        <i class="bi bi-search hero-search-icon"></i>
                        <input type="text" name="search" class="hero-search-input"
                               placeholder="Search by event name, venue, city (e.g. Pune, Mumbai, Bangalore)…"
                               value="">
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
                            <h4 class="fw-bold mb-0 text-accent font-monospace">50+</h4>
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
                    <span class="footer-brand" style="color: var(--accent);"><img src="assets/images/logo.png" alt="EventHub Logo" class="rounded-circle shadow-sm" style="width: 38px; height: 38px; object-fit: cover; border: 2px solid var(--accent);"> EventHub</span>
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
                        <li><a href="javascript:void(0);" onclick="showSupportUnderProcess(event);"><i class="bi bi-chat-dots me-2"></i>Support Center</a></li>
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
        'Tech'              => '💻',
        'Business'          => '💼',
        'Creative'          => '💡',
        'Sports'            => '⚽',
        'Music'             => '🎵',
        'Art'               => '🎨',
        'Food'              => '🍛',
        'Science'           => '🔬',
        'Health'            => '🏥',
    ];
    return $icons[$category] ?? '📌';
}
?>
