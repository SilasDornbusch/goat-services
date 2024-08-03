<?php
require("connection.php");

function containsProfanity($text) {
    $badWords = ["bastard", "goatservices", "hure", "GOAT Services", "GOAT SERVICES", "nuttensohn", "hurensohn", "penis", "schwanz", "wixer", "nigger", "neger", "Nigga", "bitch", "gaylord", "gay", "homo", "schwul", "@everyone", "Fick deine Mutter", "@dev.silas", "@bl4ckye", "kacka", "popo", "pipi", "kackhaufen", "popoloch", "popolöchen", "knecht", "mutter", "vater", "wixfresse"];
    foreach ($badWords as $word) {
        if (stripos($text, $word) !== false) {
            return true;
        }
    }
    return false;
}

function containsSpecialCharactersInUsername($text) {
    return preg_match('/[^a-zA-Z0-9_.-]/', $text);
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function registerUser($username, $email, $password) {
    global $con;
    $stmt = $con->prepare("INSERT INTO users(username, email, password) VALUES(:username, :email, :password)");
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $password);
    $stmt->execute();
}

$alertMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    if (containsProfanity($username) || containsProfanity($email)) {
        $alertMessage = "Benutzername oder E-Mail enthält unerlaubte Wörter.";
    } elseif (containsSpecialCharactersInUsername($username)) {
        $alertMessage = "Benutzername enthält unerlaubte Sonderzeichen oder Emojis.";
    } elseif (!isValidEmail($email)) {
        $alertMessage = "E-Mail ist ungültig.";
    } else {
        $stmt = $con->prepare("SELECT * FROM users WHERE username=:username OR email=:email");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $userExists = $stmt->fetch();

        if (!$userExists) {
            registerUser($username, $email, $password);
            header("Location: chat.php");

            exit();
        } else {
            $alertMessage = "Benutzername oder E-Mail existiert bereits.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT Services | Registrieren</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="../assets/services.png" type="image/x-icon">

    <script type="text/javascript">
        function showAlert(message) {
            if (message) {
                alert(message);
            }
        }
    </script>
</head>
<body onload="showAlert('<?php echo $alertMessage; ?>')">
    <div class="form-container">
        <form action="index.php" method="POST">
            <h1 id="create_text">Account Erstellen</h1>
            <div class="inputs-container">
                <input type="text" placeholder="Benutzername" name="username" autocomplete="off" class="input-box-1" required>
                <input type="email" placeholder="Email" name="email" autocomplete="off" class="input-box-2" required>
                <input type="password" placeholder="Passwort" name="password" autocomplete="off" class="input-box-3" required>
                <button class="submit-btn" type="submit">Erstellen</button>
            </div>
            <a href="login.php">Bereits einen Account?</a>
            <?php if (!empty($alertMessage)): ?>
                <div class="error-message"><?php echo htmlspecialchars($alertMessage); ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
