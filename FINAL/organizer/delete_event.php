<?php
/**
 * Delete Event
 * 
 * Processes an event deletion request via GET parameter.
 * Ownership is verified before the DELETE is executed.
 * Database CASCADE constraints handle related registrations
 * and attendance records automatically.
 *
 * @requires auth_check.php  – session bootstrap & role guard
 * @requires db_connect.php  – PDO $pdo connection
 * @requires helpers.php     – flash(), etc.
 */

require_once '../includes/auth_check.php';
requireRole(['Organizer']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

$user_id  = $_SESSION['user_id'];
$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

// ── Validate event_id ──────────────────────────────────────────
if ($event_id <= 0) {
    setFlashMessage('Invalid event ID.', 'danger');
    header('Location: organizer_dashboard.php');
    exit;
}

// ── DELETE with ownership check ────────────────────────────────
// CASCADE foreign keys will automatically remove related
// registrations and attendance records.
$stmt = $pdo->prepare("DELETE FROM events WHERE Event_ID = ? AND created_by = ?");
$stmt->execute([$event_id, $user_id]);

if ($stmt->rowCount() > 0) {
    setFlashMessage('Event deleted successfully.', 'success');
} else {
    setFlashMessage('Event not found or you do not have permission to delete it.', 'danger');
}

header('Location: organizer_dashboard.php');
exit;
