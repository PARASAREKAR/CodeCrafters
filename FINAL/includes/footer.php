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
    <!-- Footer -->
    <!-- ============================================================ -->
    <footer class="footer-custom text-center py-3 mt-auto"
            style="background-color: var(--bg-secondary); color: var(--text-muted); border-top: 1px solid var(--border);">
        <div class="container">
            <small>&copy; 2026 EventHub. All rights reserved.</small>
        </div>
    </footer>

    <!-- Bootstrap 5.3.2 JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous"></script>

    <!-- Custom JavaScript -->
    <script src="../assets/js/main.js"></script>
</body>
</html>
