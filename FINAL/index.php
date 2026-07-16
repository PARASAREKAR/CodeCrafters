<?php
/**
 * Application Entry Point
 * -----------------------
 * Routes users based on their authentication status and role:
 * - Logged in  → Redirect to role-specific dashboard
 * - Not logged → Redirect to login page
 */

session_start();
require_once __DIR__ . '/includes/helpers.php';

// If user is logged in, send them to their dashboard
if (isLoggedIn()) {
    $role = getUserRole();

    switch ($role) {
        case 'Admin':
            redirectTo('admin/admin_dashboard.php');
            break;
        case 'Organizer':
            redirectTo('organizer/organizer_dashboard.php');
            break;
        case 'Participant':
            redirectTo('participant/participant_dashboard.php');
            break;
        default:
            // Unknown role — clear session and go to login
            session_destroy();
            redirectTo('auth/login.php');
            break;
    }
}

// Not logged in — redirect to login page
redirectTo('auth/login.php');
