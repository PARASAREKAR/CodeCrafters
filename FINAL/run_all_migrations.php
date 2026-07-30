<?php
$host = '127.0.0.1'; $db = 'event_registration_db'; $user = 'root'; $pass = 'CS@aids25';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Feature 3: Add Event_Fee column to events
    $cols = $pdo->query("SHOW COLUMNS FROM events LIKE 'Event_Fee'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE events ADD COLUMN Event_Fee DECIMAL(10,2) DEFAULT 0.00 AFTER Capacity");
        echo "✅ Added Event_Fee column to events table\n";
    } else { echo "⏭️  Event_Fee column already exists\n"; }

    // Feature 7: Add organizer_approved to registrations
    $cols2 = $pdo->query("SHOW COLUMNS FROM registrations LIKE 'organizer_approved'")->fetchAll();
    if (empty($cols2)) {
        $pdo->exec("ALTER TABLE registrations ADD COLUMN organizer_approved TINYINT(1) DEFAULT 0 AFTER Status");
        echo "✅ Added organizer_approved column to registrations table\n";
    } else { echo "⏭️  organizer_approved column already exists\n"; }

    // Feature 7: Create payments table
    $tables = $pdo->query("SHOW TABLES LIKE 'payments'")->fetchAll();
    if (empty($tables)) {
        $pdo->exec("CREATE TABLE payments (
            payment_id INT AUTO_INCREMENT PRIMARY KEY,
            registration_id INT NOT NULL,
            qr_token VARCHAR(64) NOT NULL UNIQUE,
            qr_viewed_count INT DEFAULT 0,
            amount DECIMAL(10,2) DEFAULT 0.00,
            status ENUM('Pending','Paid','Cancelled') DEFAULT 'Pending',
            paid_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (registration_id) REFERENCES registrations(Registration_ID) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "✅ Created payments table\n";
    } else { echo "⏭️  payments table already exists\n"; }

    echo "\nAll migrations completed successfully!\n";
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }
