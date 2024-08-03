<?php
// Start the session to manage user authentication state
session_start();

// Include the configuration file which contains constants like CLIENT_ID and REDIRECT_URI
require_once('config.php');

// Check if the 'username' session variable is set, meaning the user is logged in
if (!isset($_SESSION['username'])) {
    // If the user is not logged in, redirect them to the login page (index.php)
    header("Location: index.php");
    exit(); // Exit to ensure no further code is executed
}

// Define the scope of permissions to request from Discord
$scope = "identify"; // Initial scope can be expanded as needed
$auth_url = "https://discord.com/oauth2/authorize?client_id=" . CLIENT_ID . "&response_type=code&redirect_uri=" . urlencode(REDIRECT_URI) . "&scope=identify+email+connections+guilds+guilds.join+gdm.join";

// Redirect the user to Discord's OAuth2 authorization page
header("Location: $auth_url");
exit(); // Exit to ensure the redirect happens immediately
?>
