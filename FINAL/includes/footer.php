<?php
/**
 * Shared Footer Template
 * ----------------------
 * Included at the bottom of every page. Provides:
 * - Closing </main> tag
 * - Subtle footer bar with copyright
 * - Bootstrap 5.3.2 JS bundle
 * - Custom main.js script
 * - Closing </body> and </html>
 */
?>
    </main>
    <!-- Main Content Ends -->

    <!-- ============================================================ -->
    <!-- Theme Switcher Widget -->
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
    <!-- Modern Footer -->
    <!-- ============================================================ -->
    <!-- ============================================================ -->
    <!-- Modern Footer -->
    <!-- ============================================================ -->
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
                        <?php if (isLoggedIn()): ?>
                            <?php $role = getUserRole(); ?>
                            <?php if ($role === 'Admin'): ?>
                                <li><a href="../admin/admin_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                <li><a href="../admin/manage_users.php"><i class="bi bi-people me-2"></i>Manage Users</a></li>
                                <li><a href="../admin/manage_requests.php"><i class="bi bi-check-circle me-2"></i>Manage Requests</a></li>
                                <li><a href="../admin/reports.php"><i class="bi bi-file-earmark-bar-graph me-2"></i>System Reports</a></li>
                            <?php elseif ($role === 'Organizer'): ?>
                                <li><a href="../organizer/organizer_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                <li><a href="../organizer/create_event.php"><i class="bi bi-plus-circle me-2"></i>Create Event</a></li>
                                <li><a href="../organizer/reports.php"><i class="bi bi-file-earmark-bar-graph me-2"></i>Event Reports</a></li>
                            <?php elseif ($role === 'Participant'): ?>
                                <li><a href="../participant/participant_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                <li><a href="../participant/browse_events.php"><i class="bi bi-search me-2"></i>Browse Events</a></li>
                                <li><a href="../participant/my_registrations.php"><i class="bi bi-journal-check me-2"></i>My Registrations</a></li>
                            <?php endif; ?>
                            <li><a href="../auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        <?php else: ?>
                            <li><a href="../index.php"><i class="bi bi-house me-2"></i>Home</a></li>
                            <li><a href="../about.php"><i class="bi bi-info-circle me-2"></i>About Us</a></li>
                            <li><a href="../contact.php"><i class="bi bi-envelope me-2"></i>Contact Us</a></li>
                            <li><a href="../auth/login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a></li>
                            <li><a href="../auth/register.php"><i class="bi bi-person-plus me-2"></i>Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Contact Support</h5>
                    <p class="footer-desc">Need help? Get in touch with our team.</p>
                    <div class="footer-links mt-3">
                        <li><a href="../contact.php"><i class="bi bi-chat-dots me-2"></i>Support Center</a></li>
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

    <!-- Bootstrap 5.3.2 JS Bundle (includes Popper) -->
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
    <script src="../assets/js/main.js"></script>
</body>
</html>
