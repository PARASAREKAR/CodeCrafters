<?php
/**
 * Application Public Browse Events Page
 */
session_start();
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/config/db_connect.php';

// ── Category Definitions (Limited to 9 Categories + Live Now) ──
$categories = [
    'All',
    'Live Now',
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
if ($filter_category === 'Live Now') {
    $where  = " WHERE e.Event_Date = CURDATE() AND e.Status = 'Approved' ";
} else {
    $where  = " WHERE e.Event_Date >= CURDATE() AND e.Status = 'Approved' ";
}
$params = [];

// Category filter
if ($filter_category !== '' && $filter_category !== 'All' && $filter_category !== 'Live Now') {
    $where   .= ' AND e.Event_Category = ? ';
    $params[] = $filter_category;
}

// Text search
if ($filter_search !== '') {
    $extra_conditions = '';
    $extra_params = [];
    $lower_search = strtolower($filter_search);
    
    // Auto-match Bangalore and Bengaluru aliases
    if (strpos($lower_search, 'bangalore') !== false || strpos($lower_search, 'bengaluru') !== false) {
        $extra_conditions = " OR e.Venue LIKE ? OR e.Venue LIKE ? ";
        $extra_params[] = '%Bangalore%';
        $extra_params[] = '%Bengaluru%';
    }
    
    $where   .= ' AND (e.Event_Name LIKE ? OR e.Description LIKE ? OR e.Venue LIKE ?' . $extra_conditions . ') ';
    $like_val = '%' . $filter_search . '%';
    $params[] = $like_val;
    $params[] = $like_val;
    $params[] = $like_val;
    
    foreach ($extra_params as $ep) {
        $params[] = $ep;
    }
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

// ── Main Query ──────────────────────────────────────────────
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

// ── Count events per category ───────────────────────────────
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
    if (in_array($row['Event_Category'], $categories)) {
        $category_counts[$row['Event_Category']] = (int) $row['cnt'];
        $total_upcoming += (int) $row['cnt'];
    }
}

// Recalculate total_upcoming including all events belonging to the active 9 categories
$stmt_total_active = $pdo->prepare(
    "SELECT COUNT(*) FROM events 
     WHERE Event_Date >= CURDATE() AND Status = 'Approved' 
     AND Event_Category IN ('Tech', 'Business', 'Creative', 'Sports', 'Music', 'Art', 'Food', 'Science', 'Health')"
);
$stmt_total_active->execute();
$total_upcoming = (int) $stmt_total_active->fetchColumn();

// Get the count of events happening today (Live Now)
$stmt_live = $pdo->prepare(
    "SELECT COUNT(*) FROM events 
     WHERE Event_Date = CURDATE() AND Status = 'Approved'"
);
$stmt_live->execute();
$live_count = (int) $stmt_live->fetchColumn();
$category_counts['Live Now'] = $live_count;

?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Events | EventHub</title>
    
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
    <style>
        .live-pulse-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.6rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: #ff4a4a;
            background: rgba(255, 74, 74, 0.1);
            border: 1px solid rgba(255, 74, 74, 0.2);
            border-radius: 50px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .pulse-dot {
            width: 6px;
            height: 6px;
            background: #ff4a4a;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(255, 74, 74, 0.7);
            animation: pulseDot 1.6s infinite;
        }
        @keyframes pulseDot {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(255, 74, 74, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(255, 74, 74, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(255, 74, 74, 0);
            }
        }
    </style>
</head>
<body>
    <!-- Theme Initialization -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'midnight-dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom" data-aos="fade-down" data-aos-delay="100">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <span style="color: var(--accent);">🎯</span> EventHub
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarLanding" aria-controls="navbarLanding"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarLanding">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="browse_events.php">
                            <i class="bi bi-search me-1"></i>Browse Events
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
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item ms-lg-2">
                            <?php $role = getUserRole(); ?>
                            <?php if ($role === 'Admin'): ?>
                                <a class="nav-link btn btn-accent btn-sm px-3 text-white" href="admin/admin_dashboard.php">
                                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                                </a>
                            <?php elseif ($role === 'Organizer'): ?>
                                <a class="nav-link btn btn-accent btn-sm px-3 text-white" href="organizer/organizer_dashboard.php">
                                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                                </a>
                            <?php else: ?>
                                <a class="nav-link btn btn-accent btn-sm px-3 text-white" href="participant/participant_dashboard.php">
                                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php else: ?>
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
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header Hero -->
    <header class="py-5 text-center bg-glass" style="margin-top: 120px; border-radius: 24px; margin-left: 15px; margin-right: 15px;">
        <div class="container py-4" data-aos="zoom-in">
            <h1 class="display-4 fw-bold">Explore <span style="color: var(--accent);">Events</span></h1>
            <p class="lead text-muted max-width-600 mx-auto">Browse through our curated lists of upcoming meetups, conferences, workshops, and gatherings.</p>
        </div>
    </header>

    <!-- Events Section -->
    <section id="events-section" class="py-5">
        <div class="container">

            <!-- Category Tabs -->
            <div class="category-tabs-wrapper mb-4" data-aos="fade-up">
                <div class="category-tabs" id="categoryTabs">
                    <?php foreach ($categories as $cat): ?>
                        <?php
                            $is_active = ($filter_category === $cat);
                            $count = ($cat === 'All') ? $total_upcoming : ($category_counts[$cat] ?? 0);
                            $tab_params = $_GET;
                            $tab_params['category'] = $cat;
                            $tab_url = 'browse_events.php?' . http_build_query($tab_params) . '#events-section';
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
            <div class="filters-bar card-custom glass-card p-3 mb-4" data-aos="fade-up" data-aos-delay="100">
                <form method="GET" action="browse_events.php#events-section" class="row g-3 align-items-end">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($filter_category, ENT_QUOTES, 'UTF-8'); ?>">

                    <!-- Text Search -->
                    <div class="col-md-4">
                        <label for="search" class="form-label small fw-semibold">
                            <i class="bi bi-search me-1"></i>Search
                        </label>
                        <input type="text" id="search" name="search"
                               class="form-control form-control-custom"
                               placeholder="Event name, description, venue, or city (e.g. Pune, Mumbai, Bangalore)…"
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
                        <a href="browse_events.php#events-section" class="btn btn-outline-accent flex-fill btn-sm">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Results Header -->
            <div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up" data-aos-delay="150">
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
                <div class="empty-state card-custom glass-card text-center py-5" data-aos="fade-up" data-aos-delay="200">
                    <span class="empty-icon">📭</span>
                    <h5 class="empty-title">No Events Found</h5>
                    <p class="empty-text">
                        No upcoming events match your filters. Try adjusting your search query or selecting a different category.
                    </p>
                    <a href="browse_events.php" class="btn btn-outline-accent mt-3">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Clear All Filters
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($events as $index => $event):
                        $capacity   = (int) $event['Capacity'];
                        $filled     = (int) $event['filled'];
                        $remaining  = max(0, $capacity - $filled);
                        $pct_filled = $capacity > 0 ? round(($filled / $capacity) * 100) : 100;
                        $is_full    = ($filled >= $capacity);

                        $description_full = $event['Description'] ?? '';
                        $words = explode(' ', strip_tags($description_full));
                        $excerpt = implode(' ', array_slice($words, 0, 30));
                        if (count($words) > 30) $excerpt .= '…';

                        $event_cat = $event['Event_Category'] ?? 'General';
                        $is_live_today = ($event['Event_Date'] === date('Y-m-d'));

                        $cap_class = 'capacity-low';
                        if ($pct_filled >= 80) $cap_class = 'capacity-high';
                        elseif ($pct_filled >= 50) $cap_class = 'capacity-medium';
                    ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($index % 3) * 100; ?>">
                        <div class="card-custom glass-card landing-event-card h-100 d-flex flex-column">
                            <div class="event-card-image-wrapper">
                                <?php if (!empty($event['Image_Path']) && file_exists($event['Image_Path'])): ?>
                                    <img src="<?php echo htmlspecialchars($event['Image_Path'], ENT_QUOTES, 'UTF-8'); ?>" alt="Event Image" class="event-card-image">
                                <?php else: ?>
                                    <?php $placeholder_id = ($index % 3) + 1; ?>
                                    <img src="assets/images/placeholder-<?php echo $placeholder_id; ?>.png" alt="Event Image" class="event-card-image">
                                <?php endif; ?>
                            </div>

                            <div class="event-card-body">
                                <div class="event-card-header">
                                    <span class="event-category-badge">
                                        <?php echo getCategoryIcon($event_cat); ?>
                                        <?php echo htmlspecialchars($event_cat, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <?php if ($is_live_today): ?>
                                        <span class="live-pulse-badge"><span class="pulse-dot"></span> LIVE TODAY</span>
                                    <?php endif; ?>
                                    <?php if ($is_full): ?>
                                        <span class="event-full-badge">FULL</span>
                                    <?php endif; ?>
                                </div>

                                <h5 class="event-card-title">
                                    <?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?>
                                </h5>

                                <?php if (!empty($excerpt)): ?>
                                    <p class="event-card-desc">
                                        <?php echo htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                <?php endif; ?>

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
                                </ul>

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
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- Footer -->
    <footer class="modern-footer mt-auto">
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
                        <li><a href="browse_events.php"><i class="bi bi-search me-2"></i>Browse Events</a></li>
                        <li><a href="about.php"><i class="bi bi-info-circle me-2"></i>About Us</a></li>
                        <li><a href="contact.php"><i class="bi bi-envelope me-2"></i>Contact Us</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="auth/login.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="auth/login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a></li>
                            <li><a href="auth/register.php"><i class="bi bi-person-plus me-2"></i>Register</a></li>
                        <?php endif; ?>
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
            once: true,
            offset: 50,
            duration: 800,
            easing: 'ease-out-cubic',
        });
    </script>
</body>
</html>
<?php
function getCategoryIcon($category) {
    $icons = [
        'All'               => '🌐',
        'Live Now'          => '🔴',
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
