<?php
// Include the database connection file
require("connection.php");

// Function to check if the text contains any profane words
function containsProfanity($text) {
    // List of profane words to check against
    $badWords = ["bastard", "goatservices", "hure", "GOAT Services", "GOAT SERVICES", "nuttensohn", "hurensohn", "penis", "schwanz", "wixer", "nigger", "neger", "Nigga", "bitch", "gaylord", "gay", "homo", "schwul", "@everyone", "Fick deine Mutter", "@dev.silas", "@bl4ckye", "kacka", "popo", "pipi", "kackhaufen", "popoloch", "popolöchen", "knecht", "mutter", "vater", "wixfresse"];
    foreach ($badWords as $word) {
        // Check if the text contains any bad words (case-insensitive)
        if (stripos($text, $word) !== false) {
            return true;
        }
    }
    return false;
}

// Function to check if the username contains special characters
function containsSpecialCharactersInUsername($text) {
    // Check if the username contains characters other than a-z, A-Z, 0-9, _, ., and -
    return preg_match('/[^a-zA-Z0-9_.-]/', $text);
}

// Function to validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Function to register a new user in the database
function registerUser($username, $email, $password) {
    global $con;
    // Prepare the SQL statement to insert user data
    $stmt = $con->prepare("INSERT INTO users(username, email, password) VALUES(:username, :email, :password)");
    // Bind the parameters
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $password);
    // Execute the statement
    $stmt->execute();
}

// Initialize an empty alert message
$alertMessage = "";

// Check if the form was submitted via POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $username = $_POST["username"];
    $email = $_POST["email"];
    // Hash the password for security
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Validate the form data
    if (containsProfanity($username) || containsProfanity($email)) {
        $alertMessage = "Benutzername oder E-Mail enthält unerlaubte Wörter.";
    } elseif (containsSpecialCharactersInUsername($username)) {
        $alertMessage = "Benutzername enthält unerlaubte Sonderzeichen oder Emojis.";
    } elseif (!isValidEmail($email)) {
        $alertMessage = "E-Mail ist ungültig.";
    } else {
        // Check if the username or email already exists in the database
        $stmt = $con->prepare("SELECT * FROM users WHERE username=:username OR email=:email");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        // Fetch the user data
        $userExists = $stmt->fetch();

        if (!$userExists) {
            // Register the new user
            registerUser($username, $email, $password);
            // Redirect to the homepage
            header("Location: homepage.php");

            // Function to send a notification to a Discord webhook
            function sendDiscordWebhook($webhookUrl, $message) {
                $data = json_encode(["content" => $message]);

                $ch = curl_init($webhookUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $response = curl_exec($ch);
                curl_close($ch);

                return $response;
            }

            // Webhook URL for Discord notifications
            $webhookUrl = "https://discord.com/api/webhooks/1242927287431860224/amgr4puPHkxyUR9IryZLcsCTvJg6uNTPlYgum5zHhUwRHTSrQv_hX3HIMbFZii-jsNl0";
            // Message to be sent to Discord
            $message = "Neuer Benutzer registriert: **$username** mit der E-Mail: **$email**";
            // Send the notification
            sendDiscordWebhook($webhookUrl, $message);

            // Exit to ensure no further code is executed
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
    <link rel="stylesheet" href="registerstyle.css">
    <link rel="shortcut icon" href="../assets/services.png" type="image/x-icon">

    <script type="text/javascript">
        // Function to show an alert with the given message
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
            <a href="login.php" id="register_text">Bereits einen Account?</a>
            <?php if (!empty($alertMessage)): ?>
                <div class="error-message"><?php echo htmlspecialchars($alertMessage); ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
