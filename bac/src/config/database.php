<?php
// Database configuration
$host = 'localhost';  // Database host (usually localhost)
$username = 'root';   // Database username (change to your username)
$password = '';       // Database password (change to your password)
$dbname = 'request_db'; // Name of your database (change to your database name)

try {
    // Create connection
    $conn = new mysqli($host, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    // Set the charset to UTF-8 to avoid encoding issues
    $conn->set_charset('utf8');
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
