<?php
/**
 * Process Requests
 * ----------------
 * Backend script to handle approve/reject actions for Organizers and Events.
 */

session_start();

require_once '../includes/auth_check.php';
requireRole(['Admin']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_requests.php');
    exit;
}

// CSRF check
$token = $_POST['csrf_token'] ?? '';
if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    setFlashMessage('Invalid security token.', 'danger');
    header('Location: manage_requests.php');
    exit;
}

$type   = $_POST['type'] ?? '';
$action = $_POST['action'] ?? '';
$id     = (int)($_POST['id'] ?? 0);

if ($id <= 0 || !in_array($type, ['organizer', 'event']) || !in_array($action, ['approve', 'reject'])) {
    setFlashMessage('Invalid request parameters.', 'danger');
    header('Location: manage_requests.php');
    exit;
}

$status = ($action === 'approve') ? 'Approved' : 'Rejected';

try {
    if ($type === 'organizer') {
        $stmt = $pdo->prepare("UPDATE users SET Account_Status = ? WHERE User_ID = ? AND Role = 'Organizer'");
        $stmt->execute([$status, $id]);
        
        if ($stmt->rowCount() > 0) {
            setFlashMessage("Organizer successfully $status.", 'success');
        } else {
            setFlashMessage("Organizer not found or already processed.", 'warning');
        }
    } elseif ($type === 'event') {
        $stmt = $pdo->prepare("UPDATE events SET Status = ? WHERE Event_ID = ?");
        $stmt->execute([$status, $id]);
        
        if ($stmt->rowCount() > 0) {
            setFlashMessage("Event successfully $status.", 'success');
        } else {
            setFlashMessage("Event not found or already processed.", 'warning');
        }
    }
} catch (PDOException $e) {
    error_log('Error processing request: ' . $e->getMessage());
    setFlashMessage('A database error occurred. Please try again.', 'danger');
}

header('Location: manage_requests.php');
exit;
