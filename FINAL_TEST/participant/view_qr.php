<?php
/**
 * View Payment QR Code (Participant)
 * Token-based, limited to 2 views. After payment, participant confirms.
 */
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$token = trim($_GET['token'] ?? '');
if (empty($token)) { die('<h2>Invalid or expired link.</h2>'); }

// Fetch payment record
$stmt = $pdo->prepare(
    "SELECT p.*, r.Registration_ID, r.Status AS reg_status,
            u.Name AS participant_name, u.Email AS participant_email,
            e.Event_Name, e.Event_Date, e.Venue, e.Event_Fee
     FROM payments p
     JOIN registrations r ON p.registration_id = r.Registration_ID
     JOIN users u ON r.User_ID = u.User_ID
     JOIN events e ON r.Event_ID = e.Event_ID
     WHERE p.qr_token = ?"
);
$stmt->execute([$token]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) { die('<h2 style="text-align:center;color:#e55;margin-top:80px;">❌ Invalid or expired payment link.</h2>'); }
if ($payment['qr_viewed_count'] >= 2 && $payment['status'] !== 'Paid') {
    die('<div style="text-align:center;margin-top:80px;font-family:sans-serif;"><h2 style="color:#e55;">⛔ This QR link has expired.</h2><p>This link can only be opened 2 times for security. Please contact the organizer.</p></div>');
}

// Increment view count
$pdo->prepare("UPDATE payments SET qr_viewed_count = qr_viewed_count + 1 WHERE qr_token = ?")
    ->execute([$token]);

// Handle "I have paid" submission
$paid_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $pdo->prepare("UPDATE payments SET status = 'Paid', paid_at = NOW() WHERE qr_token = ?")
        ->execute([$token]);
    $paid_success = true;
    $payment['status'] = 'Paid';
}

$fee_display = ($payment['Event_Fee'] > 0) ? '₹' . number_format($payment['Event_Fee'], 2) : 'FREE';
$views_left  = max(0, 2 - ($payment['qr_viewed_count']));
?>
<!DOCTYPE html>
<html lang="en" data-theme="midnight-dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment QR | EventHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #0f0f1a; color: #e2e8f0; font-family: 'Segoe UI', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .qr-card { background: #1a1a2e; border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; padding: 40px; max-width: 480px; width: 100%; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
        .qr-img { width: 280px; height: 280px; border-radius: 16px; border: 4px solid #667eea; object-fit: contain; background: #fff; padding: 8px; }
        .badge-views { background: rgba(255,100,100,0.15); color: #f87171; border: 1px solid rgba(255,100,100,0.3); padding: 6px 14px; border-radius: 20px; font-size: 13px; }
        .btn-paid { background: linear-gradient(135deg, #22c55e, #16a34a); border: none; color: #fff; padding: 14px 40px; border-radius: 12px; font-size: 16px; font-weight: 700; cursor: pointer; width: 100%; }
        .paid-banner { background: rgba(34,197,94,0.1); border: 2px solid #22c55e; border-radius: 16px; padding: 20px; }
        .event-info { background: rgba(255,255,255,0.03); border-radius: 12px; padding: 16px; text-align: left; margin: 20px 0; }
    </style>
</head>
<body>
<div class="qr-card">
    <div class="mb-3">
        <span style="font-size: 2.5rem;">🔐</span>
        <h2 class="mt-2 fw-bold" style="color:#a78bfa;">Payment QR Code</h2>
        <p class="text-muted">Scan the QR code below to complete your payment</p>
    </div>

    <?php if ($payment['status'] === 'Paid'): ?>
        <div class="paid-banner mb-4">
            <i class="bi bi-patch-check-fill text-success" style="font-size:3rem;"></i>
            <h4 class="mt-2 text-success fw-bold">Payment Confirmed! ✅</h4>
            <p class="text-muted mb-0">Your registration is fully confirmed. See you at the event!</p>
        </div>
    <?php else: ?>
        <div class="event-info">
            <p class="mb-1"><i class="bi bi-calendar-event me-2 text-primary"></i><strong><?php echo htmlspecialchars($payment['Event_Name']); ?></strong></p>
            <p class="mb-1"><i class="bi bi-calendar3 me-2 text-muted"></i><?php echo date('d M Y', strtotime($payment['Event_Date'])); ?></p>
            <p class="mb-1"><i class="bi bi-geo-alt me-2 text-muted"></i><?php echo htmlspecialchars($payment['Venue']); ?></p>
            <p class="mb-0"><i class="bi bi-currency-rupee me-2 text-warning"></i><strong style="color:#fbbf24;"><?php echo $fee_display; ?></strong></p>
        </div>

        <img src="<?php echo '../assets/images/qr_code.jpeg'; ?>" alt="Payment QR Code" class="qr-img mb-3">

        <div class="mb-3">
            <span class="badge-views"><i class="bi bi-eye me-1"></i><?php echo $views_left; ?> view(s) remaining</span>
        </div>

        <?php if (!$paid_success): ?>
            <p class="text-muted small mb-3">After scanning and completing payment, click the button below:</p>
            <form method="POST">
                <button type="submit" name="confirm_payment" class="btn-paid">
                    <i class="bi bi-check-circle-fill me-2"></i>I Have Paid — Confirm Payment
                </button>
            </form>
        <?php else: ?>
            <div class="paid-banner mt-3">
                <i class="bi bi-patch-check-fill text-success" style="font-size:2rem;"></i>
                <p class="text-success fw-bold mt-2 mb-0">Payment confirmation recorded!</p>
                <p class="text-muted small">The organizer and admin have been notified.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
