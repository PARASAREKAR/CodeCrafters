<?php
/**
 * Test Data Seed Script: Inserts 25 Participants for testing.
 */
require_once __DIR__ . '/config/db_connect.php';

try {
    $password_hash = password_hash('password123', PASSWORD_BCRYPT);
    $first_names = ['John', 'Jane', 'Alex', 'Emily', 'Michael', 'Sarah', 'David', 'Jessica', 'James', 'Emily', 'Daniel', 'Olivia', 'Matthew', 'Sophia', 'Robert', 'Isabella', 'William', 'Mia', 'Joseph', 'Charlotte', 'Andrew', 'Amelia', 'Ryan', 'Evelyn', 'Chris'];
    $last_names = ['Smith', 'Doe', 'Johnson', 'Williams', 'Brown', 'Jones', 'Miller', 'Davis', 'Garcia', 'Rodriguez', 'Wilson', 'Martinez', 'Anderson', 'Taylor', 'Thomas', 'Hernandez', 'Moore', 'Martin', 'Jackson', 'Thompson', 'White', 'Lopez', 'Lee', 'Gonzalez', 'Harris'];

    $inserted = 0;
    $stmt = $pdo->prepare("INSERT INTO users (Name, Email, Mobile, Password, Role) VALUES (?, ?, ?, ?, 'Participant')");

    for ($i = 1; $i <= 25; $i++) {
        $first = $first_names[$i - 1] ?? 'Participant';
        $last = $last_names[$i - 1] ?? $i;
        $name = "$first $last";
        $email = "participant{$i}@eventhub.com";
        $mobile = "98765432" . str_pad($i, 2, '0', STR_PAD_LEFT);

        // Check if email already exists to avoid unique constraint violations
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE Email = ?");
        $check->execute([$email]);
        if ($check->fetchColumn() > 0) {
            continue; // Skip if already exists
        }

        $stmt->execute([$name, $email, $mobile, $password_hash]);
        $inserted++;
    }

    echo "✅ Successfully seeded $inserted new test participants!<br>";
    echo "Default Password for all seeded accounts is: <strong>password123</strong><br><br>";
    echo "<strong>Sample Accounts to Log In:</strong><br>";
    echo "- Email: <code>participant1@eventhub.com</code> / Password: <code>password123</code><br>";
    echo "- Email: <code>participant25@eventhub.com</code> / Password: <code>password123</code><br><br>";
    echo "<a href='index.php'>Go back to Landing Page</a>";

} catch (PDOException $e) {
    echo "❌ Seeding failed: " . htmlspecialchars($e->getMessage());
}
?>
