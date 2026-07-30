<?php
require_once '../includes/auth_check.php';
requireRole(['Admin']);
require_once '../config/db_connect.php';

$type = $_GET['type'] ?? '';

if (!$type) {
    die("Report type not specified.");
}

$filename = "Admin_Report_" . ucfirst($type) . "_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
// UTF-8 BOM
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

if ($type === 'event_wise') {
    fputcsv($output, ['#', 'Event Name', 'Venue', 'Date', 'Capacity', 'Registered', 'Available']);
    
    $stmt = $pdo->query(
        "SELECT e.Event_Name, e.Venue, e.Event_Date, e.Capacity, COUNT(r.Registration_ID) AS reg_count
         FROM events e
         LEFT JOIN registrations r ON e.Event_ID = r.Event_ID AND r.Status = 'Confirmed'
         GROUP BY e.Event_ID
         ORDER BY e.Event_Date DESC"
    );
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($data as $i => $row) {
        $capacity = (int)$row['Capacity'];
        $registered = (int)$row['reg_count'];
        $available = max(0, $capacity - $registered);
        fputcsv($output, [
            $i + 1,
            $row['Event_Name'],
            $row['Venue'] ?? '—',
            date('d M Y', strtotime($row['Event_Date'])),
            $capacity,
            $registered,
            $available
        ]);
    }

} elseif ($type === 'participant_details') {
    fputcsv($output, ['#', 'Participant Name', 'Email', 'Mobile', 'Event', 'Registration Date', 'Status']);
    
    $stmt = $pdo->query(
        "SELECT u.Name, u.Email, u.Mobile, e.Event_Name, r.Registration_Date, r.Status
         FROM registrations r
         JOIN users u ON r.User_ID = u.User_ID
         JOIN events e ON r.Event_ID = e.Event_ID
         WHERE u.Role = 'Participant'
         ORDER BY r.Registration_Date DESC"
    );
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($data as $i => $row) {
        fputcsv($output, [
            $i + 1,
            $row['Name'],
            $row['Email'],
            $row['Mobile'] ?? 'N/A',
            $row['Event_Name'],
            date('d M Y', strtotime($row['Registration_Date'])),
            $row['Status']
        ]);
    }

} elseif ($type === 'attendance') {
    fputcsv($output, ['#', 'Event Name', 'Participant Name', 'Registration Status', 'Attendance Status']);
    
    $stmt = $pdo->query(
        "SELECT e.Event_Name, u.Name, r.Status as reg_status, a.Status as attendance_status
         FROM registrations r
         JOIN events e ON r.Event_ID = e.Event_ID
         JOIN users u ON r.User_ID = u.User_ID
         LEFT JOIN attendance a ON r.Registration_ID = a.Registration_ID
         ORDER BY e.Event_Name, u.Name"
    );
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($data as $i => $row) {
        fputcsv($output, [
            $i + 1,
            $row['Event_Name'],
            $row['Name'],
            $row['reg_status'],
            $row['attendance_status'] ?? 'Not Marked'
        ]);
    }

} elseif ($type === 'cancelled_registrations') {
    fputcsv($output, ['#', 'Participant Name', 'Email', 'Event Name', 'Registration Date', 'Cancellation Date']);
    
    $stmt = $pdo->query(
        "SELECT u.Name, u.Email, e.Event_Name, r.Registration_Date, r.created_at
         FROM registrations r
         JOIN users u ON r.User_ID = u.User_ID
         JOIN events e ON r.Event_ID = e.Event_ID
         WHERE r.Status = 'Cancelled'
         ORDER BY r.created_at DESC"
    );
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($data as $i => $row) {
        fputcsv($output, [
            $i + 1,
            $row['Name'],
            $row['Email'],
            $row['Event_Name'],
            date('d M Y', strtotime($row['Registration_Date'])),
            date('d M Y H:i', strtotime($row['created_at']))
        ]);
    }

} elseif ($type === 'payments') {
    fputcsv($output, ['#', 'Participant Name', 'Email', 'Event Name', 'Amount', 'Pay Status', 'QR Views', 'Paid At', 'QR Token']);
    
    $stmt = $pdo->query(
        "SELECT p.payment_id, p.qr_token, p.qr_viewed_count, p.amount, p.status AS pay_status,
                p.paid_at, p.created_at AS qr_sent_at,
                u.Name AS participant_name, u.Email AS participant_email,
                e.Event_Name
         FROM payments p
         JOIN registrations r ON p.registration_id = r.Registration_ID
         JOIN users u          ON r.User_ID  = u.User_ID
         JOIN events e         ON r.Event_ID = e.Event_ID
         ORDER BY p.created_at DESC"
    );
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($data as $i => $row) {
        fputcsv($output, [
            $i + 1,
            $row['participant_name'],
            $row['participant_email'],
            $row['Event_Name'],
            $row['amount'],
            $row['pay_status'],
            $row['qr_viewed_count'],
            $row['paid_at'] ? date('d M Y H:i', strtotime($row['paid_at'])) : '—',
            $row['qr_token']
        ]);
    }
}

fclose($output);
exit;
