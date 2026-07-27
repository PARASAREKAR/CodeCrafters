<?php
/**
 * forgot_password_otp.php
 * Verify OTP before allowing password reset.
 */

session_start();

require_once '../includes/helpers.php';


require_once '../src/PHPMailer.php';
require_once '../src/SMTP.php';
require_once '../src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// ============================================================
// Check whether user has requested password reset
// ============================================================
if (
    empty($_SESSION['reset_user']) ||
    empty($_SESSION['reset_otp'])
) {
    setFlashMessage(
        'danger',
        'Please request a password reset first.'
    );

    redirectTo('forgot_password.php');
}

// Logged-in reset user
$resetUser = $_SESSION['reset_user'];

// OTP expiry time
$expiryTime = $_SESSION['reset_otp_expiry'] ?? 0;
$timeLeft = max(0, $expiryTime - time());

// ============================================================
// Handle Form Submission
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
        // ========================================================
    // Resend OTP
    // ========================================================
    if ($action === 'resend') {

        // Generate a new OTP
        $newOtp = rand(100000, 999999);

        // Update session
        $_SESSION['reset_otp'] = $newOtp;
        $_SESSION['reset_otp_expiry'] = time() + 300;
        $_SESSION['reset_otp_attempts'] = 0;

        // Send OTP Email
        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'eventoraganizers2026@gmail.com';
            $mail->Password = 'gdtfdzdcubqpenyq';

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom(
                'eventoraganizers2026@gmail.com',
                'Event Registration System'
            );

            $mail->addAddress(
                $resetUser['Email'],
                $resetUser['Name']
            );

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';

            $mail->Body = "
                <h2>Password Reset OTP</h2>

                <p>Hello <b>{$resetUser['Name']}</b>,</p>

                <p>Your new OTP is:</p>

                <h1 style='color:#0d6efd;'>$newOtp</h1>

                <p>This OTP is valid for 5 minutes.</p>
            ";

            $mail->send();

        } catch (Exception $e) {

            error_log("Resend OTP Error: " . $mail->ErrorInfo);

            setFlashMessage(
                'danger',
                'Failed to resend OTP.'
            );

            redirectTo('forgot_password_otp.php');
        }

        setFlashMessage(
            'success',
            'A new OTP has been sent to your registered email.'
        );

        redirectTo('forgot_password_otp.php');
    }

 
    // ========================================================
    // Verify OTP
    // ========================================================
    if ($action === 'verify') {

        // Collect all 6 OTP digits
        $d1 = trim($_POST['d1'] ?? '');
        $d2 = trim($_POST['d2'] ?? '');
        $d3 = trim($_POST['d3'] ?? '');
        $d4 = trim($_POST['d4'] ?? '');
        $d5 = trim($_POST['d5'] ?? '');
        $d6 = trim($_POST['d6'] ?? '');

        $submittedOtp = $d1 . $d2 . $d3 . $d4 . $d5 . $d6;

        // Validate OTP format
        if (strlen($submittedOtp) !== 6 || !ctype_digit($submittedOtp)) {
            setFlashMessage('danger', 'Please enter a valid 6-digit OTP.');
            redirectTo('forgot_password_otp.php');
        }

        // Check OTP expiry
        if (time() > $_SESSION['reset_otp_expiry']) {
            setFlashMessage('danger', 'OTP has expired.');
            redirectTo('forgot_password.php');
        }

        // Count verification attempts
        $_SESSION['reset_otp_attempts'] =
            ($_SESSION['reset_otp_attempts'] ?? 0) + 1;

        if ($_SESSION['reset_otp_attempts'] > 3) {

            unset(
                $_SESSION['reset_user'],
                $_SESSION['reset_otp'],
                $_SESSION['reset_otp_expiry'],
                $_SESSION['reset_otp_attempts']
            );

            setFlashMessage(
                'danger',
                'Too many incorrect attempts. Please request a new OTP.'
            );

            redirectTo('forgot_password.php');
        }

        // Verify OTP
        if ((int)$submittedOtp === (int)$_SESSION['reset_otp']) {

            $_SESSION['reset_verified'] = true;

            redirectTo('reset_password.php');

        } else {

            setFlashMessage(
                'danger',
                'Incorrect OTP. Please try again.'
            );

            redirectTo('forgot_password_otp.php');
        }
    }
}
$pageTitle = 'Verify Reset OTP';
require_once '../includes/header.php';
?>

<!-- HTML -->
 <!-- ====================================================================
     OTP VERIFICATION PAGE – Centered Card Layout
     ==================================================================== -->
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card card-custom glass-card shadow-lg text-center" style="max-width: 450px; width: 100%;">
        <div class="card-body p-4 p-md-5">

            <!-- Title -->
            <h3 class="mb-3" style="color: var(--text-primary);">
                <i class="bi bi-shield-lock-fill me-2" style="color: var(--accent);"></i>Password Reset Verification
            </h3>
            <p class="text-muted small mb-4">
                We have sent a password reset OTP to your registered email. Please enter it below.
            </p>

            <!-- ── Simulated OTP Toast Alert ── 
            <div class="alert alert-info border-0 shadow-sm p-3 mb-4 rounded-3 text-start d-flex align-items-center" role="alert">
                <div class="fs-4 me-3">🔒</div>
                <div>
                    <strong class="d-block text-uppercase small text-info" style="letter-spacing: 0.5px;">Simulated OTP Code</strong>
                    <span class="fs-5 fw-bold font-monospace text-primary" style="letter-spacing: 2px;">
                       <?php echo (int) $_SESSION['login_otp']; ?> 
                    </span>
                </div>
            </div>
                -->

            <!-- ── Form ── -->
            <form action="forgot_password_otp.php" method="POST">

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
                        <i class="bi bi-shield-check me-1"></i>Verify OTP
                    </button>
                </div>
            </form>

            <!-- Resend Form -->
            <form action="forgot_password_otp.php" method="POST" id="resendForm">
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