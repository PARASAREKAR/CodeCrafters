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
    <?php
    $script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $is_in_subdir = (
        strpos($script_path, '/admin/') !== false ||
        strpos($script_path, '/organizer/') !== false ||
        strpos($script_path, '/participant/') !== false ||
        strpos($script_path, '/auth/') !== false
    );
    $footer_base = $is_in_subdir ? '../' : '';
    ?>
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
                                <li><a href="<?php echo $footer_base; ?>admin/admin_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                <li><a href="<?php echo $footer_base; ?>admin/manage_users.php"><i class="bi bi-people me-2"></i>Manage Users</a></li>
                                <li><a href="<?php echo $footer_base; ?>admin/view_messages.php"><i class="bi bi-envelope me-2"></i>Support Inbox</a></li>
                            <?php elseif ($role === 'Organizer'): ?>
                                <li><a href="<?php echo $footer_base; ?>organizer/organizer_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                <li><a href="<?php echo $footer_base; ?>organizer/create_event.php"><i class="bi bi-plus-circle me-2"></i>Create Event</a></li>
                            <?php elseif ($role === 'Participant'): ?>
                                <li><a href="<?php echo $footer_base; ?>participant/participant_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                <li><a href="<?php echo $footer_base; ?>participant/browse_events.php"><i class="bi bi-search me-2"></i>Browse Events</a></li>
                                <li><a href="<?php echo $footer_base; ?>participant/my_registrations.php"><i class="bi bi-journal-check me-2"></i>My Registrations</a></li>
                            <?php endif; ?>
                            <li><a href="javascript:void(0);" onclick="showSupportUnderProcess(event);"><i class="bi bi-chat-dots me-2"></i>Support Center</a></li>
                            <li><a href="<?php echo $footer_base; ?>auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo $footer_base; ?>index.php"><i class="bi bi-house me-2"></i>Home</a></li>
                            <li><a href="<?php echo $footer_base; ?>about.php"><i class="bi bi-info-circle me-2"></i>About Us</a></li>
                            <li><a href="<?php echo $footer_base; ?>contact.php"><i class="bi bi-envelope me-2"></i>Contact Us</a></li>
                            <li><a href="<?php echo $footer_base; ?>auth/login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a></li>
                            <li><a href="<?php echo $footer_base; ?>auth/register.php"><i class="bi bi-person-plus me-2"></i>Register</a></li>
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
    <script src="<?php echo $footer_base; ?>assets/js/main.js"></script>
    <script>
    if (typeof window.showSupportUnderProcess === 'undefined') {
        window.showSupportUnderProcess = function(e) {
            if (e && e.preventDefault) e.preventDefault();
            var existing = document.getElementById('support-process-toast');
            if (existing) existing.remove();
            var toast = document.createElement('div');
            toast.id = 'support-process-toast';
            toast.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4 shadow-lg border-0';
            toast.style.zIndex = '999999';
            toast.style.minWidth = '360px';
            toast.style.borderRadius = '12px';
            toast.style.background = 'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)';
            toast.style.color = '#ffffff';
            toast.style.borderLeft = '5px solid #38bdf8';
            toast.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.35)';
            toast.innerHTML = '<div class="d-flex align-items-center py-1"><i class="bi bi-gear-wide-connected me-3 fs-3 text-info"></i><div class="pe-3"><strong class="d-block text-white mb-1" style="font-size: 1.05rem;">Support Centre</strong><span class="text-light" style="font-size: 0.92rem;">Support Centre is currently under process.</span></div><button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            document.body.appendChild(toast);
            setTimeout(function() {
                if (toast && toast.parentNode) {
                    toast.classList.remove('show');
                    setTimeout(function() { if (toast.parentNode) toast.remove(); }, 300);
                }
            }, 4500);
        };
    }
    </script>
</body>
</html>
