<?php
/**
 * Database Configuration
 * Configure your MySQL connection here
 */

// Database credentials
define('DB_HOST', 'localhost');      // XAMPP default host
define('DB_USER', 'root');           // XAMPP default user (no password)
define('DB_PASSWORD', '');           // XAMPP default (empty password)
define('DB_NAME', 'bctl_animal_db'); // Database name

// Create connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection error: ' . $e->getMessage()]));
}
?>
