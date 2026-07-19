<?php
/**
 * Standalone Contact Us Page
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
    <title>Contact Us | EventHub</title>
    
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
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">
                            <i class="bi bi-info-circle me-1"></i>About Us
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">
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
            <h1 class="display-4 fw-bold">Get In <span style="color: var(--accent);">Touch</span></h1>
            <p class="lead text-muted max-width-600 mx-auto">Have questions? We would love to hear from you. Get in touch with our team.</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container py-5">
        <div class="row justify-content-center" data-aos="fade-up">
            <div class="col-lg-8">
                <div class="glass-card p-5" style="border-radius: 24px;">
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <div class="p-4 text-center" style="background: rgba(255,255,255,0.02); border-radius: 16px; border: 1px solid var(--border);">
                                <i class="bi bi-envelope-paper display-4 mb-3" style="color: var(--accent);"></i>
                                <h5 class="fw-bold">Email Us</h5>
                                <p class="text-muted mb-0">We will update the email address soon!</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 text-center" style="background: rgba(255,255,255,0.02); border-radius: 16px; border: 1px solid var(--border);">
                                <i class="bi bi-telephone display-4 mb-3" style="color: var(--accent);"></i>
                                <h5 class="fw-bold">Call Us</h5>
                                <p class="text-muted mb-0">Mobile number will be provided later.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <h3 class="fw-bold text-center mb-4">Send Us a Message</h3>
                    <form action="#" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label text-muted">Your Name</label>
                                <input type="text" class="form-control form-control-custom" id="name" required placeholder="Enter name">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label text-muted">Your Email</label>
                                <input type="email" class="form-control form-control-custom" id="email" required placeholder="Enter email">
                            </div>
                            <div class="col-12">
                                <label for="subject" class="form-label text-muted">Subject</label>
                                <input type="text" class="form-control form-control-custom" id="subject" required placeholder="Enter subject">
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label text-muted">Message</label>
                                <textarea class="form-control form-control-custom" id="message" rows="5" required placeholder="Type your message here..."></textarea>
                            </div>
                            <div class="col-12 text-center mt-4">
                                <button type="button" class="btn btn-accent px-5 py-2" onclick="alert('Thank you! This contact form is in demo mode.')">
                                    <i class="bi bi-send me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

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
