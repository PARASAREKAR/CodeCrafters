<?php
/**
 * register.php – New User Registration Page
 * 
 * Displays a styled registration form inside a centered glass-card.
 * Submits to auth_process.php with action=register.
 * Admins cannot self-register (role limited to Participant/Organizer).
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
$pageTitle = 'Register';

require_once '../includes/header.php';
?>

<!-- ====================================================================
     REGISTRATION PAGE – Centered Card Layout
     ==================================================================== -->
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card card-custom glass-card shadow-lg" style="max-width: 500px; width: 100%;">
        <div class="card-body p-4 p-md-5">

            <!-- ── Page Title ── -->
            <h3 class="text-center mb-4" style="color: var(--text-primary);">
                <i class="bi bi-person-plus-fill me-2" style="color: var(--accent);"></i>Create Account
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

            <!-- ── Registration Form ── -->
            <form action="auth_process.php" method="POST" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <!-- Action Identifier -->
                <input type="hidden" name="action" value="register">

                <!-- Full Name -->
                <div class="mb-3">
                    <label for="name" class="form-label" style="color: var(--text-secondary);">
                        <i class="bi bi-person me-1"></i>Full Name
                    </label>
                    <input type="text" class="form-control form-control-custom" id="name" name="name"
                           placeholder="Enter your full name" required
                           value="<?php echo htmlspecialchars($_SESSION['form_data']['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label" style="color: var(--text-secondary);">
                        <i class="bi bi-envelope me-1"></i>Email Address
                    </label>
                    <input type="email" class="form-control form-control-custom" id="email" name="email"
                           placeholder="you@example.com" required
                           value="<?php echo htmlspecialchars($_SESSION['form_data']['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <!-- Mobile -->
                <div class="mb-3">
                    <label for="mobile" class="form-label" style="color: var(--text-secondary);">
                        <i class="bi bi-phone me-1"></i>Mobile Number
                    </label>
                    <input type="tel" class="form-control form-control-custom" id="mobile" name="mobile"
                           placeholder="10–15 digit number" required
                           pattern="[0-9]{10,15}"
                           title="Enter a valid mobile number (10–15 digits)"
                           value="<?php echo htmlspecialchars($_SESSION['form_data']['mobile'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label" style="color: var(--text-secondary);">
                        <i class="bi bi-lock me-1"></i>Password
                    </label>
                    <div class="password-field-wrapper">
                        <input type="password" class="form-control form-control-custom" id="password" name="password"
                               placeholder="Min 12 chars – mix upper, lower, digits & symbols" required minlength="12"
                               autocomplete="new-password" style="padding-right: 2.8rem;">
                        <button type="button" class="password-toggle-btn" data-target="password" aria-label="Show password" tabindex="-1">
                            👁️
                        </button>
                    </div>

                    <!-- Strength Meter -->
                    <div class="password-strength-container" id="passwordStrengthContainer" style="display: none;">
                        <div class="password-strength-bar">
                            <div class="password-strength-fill" id="passwordStrengthFill"></div>
                        </div>
                        <div class="password-strength-label">
                            <span class="password-strength-text" id="passwordStrengthText"></span>
                            <span class="password-strength-score" id="passwordStrengthScore"></span>
                        </div>
                    </div>

                    <!-- Requirements Checklist -->
                    <div class="password-requirements" id="passwordRequirements">
                        <div class="password-requirements-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0-1A6 6 0 1 0 8 2a6 6 0 0 0 0 12zm-.5-9v1h1V5h-1zm.5 6.5a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5zM7.5 7v3h1V7h-1z"/></svg>
                            Password Requirements
                        </div>
                        <ul class="password-req-list">
                            <li class="password-req-item" data-req="length">
                                <span class="req-icon">✕</span>
                                <span>12+ characters</span>
                            </li>
                            <li class="password-req-item" data-req="uppercase">
                                <span class="req-icon">✕</span>
                                <span>Uppercase letter (A-Z)</span>
                            </li>
                            <li class="password-req-item" data-req="lowercase">
                                <span class="req-icon">✕</span>
                                <span>Lowercase letter (a-z)</span>
                            </li>
                            <li class="password-req-item" data-req="number">
                                <span class="req-icon">✕</span>
                                <span>Number (0-9)</span>
                            </li>
                            <li class="password-req-item" data-req="special">
                                <span class="req-icon">✕</span>
                                <span>Special char (!@#$…)</span>
                            </li>
                            <li class="password-req-item" data-req="no-spaces">
                                <span class="req-icon">✕</span>
                                <span>No leading/trailing spaces</span>
                            </li>
                            <li class="password-req-item" data-req="no-common">
                                <span class="req-icon">✕</span>
                                <span>Not a common password</span>
                            </li>
                            <li class="password-req-item" data-req="no-patterns">
                                <span class="req-icon">✕</span>
                                <span>No keyboard patterns</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Breach Warning (shown by JS if password is in common list) -->
                    <div class="password-breach-warning" id="passwordBreachWarning">
                        <div class="breach-text">
                            ⚠️ This password is commonly used and easily guessable. Please choose a stronger one.
                        </div>
                    </div>

                    <!-- Passphrase Tip -->
                    <div class="passphrase-tip" id="passphraseTip">
                        <div class="passphrase-tip-text">
                            💡 <strong>Pro tip:</strong> Use a passphrase — a memorable sequence of random words with numbers and symbols.
                            <br>
                            <span class="passphrase-example">Purple-Monkey-Carpet-88-Shoe!</span>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label for="confirm_password" class="form-label" style="color: var(--text-secondary);">
                        <i class="bi bi-lock-fill me-1"></i>Confirm Password
                    </label>
                    <div class="password-field-wrapper">
                        <input type="password" class="form-control form-control-custom" id="confirm_password" name="confirm_password"
                               placeholder="Re-enter your password" required minlength="12"
                               autocomplete="new-password" style="padding-right: 2.8rem;">
                        <button type="button" class="password-toggle-btn" data-target="confirm_password" aria-label="Show password" tabindex="-1">
                            👁️
                        </button>
                    </div>
                    <div class="invalid-feedback-custom" id="confirmPasswordFeedback"></div>
                </div>

                <!-- Role Selection -->
                <div class="mb-4">
                    <label for="role" class="form-label" style="color: var(--text-secondary);">
                        <i class="bi bi-shield-check me-1"></i>Register As
                    </label>
                    <select class="form-select form-control-custom" id="role" name="role" required>
                        <option value="Participant" <?php echo (($_SESSION['form_data']['role'] ?? '') === 'Participant' || empty($_SESSION['form_data']['role'])) ? 'selected' : ''; ?>>
                            Participant
                        </option>
                        <option value="Organizer" <?php echo (($_SESSION['form_data']['role'] ?? '') === 'Organizer') ? 'selected' : ''; ?>>
                            Organizer
                        </option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-accent btn-lg">
                        <i class="bi bi-person-plus me-1"></i>Create Account
                    </button>
                </div>
            </form>

            <?php
                // Clear preserved form data after rendering
                unset($_SESSION['form_data']);
            ?>

            <!-- ── Login Link ── -->
            <p class="text-center mt-4 mb-0" style="color: var(--text-secondary);">
                Already have an account?
                <a href="login.php" style="color: var(--accent); text-decoration: none; font-weight: 600;">Login here</a>
            </p>

        </div><!-- /.card-body -->
    </div><!-- /.card -->
</div><!-- /.container -->

<?php require_once '../includes/footer.php'; ?>
