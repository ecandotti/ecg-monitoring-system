<?php
/**
 * Cleanup expired remember tokens
 * 
 * This script should be run periodically (e.g., daily via cron) to remove
 * expired remember tokens from the database.
 */

// Set script execution time limit
set_time_limit(300); // 5 minutes

// Include required files
require_once __DIR__ . '/../config/database.php';

try {
    // Connect to database
    $db = getDbConnection();
    
    // Get current date/time
    $now = date('Y-m-d H:i:s');
    
    // Delete expired tokens
    $query = "DELETE FROM remember_tokens WHERE expires_at < :now";
    $stmt = $db->prepare($query);
    $stmt->execute(['now' => $now]);
    
    $count = $stmt->rowCount();
    echo "Successfully deleted $count expired remember tokens.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0); 