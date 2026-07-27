<?php
/**
 * Accept Registration & Send Payment QR Email
 * Organizer clicks Accept → payment record created → email with QR link sent to participant
 */
require_once '../includes/auth_check.php';
requireRole(['Organizer']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once '../src/Exception.php';
require_once '../src/PHPMailer.php';
require_once '../src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_participants.php');
    exit;
}

if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('Invalid CSRF token.', 'danger');
    header('Location: manage_participants.php');
    exit;
}

$reg_id  = (int) ($_POST['registration_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($reg_id < 1) {
    setFlashMessage('Invalid registration.', 'danger');
    header('Location: manage_participants.php');
    exit;
}

// Fetch registration with event and participant details
$stmt = $pdo->prepare(
    "SELECT r.*, u.Name AS participant_name, u.Email AS participant_email,
            e.Event_Name, e.Event_Fee, e.Organizer AS org_name,
            e.Event_Date, e.Venue, e.created_by
     FROM registrations r
     JOIN users u  ON r.User_ID  = u.User_ID
     JOIN events e ON r.Event_ID = e.Event_ID
     WHERE r.Registration_ID = ?"
);
$stmt->execute([$reg_id]);
$reg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reg || $reg['created_by'] != $user_id) {
    setFlashMessage('Registration not found or access denied.', 'danger');
    header('Location: manage_participants.php');
    exit;
}

// Mark organizer approved
$pdo->prepare("UPDATE registrations SET organizer_approved = 1, Status = 'Confirmed' WHERE Registration_ID = ?")
    ->execute([$reg_id]);

// Check if payment record already exists
$exists = $pdo->prepare("SELECT payment_id, qr_token FROM payments WHERE registration_id = ?");
$exists->execute([$reg_id]);
$payment = $exists->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    $token = bin2hex(random_bytes(32));
    $pdo->prepare("INSERT INTO payments (registration_id, qr_token, amount, status) VALUES (?, ?, ?, 'Pending')")
        ->execute([$reg_id, $token, $reg['Event_Fee']]);
} else {
    $token = $payment['qr_token'];
}

// Build QR link
$protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'];
$base_path = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])));
$qr_url    = $protocol . '://' . $host . $base_path . '/participant/view_qr.php?token=' . urlencode($token);

$fee_text = ($reg['Event_Fee'] > 0) ? '₹' . number_format($reg['Event_Fee'], 2) : 'FREE';

// Send email to participant
try {
    // ── Email 1: Confirmation Email ────────────────────────
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'eventoraganizers2026@gmail.com';
    $mail->Password   = 'gdtfdzdcubqpenyq';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('eventoraganizers2026@gmail.com', 'EventHub — ' . htmlspecialchars($reg['org_name']));
    $mail->addAddress($reg['participant_email'], $reg['participant_name']);
    $mail->isHTML(true);
    $mail->Subject = '✅ Registration Confirmed | ' . $reg['Event_Name'];
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; background: #f9f9f9; border-radius: 12px; overflow: hidden;'>
        <div style='background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 30px; text-align: center;'>
            <h1 style='color: #fff; margin: 0; font-size: 24px;'>🎉 Registration Confirmed!</h1>
        </div>
        <div style='padding: 30px; background: #fff;'>
            <p style='font-size: 16px; color: #333;'>Dear <strong>{$reg['participant_name']}</strong>,</p>
            <p style='color: #555;'>Your registration for <strong>{$reg['Event_Name']}</strong> has been <span style='color: #22c55e; font-weight: bold;'>accepted and confirmed by the organizer</span>.</p>
            <div style='background: #f0fdf4; border-left: 4px solid #10b981; padding: 16px; border-radius: 8px; margin: 20px 0;'>
                <p style='margin: 4px 0; color: #333;'><strong>📅 Event Date:</strong> " . date('d M Y', strtotime($reg['Event_Date'])) . "</p>
                <p style='margin: 4px 0; color: #333;'><strong>📍 Venue:</strong> {$reg['Venue']}</p>
                <p style='margin: 4px 0; color: #333;'><strong>💰 Registration Fee:</strong> {$fee_text}</p>
            </div>
            <hr style='border: 0; border-top: 1px solid #eee; margin: 24px 0;'>
            <p style='color: #888; font-size: 13px; text-align: center;'>EventHub — Powered by your Organizer Team</p>
        </div>
    </div>";
    
    $mail->send();

    // ── Email 2: Payment Email (Only if event is Paid) ──────
    if ($reg['Event_Fee'] > 0) {
        $mail->clearAllRecipients();
        $mail->addAddress($reg['participant_email'], $reg['participant_name']);
        $mail->Subject = '💳 Payment Required | ' . $reg['Event_Name'];
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; background: #f9f9f9; border-radius: 12px; overflow: hidden;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
                <h1 style='color: #fff; margin: 0; font-size: 24px;'>💳 Action Required: Event Payment</h1>
            </div>
            <div style='padding: 30px; background: #fff;'>
                <p style='font-size: 16px; color: #333;'>Dear <strong>{$reg['participant_name']}</strong>,</p>
                <p style='color: #555;'>To complete your registration for <strong>{$reg['Event_Name']}</strong>, please pay the registration fee.</p>
                
                <p style='color: #333; margin-top: 20px;'>Please scan the payment QR code using the secure link below to make your payment of <strong>{$fee_text}</strong>.</p>
                <div style='text-align: center; margin: 24px 0;'>
                    <a href='{$qr_url}' style='background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-size: 16px; font-weight: bold; display: inline-block;'>
                        🔐 View Payment QR Code
                    </a>
                </div>
                <p style='color: #e55; font-size: 13px; text-align: center;'>⚠️ This link can only be opened <strong>2 times</strong> for security. Please scan and pay immediately.</p>
                <hr style='border: 0; border-top: 1px solid #eee; margin: 24px 0;'>
                <p style='color: #888; font-size: 13px; text-align: center;'>EventHub — Powered by your Organizer Team</p>
            </div>
        </div>";
        $mail->send();
    }
    
    setFlashMessage('Registration accepted! Confirmation emails sent to ' . htmlspecialchars($reg['participant_email']), 'success');
} catch (Exception $e) {
    setFlashMessage('Registration accepted but email failed: ' . $mail->ErrorInfo, 'warning');
}

$redirect = $_SERVER['HTTP_REFERER'] ?? 'organizer_dashboard.php';
header("Location: $redirect");
exit;
