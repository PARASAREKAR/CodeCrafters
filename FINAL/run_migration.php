<?php
/**
 * Quick migration script — run once, then delete.
 * Adds Event_Category column to the events table.
 */

require_once __DIR__ . '/config/db_connect.php';

try {
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM events LIKE 'Event_Category'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Column 'Event_Category' already exists. No action needed.";
        exit;
    }

    // Add the column
    $pdo->exec("ALTER TABLE events ADD COLUMN Event_Category VARCHAR(50) NOT NULL DEFAULT 'General' AFTER Capacity");
    echo "✅ Column 'Event_Category' added successfully!<br>";

    // Add index
    $pdo->exec("CREATE INDEX idx_event_category ON events(Event_Category)");
    echo "✅ Index 'idx_event_category' created successfully!<br>";

    echo "<br><strong>Migration complete!</strong> You can now <a href='index.php'>go to the landing page</a>.";
    echo "<br><br><em>⚠️ Delete this file (run_migration.php) after running it.</em>";

} catch (PDOException $e) {
    echo "❌ Migration failed: " . htmlspecialchars($e->getMessage());
}
?>
