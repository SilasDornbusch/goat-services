<?php
session_start(); // Starten der Session
// require 'connection.php'; // Einbinden der Datenbankverbindung

// Fehleranzeige aktivieren
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialisierung der Fehlermeldung
$errorMessage = '';

// Überprüfen, ob das Formular abgeschickt wurde
if (isset($_POST['submit'])) {
    // Auslesen der Benutzereingaben
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Vorbereitung der SQL-Abfrage zum Abrufen des Benutzers
        $stmt = $con->prepare('SELECT username, email, password, is_developer FROM users WHERE username=:username OR email=:email');
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Überprüfen, ob der Benutzer gefunden wurde
        if ($user) {
            // Überprüfen, ob das eingegebene Passwort korrekt ist
            if (password_verify($password, $user['password'])) {
                // Benutzerinformationen in der Session speichern
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['is_developer'] = $user['is_developer'];
                // Weiterleitung zur Startseite
                header('Location: homepage.php');
                exit;
            } else {
                $errorMessage = 'Login fehlgeschlagen, Passwort stimmt nicht!';
            }
        } else {
            $errorMessage = 'Login fehlgeschlagen, Benutzer nicht gefunden!';
        }
    } catch (PDOException $e) {
        // Fehlerbehandlung bei Datenbankfehlern
        echo 'Fehler: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT Services | Login</title>
    <link rel="stylesheet" href="loginstyle.css">
    <link rel="shortcut icon" href="../assets/services.png" type="image/x-icon">
</head>
<body>
    <div class="form-container">
        <form action="login.php" method="POST">
            <h1 id="register_text">Login</h1>
            <div class="inputs-container">
                <!-- Eingabefeld für Benutzername oder E-Mail -->
                <input type="text" placeholder="Benutzername oder E-Mail" name="username" autocomplete="off" class="input-box-1" required>
                <!-- Eingabefeld für Passwort -->
                <input type="password" placeholder="Passwort" name="password" autocomplete="off" class="input-box-2" required>
                <!-- Login-Button -->
                <button class="submit-btn" type="submit" name="submit">Login</button>
            </div>
            <!-- Link zum Erstellen eines neuen Accounts -->
            <a href="index.php">Account erstellen!</a>
            <!-- Anzeige der Fehlermeldung, falls vorhanden -->
            <!-- <?php if (!empty($errorMessage)): ?>
                <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>
            -->
        </form>
    </div>
</body>
</html>

