<?php
// Display all PHP errors (useful for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection parameters
$dsn = 'mysql:dbname=userdb;host=localhost'; // Data Source Name specifying the database name and host
$username = 'gsuser';                        // Database username
$password = 'GSidBSdWDB2024_!';              // Database password

try {
    // Create a new PDO instance to connect to the database
    $con = new PDO($dsn, $username, $password);
    
    // Set error mode to exception to handle errors properly
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Uncomment the next line to confirm a successful connection (for debugging purposes)
    // echo "Verbindung zur Datenbank erfolgreich.";
} catch (PDOException $e) {
    // Handle connection errors
    echo 'Verbindungsfehler: ' . $e->getMessage();
}
?>
