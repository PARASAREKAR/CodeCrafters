<?php
/**
 * login.php – User Login Page
 * 
 * Displays a styled login form inside a centered glass-card.
 * Submits to auth_process.php with action=login.
 */

session_start();

// ── If already logged in, redirect to the appropriate dashboard ──
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 'Admin':
            header('Location: ../admin/admin_dashboard.php');
            break;
        case 'Organizer':
            header('Location: ../organizer/organizer_dashboard.php');
            break;
        default:
            header('Location: ../participant/participant_dashboard.php');
            break;
    }
    exit();
}

// ── Generate CSRF token if not present ──
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Page title for header include ──
$pageTitle = 'Login';

require_once '../includes/header.php';
?>

<!-- ====================================================================
     LOGIN PAGE – Centered Card Layout
     ==================================================================== -->
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card card-custom glass-card shadow-lg" style="max-width: 450px; width: 100%;">
        <div class="card-body p-4 p-md-5">

            <!-- ── Page Title ── -->
            <h3 class="text-center mb-4" style="color: var(--text-primary);">
                <i class="bi bi-box-arrow-in-right me-2" style="color: var(--accent);"></i>Welcome Back
            </h3>

            <!-- ── Flash Messages ── -->
            <?php if (!empty($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?php echo htmlspecialchars($_SESSION['flash_type'] ?? 'info', ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
                    <?php
                        echo htmlspecialchars($_SESSION['flash_message'], ENT_QUOTES, 'UTF-8');
                        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- ── Login Form ── -->
            <form action="auth_process.php" method="POST" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <!-- Action Identifier -->
                <input type="hidden" name="action" value="login">

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label" style="color: var(--text-secondary);">
                        <i class="bi bi-envelope me-1"></i>Email Address
                    </label>
                    <input type="email" class="form-control form-control-custom" id="email" name="email"
                           placeholder="you@example.com" required autofocus>
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="form-label" style="color: var(--text-secondary);">
                        <i class="bi bi-lock me-1"></i>Password
                    </label>
                    <input type="password" class="form-control form-control-custom" id="password" name="password"
                           placeholder="Enter your password" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-accent btn-lg" id="loginBtn">
                        <i class="bi bi-box-arrow-in-right me-1" id="loginIcon"></i><span id="loginText">Sign In</span>
                    </button>
                </div>
            </form>
            
            <script>
            document.querySelector('form').addEventListener('submit', function() {
                const btn = document.getElementById('loginBtn');
                const icon = document.getElementById('loginIcon');
                const text = document.getElementById('loginText');
                
                if (btn && icon && text) {
                    btn.classList.add('disabled');
                    icon.className = 'spinner-border spinner-border-sm me-2';
                    text.innerText = 'Sending OTP...';
                }
            });
            </script>

            <!-- Forgot Password Link -->
            <p class="text-center mt-3 mb-2">
                <a href="forgot_password.php"
                   style="color: var(--accent); text-decoration: none; font-weight: 600;">
                    Forgot Password?
                </a>
            </p>

            <!-- ── Register Link ── -->
            <p class="text-center mt-4 mb-0" style="color: var(--text-secondary);">
                Don't have an account?
                <a href="register.php" style="color: var(--accent); text-decoration: none; font-weight: 600;">Register here</a>
            </p>

        </div><!-- /.card-body -->
    </div><!-- /.card -->
</div><!-- /.container -->

<?php require_once '../includes/footer.php'; ?>
