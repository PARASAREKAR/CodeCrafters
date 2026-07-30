<?php
/**
 * Helper Functions
 * ----------------
 * Reusable utility functions used across the application:
 * - Input sanitization
 * - Flash messages (Bootstrap-styled)
 * - Redirect helper
 * - Authentication checks
 * - CSRF token management
 */

/**
 * Sanitize user input to prevent XSS attacks.
 *
 * @param  string $data  Raw user input
 * @return string        Sanitized string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Store a flash message in the session.
 * Flash messages are displayed once and then cleared.
 *
 * @param string $type    Bootstrap alert type (success, danger, warning, info)
 * @param string $message The message text to display
 */
function setFlashMessage($param1, $param2) {
    $knownTypes = ['success', 'danger', 'warning', 'info', 'error'];
    
    // Normalize 'error' to 'danger' for Bootstrap
    $p1_norm = ($param1 === 'error') ? 'danger' : $param1;
    $p2_norm = ($param2 === 'error') ? 'danger' : $param2;
    
    if (in_array($p1_norm, $knownTypes, true)) {
        $type = $p1_norm;
        $message = $param2;
    } elseif (in_array($p2_norm, $knownTypes, true)) {
        $type = $p2_norm;
        $message = $param1;
    } else {
        // Fallback
        $type = $p1_norm;
        $message = $param2;
    }

    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Retrieve and clear the flash message from session.
 * Returns a Bootstrap alert div if a message exists, empty string otherwise.
 *
 * @return string HTML alert div or empty string
 */
function getFlashMessage() {
    if (!isset($_SESSION['flash'])) {
        return '';
    }

    $flash   = $_SESSION['flash'];
    $type    = htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8');
    $message = $flash['message']; // Allow HTML like <br> for multi-line errors

    // Clear the flash message after retrieval
    unset($_SESSION['flash']);

    return '<div class="alert alert-' . $type . ' alert-dismissible fade show alert-custom" role="alert">'
         . $message
         . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
         . '</div>';
}

/**
 * Redirect to a given URL and terminate script execution.
 *
 * @param string $url The URL to redirect to
 */
function redirectTo($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if a user is currently logged in.
 *
 * @return bool True if user_id exists in session
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get the current user's role from the session.
 *
 * @return string|null The role string or null if not set
 */
function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Generate a CSRF token and store it in the session.
 * Returns the token for embedding in forms.
 *
 * @return string The generated CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a submitted CSRF token against the session token.
 * Clears the stored token after validation to prevent reuse.
 *
 * @param  string $token The token submitted via form
 * @return bool          True if token is valid
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    $valid = hash_equals($_SESSION['csrf_token'], $token);

    // Clear token after validation to prevent reuse
    unset($_SESSION['csrf_token']);

    return $valid;
}

/**
 * Output the flash message HTML directly.
 */
function displayFlashMessage() {
    echo getFlashMessage();
}

/**
 * Returns the image path for a given event category.
 * Falls back to placeholder if category image doesn't exist.
 *
 * @param string $category   Event category name
 * @param string $base       Base path prefix ('' for root, '../' for subdirs)
 * @return string            Relative image path
 */
function getCategoryImage(string $category, string $base = ''): string {
    $map = [
        'Tech'        => 'cat_tech.png',
        'Technology'  => 'cat_tech.png',
        'Business'    => 'cat_business.png',
        'Music'       => 'cat_music.png',
        'Art'         => 'cat_art.png',
        'Food'        => 'cat_food.png',
        'Sports'      => 'cat_sports.png',
        'Science'     => 'cat_science.png',
        'Health'      => 'cat_health.png',
        'Health & Wellness' => 'cat_health.png',
        'Creative'    => 'cat_creative.png',
        'Arts & Culture' => 'cat_art.png',
    ];
    $file = $map[$category] ?? null;
    if ($file) {
        return $base . 'assets/images/' . $file;
    }
    return $base . 'assets/images/placeholder-1.png';
}
