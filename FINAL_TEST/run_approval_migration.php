<?php
/**
 * Migration script for adding approval workflow columns.
 * Run once, then delete.
 */

require_once __DIR__ . '/config/db_connect.php';

try {
    // 1. Add Account_Status to users
    $stmt1 = $pdo->query("SHOW COLUMNS FROM users LIKE 'Account_Status'");
    if ($stmt1->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN Account_Status VARCHAR(20) NOT NULL DEFAULT 'Approved' AFTER Role");
        echo "✅ Column 'Account_Status' added to 'users' successfully!<br>";
    } else {
        echo "✅ Column 'Account_Status' already exists in 'users'.<br>";
    }

    // 2. Add Status to events
    $stmt2 = $pdo->query("SHOW COLUMNS FROM events LIKE 'Status'");
    if ($stmt2->rowCount() == 0) {
        $pdo->exec("ALTER TABLE events ADD COLUMN Status VARCHAR(20) NOT NULL DEFAULT 'Approved' AFTER Event_Category");
        echo "✅ Column 'Status' added to 'events' successfully!<br>";
    } else {
        echo "✅ Column 'Status' already exists in 'events'.<br>";
    }

    echo "<br><strong>Migration complete!</strong>";

} catch (PDOException $e) {
    echo "❌ Migration failed: " . htmlspecialchars($e->getMessage());
}
?>
