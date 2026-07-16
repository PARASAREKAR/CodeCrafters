<?php
/**
 * otp_verify.php – OTP Verification Page
 * 
 * Displays a styled 6-digit OTP verification screen inside a centered glass-card.
 * Focus shifts automatically to the next box upon typing a digit.
 * Features a simulated alert with the generated OTP code for easy testing.
 */

session_start();

require_once '../includes/helpers.php';

// ── Guard: Ensure user has authenticated with email/password first ──
if (empty($_SESSION['temp_user']) || empty($_SESSION['login_otp'])) {
    setFlashMessage('danger', 'Please log in to initiate OTP verification.');
    redirectTo('login.php');
}

$tempUser = $_SESSION['temp_user'];
$expiryTime = $_SESSION['login_otp_expiry'] ?? 0;
$timeLeft = max(0, $expiryTime - time());

// ── Handle Form Submissions (Verify or Resend) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Handle Resend Action
    if ($action === 'resend') {
        $newOtp = rand(100000, 999999);
        $_SESSION['login_otp']        = $newOtp;
        $_SESSION['login_otp_expiry'] = time() + 300; // Reset validity to 5 mins
        $_SESSION['otp_attempts']     = 0;            // Reset attempts

        setFlashMessage('success', 'A new verification code has been simulated.');
        redirectTo('otp_verify.php');
    }

    // Handle Verify Action
    if ($action === 'verify') {
        // Collect 6 digits
        $d1 = trim($_POST['d1'] ?? '');
        $d2 = trim($_POST['d2'] ?? '');
        $d3 = trim($_POST['d3'] ?? '');
        $d4 = trim($_POST['d4'] ?? '');
        $d5 = trim($_POST['d5'] ?? '');
        $d6 = trim($_POST['d6'] ?? '');
        $submittedOtp = $d1 . $d2 . $d3 . $d4 . $d5 . $d6;

        // Validation
        if (strlen($submittedOtp) !== 6 || !ctype_digit($submittedOtp)) {
            setFlashMessage('danger', 'Please enter a valid 6-digit verification code.');
            redirectTo('otp_verify.php');
        }

        // Check Expiration
        if (time() > $_SESSION['login_otp_expiry']) {
            setFlashMessage('danger', 'OTP has expired. Please request a new one.');
            redirectTo('otp_verify.php');
        }

        // Check Attempts
        $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;
        if ($_SESSION['otp_attempts'] > 3) {
            unset($_SESSION['temp_user'], $_SESSION['login_otp'], $_SESSION['login_otp_expiry'], $_SESSION['otp_attempts']);
            setFlashMessage('danger', 'Too many failed attempts. Please log in again.');
            redirectTo('login.php');
        }

        // Verify OTP
        if ((int)$submittedOtp === (int)$_SESSION['login_otp']) {
            // Success! Regenerate session id
            session_regenerate_id(true);

            // Log user in
            $_SESSION['user_id']    = $tempUser['User_ID'];
            $_SESSION['user_name']  = $tempUser['Name'];
            $_SESSION['user_email'] = $tempUser['Email'];
            $_SESSION['user_role']  = $tempUser['Role'];

            // Clear temp data
            unset($_SESSION['temp_user'], $_SESSION['login_otp'], $_SESSION['login_otp_expiry'], $_SESSION['otp_attempts']);

            setFlashMessage('success', 'Logged in successfully!');

            // Redirect based on role
            switch ($tempUser['Role']) {
                case 'Admin':
                    redirectTo('../admin/admin_dashboard.php');
                    break;
                case 'Organizer':
                    redirectTo('../organizer/organizer_dashboard.php');
                    break;
                case 'Participant':
                default:
                    redirectTo('../participant/participant_dashboard.php');
                    break;
            }
        } else {
            setFlashMessage('danger', 'Incorrect verification code. Please try again.');
            redirectTo('otp_verify.php');
        }
    }
}

$pageTitle = 'Verify OTP';
require_once '../includes/header.php';
?>

<!-- ====================================================================
     OTP VERIFICATION PAGE – Centered Card Layout
     ==================================================================== -->
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card card-custom glass-card shadow-lg text-center" style="max-width: 450px; width: 100%;">
        <div class="card-body p-4 p-md-5">

            <!-- Title -->
            <h3 class="mb-3" style="color: var(--text-primary);">
                <i class="bi bi-shield-lock-fill me-2" style="color: var(--accent);"></i>Security Verification
            </h3>
            <p class="text-muted small mb-4">
                We have generated a one-time verification code for your account. Please enter it below.
            </p>

            <!-- ── Simulated OTP Toast Alert ── -->
            <div class="alert alert-info border-0 shadow-sm p-3 mb-4 rounded-3 text-start d-flex align-items-center" role="alert">
                <div class="fs-4 me-3">🔒</div>
                <div>
                    <strong class="d-block text-uppercase small text-info" style="letter-spacing: 0.5px;">Simulated OTP Code</strong>
                    <span class="fs-5 fw-bold font-monospace text-primary" style="letter-spacing: 2px;">
                        <?php echo (int) $_SESSION['login_otp']; ?>
                    </span>
                </div>
            </div>

            <!-- ── Form ── -->
            <form action="otp_verify.php" method="POST">
                <input type="hidden" name="action" value="verify">

                <!-- OTP 6-Box Inputs -->
                <div class="d-flex justify-content-between gap-2 mb-4">
                    <input type="text" name="d1" class="form-control form-control-custom text-center fs-4 fw-bold otp-box" maxlength="1" required autocomplete="off" autofocus>
                    <input type="text" name="d2" class="form-control form-control-custom text-center fs-4 fw-bold otp-box" maxlength="1" required autocomplete="off">
                    <input type="text" name="d3" class="form-control form-control-custom text-center fs-4 fw-bold otp-box" maxlength="1" required autocomplete="off">
                    <input type="text" name="d4" class="form-control form-control-custom text-center fs-4 fw-bold otp-box" maxlength="1" required autocomplete="off">
                    <input type="text" name="d5" class="form-control form-control-custom text-center fs-4 fw-bold otp-box" maxlength="1" required autocomplete="off">
                    <input type="text" name="d6" class="form-control form-control-custom text-center fs-4 fw-bold otp-box" maxlength="1" required autocomplete="off">
                </div>

                <!-- Verify Button -->
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-accent btn-lg">
                        <i class="bi bi-shield-check me-1"></i>Verify & Login
                    </button>
                </div>
            </form>

            <!-- Resend Form -->
            <form action="otp_verify.php" method="POST" id="resendForm">
                <input type="hidden" name="action" value="resend">
                <p class="text-muted small mb-0">
                    Didn't receive the code?
                    <button type="submit" class="btn btn-link p-0 text-decoration-none fw-bold" id="resendBtn" style="color: var(--accent); font-size: 0.875rem;" disabled>
                        Resend Code (<span id="countdown">30</span>s)
                    </button>
                </p>
            </form>

        </div><!-- /.card-body -->
    </div><!-- /.card -->
</div><!-- /.container -->

<!-- ── Digit Shifting & Countdown Script ── -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const otpBoxes = document.querySelectorAll(".otp-box");

    // Handle shift focus on typing
    otpBoxes.forEach((box, index) => {
        // Shift focus to next input on digit entry
        box.addEventListener("input", (e) => {
            const val = e.target.value;
            if (val && /^[0-9]$/.test(val)) {
                if (index < otpBoxes.length - 1) {
                    otpBoxes[index + 1].focus();
                }
            } else {
                e.target.value = ""; // Clear invalid chars
            }
        });

        // Handle backspace focus shift
        box.addEventListener("keydown", (e) => {
            if (e.key === "Backspace") {
                if (!box.value && index > 0) {
                    otpBoxes[index - 1].focus();
                }
            }
        });

        // Paste functionality
        box.addEventListener("paste", (e) => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData("text").trim();
            if (text.length === 6 && /^\d+$/.test(text)) {
                otpBoxes.forEach((b, i) => {
                    b.value = text[i];
                });
                otpBoxes[5].focus();
            }
        });
    });

    // Countdown Timer for Resend
    let secondsLeft = 30;
    const resendBtn = document.getElementById("resendBtn");
    const countdown = document.getElementById("countdown");

    const timer = setInterval(() => {
        secondsLeft--;
        if (secondsLeft <= 0) {
            clearInterval(timer);
            resendBtn.removeAttribute("disabled");
            resendBtn.innerHTML = "Resend Code";
        } else {
            countdown.textContent = secondsLeft;
        }
    }, 1000);
});
</script>

<?php require_once '../includes/footer.php'; ?>
