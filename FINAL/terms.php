<?php
/**
 * Terms and Conditions Page – EventHub
 * ------------------------------------
 * Public Terms of Service & User Agreement page.
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
    <title>Terms & Conditions | EventHub</title>

    <!-- Bootstrap 5.3.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom Stylesheets -->
    <link rel="stylesheet" href="assets/css/themes.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/landing.css">

    <style>
        .toc-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            position: sticky;
            top: 100px;
        }

        .toc-link {
            color: var(--text-secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.92rem;
            transition: all 0.2s ease;
        }

        .toc-link:hover {
            color: var(--accent);
            background: var(--accent-light);
            transform: translateX(4px);
        }

        .terms-section {
            scroll-margin-top: 100px;
        }

        .term-badge {
            background: var(--accent-light);
            color: var(--accent);
            border: 1px solid var(--border);
            font-weight: 600;
            font-size: 0.82rem;
            padding: 4px 12px;
            border-radius: 50px;
        }

        .print-btn {
            background: var(--bg-card);
            color: var(--text-primary);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 8px 16px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .print-btn:hover {
            color: var(--accent);
            border-color: var(--accent);
        }
    </style>
</head>

<body>
    <!-- Theme Initialization -->
    <script>
        (function () {
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLanding"
                aria-controls="navbarLanding" aria-expanded="false" aria-label="Toggle navigation">
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
                                <a class="nav-link btn btn-accent btn-sm px-3 text-white"
                                    href="organizer/organizer_dashboard.php">
                                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                                </a>
                            <?php else: ?>
                                <a class="nav-link btn btn-accent btn-sm px-3 text-white"
                                    href="participant/participant_dashboard.php">
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
    <header class="py-5 text-center bg-glass"
        style="margin-top: 120px; border-radius: 24px; margin-left: 15px; margin-right: 15px;">
        <div class="container py-4" data-aos="zoom-in">
            <div class="d-inline-flex align-items-center justify-content-center p-3 mb-3 rounded-circle"
                style="background: var(--accent-light);">
                <i class="bi bi-file-earmark-text-fill fs-1" style="color: var(--accent);"></i>
            </div>
            <h1 class="fw-extrabold display-5 mb-2" style="color: var(--text-primary);">Terms & Conditions</h1>
            <p class="lead max-w-600 mx-auto" style="color: var(--text-secondary); font-size: 1.1rem;">
                Please review these Terms of Service carefully before registering for events or utilizing the EventHub
                platform.
            </p>
            <div class="d-flex align-items-center justify-content-center gap-3 mt-4 flex-wrap">
                <span class="term-badge"><i class="bi bi-calendar-check me-1"></i>Effective Date: July 24, 2026</span>
                <span class="term-badge"><i class="bi bi-shield-check me-1"></i>Version 2.4</span>
                <button onclick="window.print()" class="print-btn border-0 shadow-sm">
                    <i class="bi bi-printer me-1"></i>Print Terms
                </button>
            </div>
        </div>
    </header>

    <!-- Terms Content Area -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">

                <!-- Sticky Table of Contents Sidebar -->
                <div class="col-lg-4 d-none d-lg-block">
                    <div class="toc-card p-4 shadow-sm" data-aos="fade-right">
                        <h6 class="fw-bold mb-3 text-uppercase tracking-wider"
                            style="color: var(--accent); font-size: 0.85rem;">
                            <i class="bi bi-list-nested me-2"></i>Table of Contents
                        </h6>
                        <nav class="d-flex flex-column gap-1">
                            <a href="#section-1" class="toc-link"><i class="bi bi-chevron-right me-2 small"></i>1.
                                Acceptance of Terms</a>
                            <a href="#section-2" class="toc-link"><i class="bi bi-chevron-right me-2 small"></i>2. User
                                Accounts & Privileges</a>
                            <a href="#section-3" class="toc-link"><i class="bi bi-chevron-right me-2 small"></i>3. Event
                                Hosting & Organizers</a>
                            <a href="#section-4" class="toc-link"><i class="bi bi-chevron-right me-2 small"></i>4. Event
                                Registration & Entry</a>
                            <a href="#section-5" class="toc-link"><i class="bi bi-chevron-right me-2 small"></i>5.
                                Cancellations & Refunds</a>
                            <a href="#section-6" class="toc-link"><i class="bi bi-chevron-right me-2 small"></i>6.
                                Acceptable Use & Conduct</a>
                            <a href="#section-7" class="toc-link"><i class="bi bi-chevron-right me-2 small"></i>7.
                                Intellectual Property Rights</a>
                            <a href="#section-8" class="toc-link"><i class="bi bi-chevron-right me-2 small"></i>8.
                                Privacy & Data Protection</a>
                            <a href="#section-9" class="toc-link"><i class="bi bi-chevron-right me-2 small"></i>9.
                                Disclaimers & Liability</a>
                            <a href="#section-10" class="toc-link"><i class="bi bi-chevron-right me-2 small"></i>10.
                                Termination & Updates</a>
                            <a href="#section-11" class="toc-link"><i class="bi bi-chevron-right me-2 small"></i>11.
                                Contact & Support</a>
                        </nav>

                        <hr style="border-color: var(--border);">

                        <div class="p-3 rounded-3" style="background: var(--accent-light);">
                            <small class="d-block mb-1 fw-semibold" style="color: var(--accent);">Have
                                questions?</small>
                            <small class="d-block text-muted mb-2">Our support team is available 24/7 to assist with
                                compliance inquiries.</small>
                            <a href="contact.php" class="btn btn-sm btn-accent w-100 text-white fw-semibold">
                                <i class="bi bi-chat-left-text me-1"></i>Contact Support
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Terms Details Main Body -->
                <div class="col-lg-8">

                    <!-- Preamble Card -->
                    <div class="card card-custom glass-card p-4 p-md-5 mb-4 shadow-sm" data-aos="fade-up">
                        <h4 class="fw-bold mb-3" style="color: var(--text-primary);">
                            <i class="bi bi-bookmark-star-fill text-accent me-2"></i>Welcome to EventHub
                        </h4>
                        <p style="color: var(--text-secondary); line-height: 1.8;">
                            These Terms and Conditions ("Terms", "Agreement") govern your access to and use of the
                            EventHub online event registration platform, including any subdomains, mobile views,
                            services, features, and tools provided by EventHub ("we", "us", or "our").
                        </p>
                        <p style="color: var(--text-secondary); line-height: 1.8;" class="mb-0">
                            By creating an account, browsing events, or registering for any event through EventHub, you
                            acknowledge that you have read, understood, and agree to be legally bound by these Terms. If
                            you do not agree with any part of these terms, you must refrain from using the platform.
                        </p>
                    </div>

                    <!-- Section 1 -->
                    <div id="section-1" class="terms-section card card-custom glass-card p-4 p-md-5 mb-4 shadow-sm"
                        data-aos="fade-up">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded me-3"
                                style="background: var(--accent-light); color: var(--accent);">
                                <i class="bi bi-patch-check-fill fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">1. Acceptance of Terms</h5>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.7;">
                            By accessing or using our services, you confirm that you are at least 18 years of age or
                            possess legal parental or guardian consent to enter into these Terms. Accessing EventHub on
                            behalf of a corporation, educational institution, or organization implies that you possess
                            full authority to bind that entity to these Terms.
                        </p>
                        <ul style="color: var(--text-secondary); line-height: 1.7;" class="mb-0">
                            <li>All users must maintain compliance with applicable local, state, national, and
                                international laws.</li>
                            <li>These terms apply equally to Participants, Event Organizers, and Site Administrators.
                            </li>
                        </ul>
                    </div>

                    <!-- Section 2 -->
                    <div id="section-2" class="terms-section card card-custom glass-card p-4 p-md-5 mb-4 shadow-sm"
                        data-aos="fade-up">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded me-3"
                                style="background: var(--accent-light); color: var(--accent);">
                                <i class="bi bi-person-badge-fill fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">2. User Accounts & Security
                            </h5>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.7;">
                            To access specific features of EventHub—such as hosting events or registering for
                            limited-capacity sessions—you must create an account.
                        </p>
                        <ul style="color: var(--text-secondary); line-height: 1.7;">
                            <li><strong>Account Accuracy:</strong> You agree to provide accurate, current, and complete
                                registration information (including full name, valid email address, and 10-digit mobile
                                number).</li>
                            <li><strong>Credential Confidentiality:</strong> You are responsible for safeguarding your
                                login password. EventHub implements high-security password standards; however, any
                                actions executed under your authenticated account remain your sole responsibility.</li>
                            <li><strong>Role Privilege Boundaries:</strong> Accounts are assigned specific roles
                                (Participant, Organizer, Admin). Attempting to bypass role-based permissions or access
                                unauthorized administrative routes is strictly forbidden.</li>
                        </ul>
                        <div class="alert alert-warning border-0 d-flex align-items-center gap-3 mb-0"
                            style="background: var(--warning-bg); color: var(--warning);">
                            <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0"></i>
                            <div>
                                Immediate notice must be provided to <a href="contact.php" class="alert-link">EventHub
                                    Support</a> upon discovering any unauthorized use or security breach of your
                                account.
                            </div>
                        </div>
                    </div>

                    <!-- Section 3 -->
                    <div id="section-3" class="terms-section card card-custom glass-card p-4 p-md-5 mb-4 shadow-sm"
                        data-aos="fade-up">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded me-3"
                                style="background: var(--accent-light); color: var(--accent);">
                                <i class="bi bi-calendar2-check-fill fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">3. Event Hosting & Organizer
                                Responsibilities</h5>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.7;">
                            Event Organizers who create, publish, and manage events on EventHub must adhere to strict
                            quality and transparency criteria:
                        </p>
                        <ul style="color: var(--text-secondary); line-height: 1.7;" class="mb-0">
                            <li><strong>Event Authenticity:</strong> All event details—including date, time,
                                venue/online link, maximum participant capacity, category, and description—must be
                                accurate and truthful.</li>
                            <li><strong>Approval Workflow:</strong> New event creations may be subject to review by
                                EventHub Administrators prior to public listing.</li>
                            <li><strong>Venue Safety & Compliance:</strong> For physical events, Organizers are solely
                                responsible for securing physical venue permissions, safety compliance, and insurance.
                            </li>
                            <li><strong>Communication:</strong> Organizers must notify registered participants promptly
                                of any schedule modifications, venue changes, or postponements.</li>
                        </ul>
                    </div>

                    <!-- Section 4 -->
                    <div id="section-4" class="terms-section card card-custom glass-card p-4 p-md-5 mb-4 shadow-sm"
                        data-aos="fade-up">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded me-3"
                                style="background: var(--accent-light); color: var(--accent);">
                                <i class="bi bi-ticket-perforated-fill fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">4. Ticket Booking & Participant
                                Guidelines</h5>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.7;">
                            Participants using EventHub to register for events agree to the following terms:
                        </p>
                        <ul style="color: var(--text-secondary); line-height: 1.7;" class="mb-0">
                            <li><strong>Registration Confirmation:</strong> A registration is deemed confirmed once
                                recorded in the EventHub system and visible under <em>My Registrations</em>.</li>
                            <li><strong>Non-Transferability:</strong> Unless explicitly allowed by the Organizer, event
                                registrations and digital badges/passports are non-transferable between users.</li>
                            <li><strong>Capacity Limits:</strong> Event registrations operate on a first-come,
                                first-served basis up to the seat capacity defined by the Organizer.</li>
                            <li><strong>Check-in Verification:</strong> Participants must present valid proof of
                                registration (digital pass or email confirmation) upon check-in at physical or virtual
                                venues.</li>
                        </ul>
                    </div>

                    <!-- Section 5 -->
                    <div id="section-5" class="terms-section card card-custom glass-card p-4 p-md-5 mb-4 shadow-sm"
                        data-aos="fade-up">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded me-3"
                                style="background: var(--accent-light); color: var(--accent);">
                                <i class="bi bi-cash-coin fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">5. Payments, Cancellations &
                                Refunds</h5>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.7;">
                            Registration fee policies and refund conditions are governed as follows:
                        </p>
                        <ul style="color: var(--text-secondary); line-height: 1.7;" class="mb-0">
                            <li><strong>Free & Paid Events:</strong> EventHub hosts both free community events and paid
                                registrations. Paid events display pricing transparently before confirmation.</li>
                            <li><strong>Participant Cancellations:</strong> Participants may request registration
                                cancellation via their dashboard subject to the specific refund window specified by the
                                event organizer.</li>
                            <li><strong>Event Cancellation by Organizer:</strong> If an event is cancelled by the
                                Organizer or EventHub Administration, registered participants will be notified
                                immediately and issued full refunds where applicable.</li>
                        </ul>
                    </div>

                    <!-- Section 6 -->
                    <div id="section-6" class="terms-section card card-custom glass-card p-4 p-md-5 mb-4 shadow-sm"
                        data-aos="fade-up">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded me-3"
                                style="background: var(--accent-light); color: var(--accent);">
                                <i class="bi bi-shield-x fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">6. Code of Conduct & Acceptable
                                Use</h5>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.7;">
                            To maintain a safe, inclusive, and collaborative environment, all users must refrain from
                            prohibited conduct including:
                        </p>
                        <ul style="color: var(--text-secondary); line-height: 1.7;" class="mb-0">
                            <li>Publishing false, misleading, defamatory, offensive, or illegal content.</li>
                            <li>Engaging in harassment, discrimination, or hate speech toward speakers, organizers, or
                                fellow participants.</li>
                            <li>Attempting automated data extraction, scraping, vulnerability scanning, or Denial of
                                Service (DoS) attacks.</li>
                            <li>Submitting duplicate or fake registrations to hoard event seat capacities.</li>
                        </ul>
                    </div>

                    <!-- Section 7 -->
                    <div id="section-7" class="terms-section card card-custom glass-card p-4 p-md-5 mb-4 shadow-sm"
                        data-aos="fade-up">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded me-3"
                                style="background: var(--accent-light); color: var(--accent);">
                                <i class="bi bi-award-fill fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">7. Intellectual Property Rights
                            </h5>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.7;">
                            The EventHub platform code, brand, visual assets, logos, design templates, and database
                            architectures are the exclusive property of EventHub and CodeCrafters.
                        </p>
                        <p style="color: var(--text-secondary); line-height: 1.7;" class="mb-0">
                            Organizers retain ownership of custom banners, logos, and presentation materials uploaded to
                            their event listings, while granting EventHub a non-exclusive license to display such media
                            for event promotion.
                        </p>
                    </div>

                    <!-- Section 8 -->
                    <div id="section-8" class="terms-section card card-custom glass-card p-4 p-md-5 mb-4 shadow-sm"
                        data-aos="fade-up">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded me-3"
                                style="background: var(--accent-light); color: var(--accent);">
                                <i class="bi bi-lock-fill fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">8. Privacy & Data Protection
                            </h5>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.7;">
                            We take data protection seriously. Personal information collected during registration (name,
                            email, phone number, role) is handled strictly in accordance with system privacy standards.
                        </p>
                        <ul style="color: var(--text-secondary); line-height: 1.7;" class="mb-0">
                            <li>Data is utilized solely to process event registrations, communicate updates, and secure
                                user accounts.</li>
                            <li>We do not sell personal information to third-party advertisers.</li>
                            <li>Cookies and browser local storage are used exclusively for theme preference retention
                                and session management.</li>
                        </ul>
                    </div>

                    <!-- Section 9 -->
                    <div id="section-9" class="terms-section card card-custom glass-card p-4 p-md-5 mb-4 shadow-sm"
                        data-aos="fade-up">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded me-3"
                                style="background: var(--accent-light); color: var(--accent);">
                                <i class="bi bi-exclamation-octagon-fill fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">9. Limitation of Liability &
                                Disclaimers</h5>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.7;">
                            EventHub is provided on an "AS IS" and "AS AVAILABLE" basis. While we strive for
                            uninterrupted 99.9% platform availability, we do not warrant that services will be
                            uninterrupted or error-free.
                        </p>
                        <p style="color: var(--text-secondary); line-height: 1.7;" class="mb-0">
                            EventHub is not liable for indirect, incidental, or consequential damages resulting from
                            event cancellations, organizer misrepresentations, third-party venue disruptions, or
                            unforeseen network outages.
                        </p>
                    </div>

                    <!-- Section 10 -->
                    <div id="section-10" class="terms-section card card-custom glass-card p-4 p-md-5 mb-4 shadow-sm"
                        data-aos="fade-up">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded me-3"
                                style="background: var(--accent-light); color: var(--accent);">
                                <i class="bi bi-arrow-repeat fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">10. Account Termination &
                                Amendments</h5>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.7;">
                            EventHub reserves the right to suspend or terminate accounts that violate these Terms or
                            engage in fraudulent activities without prior notice.
                        </p>
                        <p style="color: var(--text-secondary); line-height: 1.7;" class="mb-0">
                            We may update these Terms from time to time. Continued use of the platform following
                            published amendments constitutes your acceptance of the updated Terms.
                        </p>
                    </div>

                    <!-- Section 11 -->
                    <div id="section-11" class="terms-section card card-custom glass-card p-4 p-md-5 mb-4 shadow-sm"
                        data-aos="fade-up">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded me-3"
                                style="background: var(--accent-light); color: var(--accent);">
                                <i class="bi bi-headset fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: var(--text-primary);">11. Contact & Support Center
                            </h5>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.7;">
                            If you have questions, feedback, or legal inquiries regarding these Terms and Conditions,
                            please contact our team:
                        </p>
                        <div class="p-4 rounded-3 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3"
                            style="background: var(--bg-card); border: 1px solid var(--border);">
                            <div>
                                <h6 class="fw-bold mb-1" style="color: var(--text-primary);">EventHub Compliance &
                                    Support</h6>
                                <p class="mb-0 small" style="color: var(--text-secondary);">Email: support@eventhub.com
                                    | CodeCrafters Team</p>
                            </div>
                            <a href="contact.php" class="btn btn-accent text-white px-4 py-2 text-nowrap">
                                <i class="bi bi-envelope-fill me-1"></i>Contact Page
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Theme Switcher Widget -->
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

    <!-- Modern Footer -->
    <footer class="modern-footer mt-auto">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-4 col-md-6">
                    <span class="footer-brand" style="color: var(--accent);">🎯 EventHub</span>
                    <p class="footer-desc">
                        Discover and register for world-class tech, business, and creative events. Elevate your
                        potential today.
                    </p>
                    <div class="footer-socials">
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                        <a href="#"><i class="bi bi-github"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="bi bi-house me-2"></i>Home</a></li>
                        <li><a href="about.php"><i class="bi bi-info-circle me-2"></i>About Us</a></li>
                        <li><a href="contact.php"><i class="bi bi-envelope me-2"></i>Contact Us</a></li>
                        <li><a href="terms.php"><i class="bi bi-file-earmark-text me-2"></i>Terms & Conditions</a></li>
                        <li><a href="auth/login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Contact Support</h5>
                    <p class="footer-desc">Need help? Get in touch with our support team.</p>
                    <div class="footer-links mt-3">
                        <li><a href="contact.php"><i class="bi bi-envelope me-2"></i>Send Message</a></li>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; 2026 EventHub. All rights reserved.</span>
                <div>
                    <a href="#" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                    <a href="terms.php" class="text-muted text-decoration-none">Terms of Service</a>
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

    <!-- Custom JavaScript -->
    <script src="assets/js/main.js"></script>
</body>

</html>