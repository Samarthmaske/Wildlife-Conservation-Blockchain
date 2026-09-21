<?php
/**
 * Database Configuration
 * Configure your MySQL connection here
 */

// Database credentials - Supports Railway Env Vars and Local XAMPP
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');      
define('DB_USER', getenv('MYSQLUSER') ?: 'root');           
define('DB_PASSWORD', getenv('MYSQLPASSWORD') ?: '');           
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'bctl_animal_db'); 
$port = getenv('MYSQLPORT') ?: 3306;

// Create connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, $port);
    
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
