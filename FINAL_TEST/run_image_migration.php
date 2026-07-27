<?php
/**
 * Migration Runner: Add Image_Path column to events table.
 */
require_once __DIR__ . '/config/db_connect.php';

try {
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM events LIKE 'Image_Path'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Column 'Image_Path' already exists. No action needed.\n";
        exit;
    }

    // Add the column
    $pdo->exec("ALTER TABLE events ADD COLUMN Image_Path VARCHAR(255) DEFAULT NULL AFTER Event_Category");
    echo "✅ Column 'Image_Path' added successfully!\n";

} catch (PDOException $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
}
?>
