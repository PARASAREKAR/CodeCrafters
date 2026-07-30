<?php
/**
 * Export Participants to CSV
 * 
 * Streams a CSV file of all participants registered for a
 * given event.  Sets appropriate HTTP headers and uses
 * fputcsv() for clean output.  No HTML is rendered.
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

// ── Verify event ownership ─────────────────────────────────────
$stmtEvent = $pdo->prepare("SELECT Event_Name FROM events WHERE Event_ID = ? AND created_by = ?");
$stmtEvent->execute([$event_id, $user_id]);
$event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    setFlashMessage('Event not found or access denied.', 'danger');
    header('Location: organizer_dashboard.php');
    exit;
}

// ── Fetch participants ─────────────────────────────────────────
$stmtParts = $pdo->prepare(
    "SELECT u.Name, u.Email, u.Mobile, r.College_Organization,
            r.Registration_Date, r.Status
     FROM registrations r
     JOIN users u ON r.User_ID = u.User_ID
     WHERE r.Event_ID = ?
     ORDER BY r.Registration_Date ASC"
);
$stmtParts->execute([$event_id]);
$participants = $stmtParts->fetchAll(PDO::FETCH_ASSOC);

// ── Build a safe filename from the event name ──────────────────
$safeEventName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $event['Event_Name']);
$filename      = "Participants_{$safeEventName}_" . date('Ymd') . ".csv";

// ── Set CSV download headers ───────────────────────────────────
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// ── Write CSV to output stream ─────────────────────────────────
$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Header row
fputcsv($output, ['Name', 'Email', 'Mobile', 'College/Organization', 'Registration Date', 'Status']);

// Data rows
foreach ($participants as $p) {
    fputcsv($output, [
        $p['Name'],
        $p['Email'],
        $p['Mobile']               ?? 'N/A',
        $p['College_Organization'] ?? 'N/A',
        date('d M Y', strtotime($p['Registration_Date'])),
        $p['Status']
    ]);
}

fclose($output);
exit;
