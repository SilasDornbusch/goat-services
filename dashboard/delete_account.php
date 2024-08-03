<?php
// Start the session to manage user authentication state
session_start();

// Include the database connection file
require_once 'connection.php';

// Check if the request method is POST and the action is 'delete_account'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_account') {
    // Check if the user is logged in by verifying the 'username' session variable
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username']; // Get the username from the session

        // Prepare the SQL statement to delete the user from the database
        $sql = "DELETE FROM users WHERE username = ?";
        $stmt = $con->prepare($sql);
        $stmt->execute([$username]); // Execute the prepared statement with the username

        // Unset all session variables
        session_unset();
        // Destroy the session
        session_destroy();

        // Redirect the user to the login page after account deletion
        header("Location: login.php");
        exit(); // Exit to ensure the redirect happens immediately
    } else {
        // If the user is not logged in, display an error message
        echo "Benutzer nicht angemeldet";
    }
} else {
    // If the request is not valid, display an error message
    echo "Ungültige Anfrage";
}
?>
