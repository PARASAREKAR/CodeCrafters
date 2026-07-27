<?php
/**
 * logout.php – End the User Session
 * 
 * Clears all session data, destroys the session,
 * and redirects the user back to the login page.
 */

// 1. Start the session (required to access/destroy it)
session_start();

// 2. Unset all session variables
session_unset();

// 3. Destroy the session
session_destroy();

// 4. Redirect to the login page
header('Location: login.php');
exit();
