<?php
/**
 * reset_password.php
 * Allows the user to set a new password after OTP verification.
 */

session_start();

require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

// ============================================================
// Allow access only after successful OTP verification
// ============================================================
if (
    empty($_SESSION['reset_verified']) ||
    empty($_SESSION['reset_user'])
) {
    setFlashMessage(
        'danger',
        'Please verify your OTP first.'
    );

    redirectTo('forgot_password.php');
}
// ============================================================
// Handle Password Reset Form
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form data
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Check empty fields
    if (empty($newPassword) || empty($confirmPassword)) {

        setFlashMessage(
            'danger',
            'Please fill in all fields.'
        );

        redirectTo('reset_password.php');
    }

    // Check password match
    if ($newPassword !== $confirmPassword) {

        setFlashMessage(
            'danger',
            'Passwords do not match.'
        );

        redirectTo('reset_password.php');
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    try {

        // Update password in database
        $stmt = $pdo->prepare("
            UPDATE users
            SET Password = :password
            WHERE User_ID = :id
        ");

        $stmt->execute([
            ':password' => $hashedPassword,
            ':id'       => $_SESSION['reset_user']['User_ID']
        ]);

        // Clear reset session
        unset(
            $_SESSION['reset_verified'],
            $_SESSION['reset_user'],
            $_SESSION['reset_otp'],
            $_SESSION['reset_otp_expiry'],
            $_SESSION['reset_otp_attempts']
        );

        setFlashMessage(
            'success',
            'Password has been reset successfully. Please login.'
        );

        redirectTo('login.php');

    } catch (PDOException $e) {

        error_log("Password Reset Error: " . $e->getMessage());

        setFlashMessage(
            'danger',
            'Something went wrong. Please try again.'
        );

        redirectTo('reset_password.php');
    }
}

$pageTitle = 'Reset Password';
require_once '../includes/header.php';
?>

<!-- ============================================================
     RESET PASSWORD PAGE
============================================================ -->
<div class="container d-flex justify-content-center align-items-center" style="min-height:80vh;">

    <div class="card card-custom glass-card shadow-lg" style="max-width:500px; width:100%;">

        <div class="card-body p-4 p-md-5">

            <!-- Title -->
            <h3 class="text-center mb-3" style="color: var(--text-primary);">
                <i class="bi bi-key-fill me-2" style="color: var(--accent);"></i>
                Reset Password
            </h3>

            <p class="text-center text-muted mb-4">
                Enter your new password below.
            </p>

            <!-- Reset Password Form -->
            <form action="reset_password.php" method="POST">

                <!-- New Password -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-lock-fill me-1"></i>
                        New Password
                    </label>

                    <input
                        type="password"
                        name="new_password"
                        class="form-control form-control-custom"
                        placeholder="Enter new password"
                        required
                    >
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label class="form-label">
                        <i class="bi bi-shield-lock-fill me-1"></i>
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        class="form-control form-control-custom"
                        placeholder="Confirm new password"
                        required
                    >
                </div>

                <!-- Submit Button -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-accent btn-lg">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        Update Password
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>
<?php require_once '../includes/footer.php'; ?>