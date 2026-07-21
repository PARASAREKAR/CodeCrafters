<?php
session_start();
require_once '../includes/helpers.php';

$pageTitle = 'Forgot Password';
require_once '../includes/header.php';
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height:80vh;">
    <div class="card card-custom glass-card shadow-lg" style="max-width:450px;width:100%;">
        <div class="card-body p-4">

            <h3 class="text-center mb-4">
                <i class="bi bi-key-fill me-2"></i>Forgot Password
            </h3>

            <p class="text-center text-muted mb-4">
                Enter your registered email address to receive a password reset OTP.
            </p>

            <form action="forgot_password_process.php" method="POST">

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control form-control-custom"
                        placeholder="Enter your registered email"
                        required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-accent btn-lg">
                        Send OTP
                    </button>
                </div>

            </form>

            <div class="text-center mt-3">
                <a href="login.php">← Back to Login</a>
            </div>

        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>