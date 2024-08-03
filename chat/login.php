<?php
session_start();
require 'connection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$errorMessage = '';

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $con->prepare('SELECT username, email, password, is_developer FROM users WHERE username=:username OR email=:email');
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['is_developer'] = $user['is_developer'];

                if (isset($_POST['remember_me'])) {
                    $token = bin2hex(random_bytes(16));
                    $expiryDate = date('Y-m-d H:i:s', strtotime('+365 days'));
                    $stmt = $con->prepare('UPDATE users SET remember_token = :token, token_expiry = :expiry WHERE username = :username');
                    $stmt->bindParam(':token', $token);
                    $stmt->bindParam(':expiry', $expiryDate);
                    $stmt->bindParam(':username', $user['username']);
                    $stmt->execute();
                    setcookie('remember_me', $token, [
                        'expires' => strtotime('+30 days'),
                        'path' => '/',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);
                }
                
                header('Location: chat.php');
                exit;
            } else {
                $errorMessage = 'Login fehlgeschlagen, Passwort stimmt nicht!';
            }
        } else {
            $errorMessage = 'Login fehlgeschlagen, Benutzer nicht gefunden!';
        }
    } catch (PDOException $e) {
        echo 'Fehler: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MESSAGING APP | Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="../assets/services.png" type="image/x-icon">
</head>
<body>
    <div class="form-container">
        <form action="login.php" method="POST">
            <h1 id="register_text">Login</h1>
            <div class="inputs-container">
                <input type="text" placeholder="Benutzername oder E-Mail" name="username" autocomplete="off" class="input-box-1" required>
                <input type="password" placeholder="Passwort" name="password" autocomplete="off" class="input-box-2" required>
                <label><input type="checkbox" name="remember_me"> Remember Me</label>
                <button class="submit-btn" type="submit" name="submit">Login</button>
            </div>
            <a href="index.php">Account erstellen!</a>
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
