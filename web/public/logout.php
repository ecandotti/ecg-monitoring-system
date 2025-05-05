<?php
// Logout script
session_start();
require_once '../config/database.php';
require_once '../config/security.php';

// Get user ID before clearing session
$user_id = $_SESSION['user_id'] ?? null;

// Clear session variables
$_SESSION = array();

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Handle remember token cleanup
if (isset($_COOKIE['remember_token'])) {
    // Delete cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    
    // If we have the user ID, delete all remember tokens for this user from the database
    if ($user_id) {
        try {
            $db = getDbConnection();
            $query = "DELETE FROM remember_tokens WHERE user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->execute(['user_id' => $user_id]);
        } catch (Exception $e) {
            // Silent fail - we're logging out anyway
        }
    }
}

// Destroy session
session_destroy();

// Redirect to home page
header('Location: /public/index.php');
exit; 