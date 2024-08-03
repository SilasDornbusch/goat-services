<?php
// Start the session to manage user authentication state
session_start();

// Include the configuration file
require_once('config.php');

// Check if the user is logged in by verifying the 'username' session variable
if (!isset($_SESSION['username'])) {
    // If the user is not logged in, redirect them to the login page (index.php)
    header("Location: index.php");
    exit(); // Exit to ensure no further code is executed
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT Services | Dashboard</title>
    <link rel="shortcut icon" href="../assets/services.png" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }
        .navbar-brand {
            font-size: 1.8rem;
            color: #343a40;
            text-decoration: none;
        }
        .navbar-nav {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .navbar-nav .nav-item {
            margin-left: 20px;
        }
        .nav-link {
            color: #343a40;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            text-decoration: underline;
        }
        .hamburger {
            display: flex;
            flex-direction: column;
            cursor: pointer;
        }
        .hamburger div {
            width: 25px;
            height: 2px;
            background-color: #343a40;
            margin: 4px 0;
            transition: all 0.3s ease;
        }
        .navbar-nav-mobile {
            display: none;
            flex-direction: column;
            width: 100%;
            position: absolute;
            top: 60px;
            left: 0;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar-nav-mobile.show {
            display: flex;
        }
        .navbar-nav-mobile .nav-item {
            margin: 10px 0;
        }
        h1 {
            text-align: center;
            color: #343a40;
            margin-top: 20px;
        }
        .container {
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #343a40;
            margin: 10px 0;
        }
        @media (max-width: 768px) {
            .navbar-nav {
                display: none;
            }
            .navbar {
                padding: 10px 15px;
            }
            .navbar-brand {
                font-size: 1.5rem;
            }
            .container {
                padding: 10px;
            }
        }
        @media (max-width: 480px) {
            .navbar {
                padding: 10px;
            }
            .navbar-brand {
                font-size: 1.2rem;
            }
            h1 {
                font-size: 1.5rem;
            }
            h2 {
                font-size: 1.2rem;
            }
        }
    </style>
    <script>
        // Function to toggle the mobile menu visibility
        function toggleMenu() {
            const navbarNavMobile = document.getElementById('navbarNavMobile');
            navbarNavMobile.classList.toggle('show');
        }
    </script>
</head>
<body>
<nav class="navbar">
    <a class="navbar-brand">
        <?php if(isset($_SESSION['is_developer']) && $_SESSION['is_developer']): ?>
            <div id="developerBadge" style="display: inline-block; background-image: url('../assets/dev-badge.png'); background-size: contain; background-repeat: no-repeat; padding-left: 50px; background-position: 10px center;">GOAT Services</div>
            <span style="vertical-align: middle;"> - Entwicklermodus</span>
        <?php else: ?>
            <div id="developerBadge">GOAT Services</div>
        <?php endif;?>
    </a>
    <div class="hamburger" onclick="toggleMenu()">
        <div></div>
        <div></div>
        <div></div>
    </div>
    <ul class="navbar-nav-mobile" id="navbarNavMobile">
        <li class="nav-item">
            <a class="nav-link" href="../index.html">Website</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="dcauth.php">Connect via Discord</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Settings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="videos.php">Videos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="upload.php">Upload Video</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="https://goat-services.de/accmanager">ACCMANAGER</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="logout.php">Logout</a>
        </li>
    </ul>
</nav>
<div class="container mt-4">
    <div class="main-content">
        <h1>Welcome to your Dashboard</h1>
    </div>
    <?php if(isset($_SESSION["username"])): ?>
        <h2>Benutzername: <?php echo htmlspecialchars($_SESSION["username"]); ?></h2>
    <?php else: ?>
        <h2>Benutzername: Unbekannt ??? WAS HAST DU GEMACHT XD</h2>
    <?php endif; ?>
    <?php if(isset($_SESSION["email"])): ?>
        <h2>Emailadresse: <?php echo htmlspecialchars($_SESSION["email"]); ?></h2>
    <?php else: ?>
        <h2>Emailadresse: Unbekannt</h2>
    <?php endif; ?>
    <?php if(isset($_SESSION["discord_username"])): ?>
        <h2>Discord-Benutzername: <?php echo htmlspecialchars($_SESSION["discord_username"]); ?></h2>
    <?php else: ?>
        <h2>Discord-Benutzername: Nicht verfügbar</h2>
    <?php endif; ?>
</div>
</body>
</html>
