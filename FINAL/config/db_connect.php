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
$db_pass = '';

// DSN with utf8mb4 charset
$dsn = "mysql:host={$db_host};port=3307;dbname={$db_name};charset=utf8mb4";

// PDO connection options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Fetch associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                    // Use real prepared statements
];

try {
    // Create PDO instance
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // Log the error and show a user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
