<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

require_once '../src/PHPMailer.php';
require_once '../src/SMTP.php';
require_once '../src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ============================================================
// Allow only POST requests
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot_password.php');
    exit();
}

// ============================================================
// Get and validate email
// ============================================================
$email = sanitizeInput($_POST['email'] ?? '');

if (empty($email)) {
    setFlashMessage(
    'danger',
    'Please enter your registered email.'
);

redirectTo('forgot_password.php');
}

/* ⬇️ ISKE BILKUL NEECHE YE CODE PASTE KARO ⬇️ */

// ============================================================
// Check if the entered email exists in the database
// ============================================================
try {

    // Find user by email
    $stmt = $pdo->prepare("
        SELECT User_ID, Name, Email
        FROM users
        WHERE Email = :email
        LIMIT 1
    ");

    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log("Forgot Password Error: " . $e->getMessage());

    setFlashMessage(
        'danger',
        'Something went wrong. Please try again later.'
    );

    redirectTo('forgot_password.php');
}

// If email is not registered
if (!$user) {
    setFlashMessage(
    'warning',
    'No account found with this email address.'
);

redirectTo('forgot_password.php');
}

// ============================================================
// Generate Password Reset OTP
// ============================================================

// Generate a random 6-digit OTP
$otp = rand(100000, 999999);

// Store required information in session
$_SESSION['reset_user'] = [
    'User_ID' => $user['User_ID'],
    'Name'    => $user['Name'],
    'Email'   => $user['Email']
];

// Store OTP details
$_SESSION['reset_otp'] = $otp;
$_SESSION['reset_otp_expiry'] = time() + 300;   // Valid for 5 minutes
$_SESSION['reset_otp_attempts'] = 0;
// ============================================================
// Send Password Reset OTP to User's Email
// ============================================================

$mail = new PHPMailer(true);

try {

    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'eventoraganizers2026@gmail.com';
    $mail->Password   = 'gdtfdzdcubqpenyq';   // Replace with your Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender & Receiver
    $mail->setFrom('eventoraganizers2026@gmail.com', 'Event Registration System');
    $mail->addAddress($user['Email'], $user['Name']);

    // Email Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset OTP';

    $mail->Body = "
        <h2>Password Reset Request</h2>

        <p>Hello <b>{$user['Name']}</b>,</p>

        <p>Your password reset OTP is:</p>

        <h1 style='color:#0d6efd;'>$otp</h1>

        <p>This OTP is valid for <b>5 minutes</b>.</p>

        <p>If you did not request a password reset, please ignore this email.</p>
    ";

    $mail->send();

} catch (Exception $e) {

    error_log('Forgot Password Mail Error: ' . $mail->ErrorInfo);

    setFlashMessage(
        'danger',
        'Failed to send OTP. Please try again later.'
    );

    redirectTo('forgot_password.php');
   
}

// ============================================================
// Redirect to OTP Verification Page
// ============================================================

setFlashMessage(
    'success',
    'OTP has been sent to your registered email.'
);

redirectTo('forgot_password_otp.php');