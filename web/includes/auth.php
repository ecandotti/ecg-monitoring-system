<?php
/**
 * Authentication handler
 * 
 * This file should be included at the beginning of each page that requires authentication.
 * It handles session management and auto-login with remember tokens.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include security functions
require_once __DIR__ . '/../config/security.php';

// Try auto-login with remember token if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    checkRememberToken();
}

/**
 * Check if user is logged in
 *
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if user has a specific role
 *
 * @param string $role Role to check for
 * @return bool True if user has the role, false otherwise
 */
function hasRole($role) {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Require authentication to access the page
 * Redirects to login page if not authenticated
 *
 * @param string $role Optional role requirement
 * @return void
 */
function requireAuth($role = null) {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Authentication required to access this page.';
        header('Location: /public/login.php');
        exit;
    }
    
    if ($role !== null && !hasRole($role)) {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        header('Location: /public/index.php');
        exit;
    }
} 