<?php
/**
 * ============================================================
 *  CANCEL REGISTRATION
 * ============================================================
 *  Action-only script (no HTML output). Receives a
 *  registration_id via GET, verifies ownership, updates the
 *  status to 'Cancelled', and redirects back to My Registrations.
 *
 *  All queries use PDO prepared statements.
 * ============================================================
 */

/* ── Bootstrap: auth, DB, helpers ─────────────────────────── */
require_once '../includes/auth_check.php';
requireRole(['Participant']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

/* ── Current user ID ──────────────────────────────────────── */
$user_id = $_SESSION['user_id'];

/* ── Read registration_id from query string ───────────────── */
$registration_id = (int) ($_GET['registration_id'] ?? 0);

if ($registration_id <= 0) {
    setFlashMessage('error', 'Invalid registration ID.');
    header('Location: my_registrations.php');
    exit;
}

/* ──────────────────────────────────────────────────────────
   Verify ownership – the registration must belong to the
   logged-in user. This prevents a participant from cancelling
   another user's registration by manipulating the URL.
   ────────────────────────────────────────────────────────── */
$stmt_check = $pdo->prepare(
    'SELECT Registration_ID, Status
       FROM registrations
      WHERE Registration_ID = ? AND User_ID = ?'
);
$stmt_check->execute([$registration_id, $user_id]);
$registration = $stmt_check->fetch(PDO::FETCH_ASSOC);

if (!$registration) {
    /* Registration not found or doesn't belong to this user */
    setFlashMessage('error', 'Registration not found or access denied.');
    header('Location: my_registrations.php');
    exit;
}

/* Optional: prevent cancelling an already-cancelled registration */
if ($registration['Status'] === 'Cancelled') {
    setFlashMessage('info', 'This registration has already been cancelled.');
    header('Location: my_registrations.php');
    exit;
}

/* ──────────────────────────────────────────────────────────
   Update status to 'Cancelled'
   ────────────────────────────────────────────────────────── */
$stmt_cancel = $pdo->prepare(
    "UPDATE registrations
        SET Status = 'Cancelled'
      WHERE Registration_ID = ? AND User_ID = ?"
);
$stmt_cancel->execute([$registration_id, $user_id]);

/* ── Flash success & redirect ─────────────────────────────── */
setFlashMessage('success', 'Your registration has been cancelled successfully.');
header('Location: my_registrations.php');
exit;
?>
