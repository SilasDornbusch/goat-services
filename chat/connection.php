<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dsn = 'mysql:dbname=userdb;host=localhost';
$username = 'gsuser';
$password = 'GSidBSdWDB2024_!';

try {
    $con = new PDO($dsn, $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    #    echo "Verbindung zur Datenbank erfolgreich.";
} catch (PDOException $e) {
    echo 'Verbindungsfehler: ' . $e->getMessage();
}
?>
