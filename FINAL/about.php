<?php
/**
 * Standalone About Us Page
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/helpers.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | EventHub</title>
    
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

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom" data-aos="fade-down" data-aos-delay="100">
        <div class="container">
            <!-- Brand -->
            <a class="navbar-brand fw-bold" href="index.php">
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
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="browse_events.php">
                            <i class="bi bi-calendar-event me-1"></i>Browse Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">
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
    <header class="py-5 text-center position-relative overflow-hidden" style="margin-top: 120px; border-radius: 24px; margin-left: 15px; margin-right: 15px; min-height: 300px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: url('assets/images/hero-bg-premium.png') center/cover no-repeat; filter: brightness(0.35);"></div>
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.7));"></div>
        <div class="container position-relative z-1 py-4" data-aos="zoom-in">
            <h1 class="display-4 fw-bold text-white text-shadow-sm">About <span style="color: var(--accent);">EventHub</span></h1>
            <p class="lead text-light max-width-600 mx-auto text-shadow-sm" style="opacity: 0.9;">Bridging the gap between passionate event organizers and attendees worldwide.</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container py-5">
        <section class="row align-items-center mb-5" data-aos="fade-up">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h3 class="fw-bold mb-3">Our Mission</h3>
                <p class="text-muted" style="line-height: 1.8;">
                    EventHub was founded with a singular, powerful vision: to make event management and discovery completely seamless. We believe in the power of shared experiences and community gathering to drive innovation, learning, and growth.
                </p>
                <p class="text-muted" style="line-height: 1.8;">
                    By providing organizers with intuitive creation and approval tools, and giving attendees a clean, visual platform to discover opportunities, we help make every event a resounding success.
                </p>
            </div>
            <div class="col-lg-6 text-center">
                <div class="glass-card p-3" style="border-radius: 28px; overflow: hidden; transform: rotate(1deg);">
                    <img src="assets/images/company_showcase_3.png" alt="High-tech event schedules lobby showcase" class="img-fluid" style="border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.3);">
                </div>
            </div>
        </section>

        <!-- Company Values -->
        <section class="py-5" data-aos="fade-up">
            <h3 class="text-center fw-bold mb-5">Our Core Values</h3>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100 text-center">
                        <i class="bi bi-shield-check display-5 mb-3" style="color: var(--accent);"></i>
                        <h5 class="fw-bold">Trust & Security</h5>
                        <p class="text-muted">An admin-verified ecosystem ensuring high quality and authentic listings.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100 text-center">
                        <i class="bi bi-lightning-charge display-5 mb-3" style="color: var(--accent);"></i>
                        <h5 class="fw-bold">Seamless Experience</h5>
                        <p class="text-muted">One-click registrations, quick filters, and interactive capacity trackers.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100 text-center">
                        <i class="bi bi-people display-5 mb-3" style="color: var(--accent);"></i>
                        <h5 class="fw-bold">Community-Focused</h5>
                        <p class="text-muted">Built to cultivate lasting relationships, networking, and direct collaboration.</p>
                    </div>
                </div>
            </div>
        <!-- How EventHub Works -->
        <section class="py-5 mb-5">
            <style>
                .relative-card {
                    position: relative;
                    overflow: visible !important;
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                }
                .relative-card:hover {
                    transform: translateY(-8px);
                    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2) !important;
                }
                .step-badge {
                    position: absolute;
                    top: -18px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 36px;
                    height: 36px;
                    background: var(--accent);
                    color: #fff;
                    font-weight: 700;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
                    border: 2px solid var(--border);
                }
            </style>
            <h3 class="text-center fw-bold mb-5" data-aos="fade-up">How EventHub Works</h3>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="glass-card p-4 h-100 text-center relative-card">
                        <div class="step-badge">1</div>
                        <i class="bi bi-search display-5 mb-3 d-block mt-2" style="color: var(--accent);"></i>
                        <h5 class="fw-bold">Discover Events</h5>
                        <p class="text-muted">Explore world-class events, tech conferences, and workshops with custom category filters and dates.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="glass-card p-4 h-100 text-center relative-card">
                        <div class="step-badge">2</div>
                        <i class="bi bi-pencil-square display-5 mb-3 d-block mt-2" style="color: var(--accent);"></i>
                        <h5 class="fw-bold">Register Instantly</h5>
                        <p class="text-muted">Register for events in one click. Track real-time seat availability and manage pending approvals.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="glass-card p-4 h-100 text-center relative-card">
                        <div class="step-badge">3</div>
                        <i class="bi bi-check-circle display-5 mb-3 d-block mt-2" style="color: var(--accent);"></i>
                        <h5 class="fw-bold">Attend & Engage</h5>
                        <p class="text-muted">Get your attendance marked by organizers, network with peers, and download event materials.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="modern-footer mt-auto">
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
            once: true,
            offset: 50,
            duration: 800,
            easing: 'ease-out-cubic',
        });
    </script>
</body>
</html>
