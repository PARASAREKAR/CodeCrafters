<?php
require_once '../includes/auth_check.php';
requireRole(['Organizer']);
require_once '../config/db_connect.php';

$user_id = $_SESSION['user_id'];
$type = $_GET['type'] ?? '';

if (!$type) {
    die("Report type not specified.");
}

$filename = "Organizer_Report_" . ucfirst($type) . "_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
// UTF-8 BOM
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

if ($type === 'event_wise') {
    fputcsv($output, ['#', 'Event Name', 'Date', 'Venue', 'Capacity', 'Confirmed', 'Pending', 'Cancelled', 'Total']);
    
    $stmt = $pdo->prepare(
        "SELECT e.Event_Name, e.Event_Date, e.Venue, e.Capacity,
                SUM(CASE WHEN r.Status = 'Confirmed' THEN 1 ELSE 0 END) AS confirmed,
                SUM(CASE WHEN r.Status = 'Pending'   THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN r.Status = 'Cancelled' THEN 1 ELSE 0 END) AS cancelled,
                COUNT(r.Registration_ID) AS total
         FROM events e
         LEFT JOIN registrations r ON e.Event_ID = r.Event_ID
         WHERE e.created_by = ?
         GROUP BY e.Event_ID
         ORDER BY e.Event_Date DESC"
    );
    $stmt->execute([$user_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($data as $i => $row) {
        fputcsv($output, [
            $i + 1,
            $row['Event_Name'],
            date('d M Y', strtotime($row['Event_Date'])),
            $row['Venue'],
            $row['Capacity'],
            $row['confirmed'],
            $row['pending'],
            $row['cancelled'],
            $row['total']
        ]);
    }

} elseif ($type === 'participant_details') {
    fputcsv($output, ['#', 'Event Name', 'Participant Name', 'Email', 'Mobile', 'College/Organization', 'Registration Date', 'Status']);
    
    $stmt = $pdo->prepare(
        "SELECT e.Event_Name, u.Name, u.Email, u.Mobile,
                r.College_Organization, r.Registration_Date, r.Status
         FROM registrations r
         JOIN users u  ON r.User_ID  = u.User_ID
         JOIN events e ON r.Event_ID = e.Event_ID
         WHERE e.created_by = ?
         ORDER BY e.Event_Name, r.Registration_Date DESC"
    );
    $stmt->execute([$user_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($data as $i => $row) {
        fputcsv($output, [
            $i + 1,
            $row['Event_Name'],
            $row['Name'],
            $row['Email'],
            $row['Mobile'] ?? 'N/A',
            $row['College_Organization'] ?? 'N/A',
            date('d M Y', strtotime($row['Registration_Date'])),
            $row['Status']
        ]);
    }

} elseif ($type === 'attendance') {
    fputcsv($output, ['#', 'Event Name', 'Participant Name', 'Email', 'Attendance Status', 'Marked At']);
    
    $stmt = $pdo->prepare(
        "SELECT e.Event_Name, u.Name, u.Email,
                a.Status AS Attendance_Status, a.marked_at AS Marked_At
         FROM attendance a
         JOIN registrations r ON a.Registration_ID = r.Registration_ID
         JOIN users u          ON r.User_ID  = u.User_ID
         JOIN events e         ON r.Event_ID = e.Event_ID
         WHERE e.created_by = ?
         ORDER BY e.Event_Name, u.Name"
    );
    $stmt->execute([$user_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($data as $i => $row) {
        fputcsv($output, [
            $i + 1,
            $row['Event_Name'],
            $row['Name'],
            $row['Email'],
            $row['Attendance_Status'],
            $row['Marked_At'] ? date('d M Y H:i', strtotime($row['Marked_At'])) : 'N/A'
        ]);
    }

} elseif ($type === 'cancelled_registrations') {
    fputcsv($output, ['#', 'Event Name', 'Participant Name', 'Email', 'Mobile', 'Registration Date']);
    
    $stmt = $pdo->prepare(
        "SELECT e.Event_Name, u.Name, u.Email, u.Mobile,
                r.Registration_Date
         FROM registrations r
         JOIN users u  ON r.User_ID  = u.User_ID
         JOIN events e ON r.Event_ID = e.Event_ID
         WHERE e.created_by = ? AND r.Status = 'Cancelled'
         ORDER BY r.Registration_Date DESC"
    );
    $stmt->execute([$user_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($data as $i => $row) {
        fputcsv($output, [
            $i + 1,
            $row['Event_Name'],
            $row['Name'],
            $row['Email'],
            $row['Mobile'] ?? 'N/A',
            date('d M Y', strtotime($row['Registration_Date']))
        ]);
    }
}

fclose($output);
exit;
