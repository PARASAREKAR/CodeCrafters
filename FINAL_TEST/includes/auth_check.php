<?php
/**
 * Authentication & Authorization Check
 * -------------------------------------
 * Include this file at the top of any protected page.
 * Starts the session and provides the requireRole() function
 * to enforce role-based access control.
 */

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include helper functions
require_once __DIR__ . '/helpers.php';

/**
 * Require the current user to have one of the allowed roles.
 * Redirects unauthenticated users to login page.
 * Redirects unauthorized users to their own dashboard.
 *
 * @param array $allowedRoles Array of role strings that can access the page
 */
function requireRole(array $allowedRoles) {
    // Check if user is logged in
    if (!isLoggedIn()) {
        setFlashMessage('warning', 'Please log in to access that page.');
        redirectTo('../auth/login.php');
    }

    // Check if user's role is in the allowed roles
    $userRole = getUserRole();
    if (!in_array($userRole, $allowedRoles)) {
        setFlashMessage('danger', 'You do not have permission to access that page.');

        // Redirect to the user's own dashboard based on their actual role
        switch ($userRole) {
            case 'Admin':
                redirectTo('../admin/admin_dashboard.php');
                break;
            case 'Organizer':
                redirectTo('../organizer/organizer_dashboard.php');
                break;
            case 'Participant':
                redirectTo('../participant/participant_dashboard.php');
                break;
            default:
                // Unknown role — send to login
                redirectTo('../auth/login.php');
                break;
        }
    }
}
