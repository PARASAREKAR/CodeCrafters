<?php
/**
 * Database Connection Configuration
 * ----------------------------------
 * Establishes a PDO connection to the MySQL database.
 * Uses utf8mb4 charset for full Unicode support.
 * Sets exception mode for reliable error handling.
 */

// Database credentials
$db_host = 'localhost';
$db_name = 'event_registration_db';
$db_user = 'root';
$db_pass = 'CS@aids25';

// DSN with utf8mb4 charset
$dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";

// PDO connection options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Fetch associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                    // Use real prepared statements
];

try {
    // Try connecting directly to the database
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    
    // Fallback for default XAMPP (empty password) if access denied
    if (strpos($e->getMessage(), 'Access denied') !== false || $e->getCode() == 1045) {
        $db_pass = ''; // Try empty password
        $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        } catch (PDOException $fallback_e) {
            // Still failing or DB doesn't exist? Keep going to check if it's a missing DB error
            $e = $fallback_e; 
        }
    }

    // Error 1049 means 'Unknown database'
    if (isset($e) && ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false)) {
        try {
            // Connect to MySQL server without selecting a DB
            $dsn_no_db = "mysql:host={$db_host};charset=utf8mb4";
            $pdo = new PDO($dsn_no_db, $db_user, $db_pass, $options);
            
            // Create the database and select it
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $pdo->exec("USE `$db_name`");
            
            // Import schema
            $schemaFile = __DIR__ . '/../database/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                $pdo->exec($sql);
            } else {
                die("Database created, but schema.sql file is missing! Cannot create tables.");
            }
        } catch (PDOException $setup_e) {
            error_log("Database Auto-Setup Error: " . $setup_e->getMessage());
            die("<h1>Database Auto-Setup Failed</h1><p>We tried to create the database automatically but failed: " . htmlspecialchars($setup_e->getMessage()) . "</p>");
        }
    } elseif (isset($e) && $pdo === null) {
        // Log the error and show a user-friendly message
        error_log("Database Connection Error: " . $e->getMessage());
        die("<h1>Database Connection Failed</h1><p>Please check your MySQL username and password in config/db_connect.php.</p>");
    }
}
