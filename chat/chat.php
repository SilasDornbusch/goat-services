<?php
session_start();
date_default_timezone_set('Europe/Berlin');
require_once('connection.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    echo "Du musst angemeldet sein!";
    exit();
}

if (!isset($_SESSION['is_developer'])) {
    header("Location: login.php");
    echo "Aktuell können nur devs auf den chat zugreifen!";
    exit();
}

$chat_messages = [];
try {
    $stmt = $con->prepare("SELECT * FROM chat_messages");
    $stmt->execute();
    $chat_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error loading messages: ' . $e->getMessage();
}

function saveMessage($content, $sender, $con) {
    try {
        $stmt = $con->prepare("INSERT INTO chat_messages (content, sender, created_at) VALUES (:content, :sender, NOW())");
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':sender', $sender);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo 'Error saving message: ' . $e->getMessage();
        return false;
    }
}

function updateMessage($id, $content, $con) {
    try {
        $stmt = $con->prepare("UPDATE chat_messages SET content = :content, edited = 1 WHERE id = :id");
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo 'Error updating message: ' . $e->getMessage();
        return false;
    }
}

function deleteMessage($id, $con) {
    try {
        $stmt = $con->prepare("DELETE FROM chat_messages WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo 'Error deleting message: ' . $e->getMessage();
        return false;
    }
}

function displayMessages($chat_messages) {
    foreach ($chat_messages as $message) {
        $timestamp = new DateTime($message['created_at'], new DateTimeZone('UTC'));
        $timestamp->setTimezone(new DateTimeZone('Europe/Berlin'));
        echo '<div class="message-box" data-id="' . htmlspecialchars($message['id']) . '">';
        echo '<div class="message-content">' . htmlspecialchars($message['content']);
        if ($message['edited']) {
            echo ' <span class="edited">(bearbeitet)</span>';
        }
        echo '</div>';
        echo '<div class="message-sender">' . htmlspecialchars($message['sender']) . '</div>';
        echo '<div class="timestamp">' . htmlspecialchars($timestamp->format('d/m/Y H:i')) . '</div>';
        echo '<div class="message-actions">';
        echo '<button class="edit-btn btn btn-sm btn-warning">Bearbeiten</button>';
        echo '<button class="delete-btn btn btn-sm btn-danger">Löschen</button>';
        echo '</div>';
        echo '</div>';
    }
}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'send':
            $message = trim($_POST['message']);
            if (!empty($message)) {
                if (saveMessage($message, $_SESSION['username'], $con)) {
                    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error saving message.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Message is empty.']);
            }
            break;
        case 'edit':
            $id = $_POST['id'];
            $message = trim($_POST['message']);
            if (!empty($message)) {
                if (updateMessage($id, $message, $con)) {
                    echo json_encode(['status' => 'success', 'message' => 'Message updated successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error updating message.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Message is empty.']);
            }
            break;
        case 'delete':
            $id = $_POST['id'];
            if (deleteMessage($id, $con)) {
                echo json_encode(['status' => 'success', 'message' => 'Message deleted successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error deleting message.']);
            }
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat | GOAT Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <style>
        body {
    font-family: 'Arial', sans-serif;
    background-color: #2c3e50; 
    color: #fff; 

    -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
             user-select: none;

}

.container {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    height: 100vh;
}

.chat-container {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
    border-radius: 8px;
    background-color: #34495e;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.message-box {
    background-color: #2c3e50; 
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    position: relative;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.message-content {
    font-size: 16px;
    word-wrap: break-word;
    color: #fff;
}

.message-sender {
    font-size: 14px;
    font-style: italic;
    color: #2980b9; 
}

.timestamp {
    font-size: 12px;
    color: #95a5a6;
    margin-top: 5px;
}

.input-group {
    margin-top: 20px;
    display: flex;
}

#message {
    flex: 1;
    border-radius: 5px;
    background-color: #34495e; 
    color: #fff; 
    border: 1px solid #2c3e50;
    padding: 10px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#send-btn {
    border-radius: 5px;
    background-color: #3498db;
    border-color: #3498db;
    color: #fff;
}

#send-btn:hover {
    background-color: #2980b9;
    border-color: #2980b9;
}

#emoji-btn {
    background-color: #27ae60;
    color: #fff;
    border-radius: 5px;
}

#emoji-btn:hover {
    background-color: #2ecc71;
}

.emoji-picker {
    position: absolute;
    bottom: 80px;
    right: 10px;
    display: none;
    background-color: #34495e;
    border: 1px solid #2c3e50;
    border-radius: 8px;
    padding: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
}

.emoji-picker img {
    cursor: pointer;
    width: 40px;
    height: 40px;
    margin: 5px;
    border-radius: 50%;
    transition: transform 0.3s ease-in-out, filter 0.3s ease-in-out;
}

.emoji-picker img:hover {
    transform: scale(1.1);
    filter: brightness(120%);
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
}

.message-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: none;
}

.message-box:hover .message-actions {
    display: block;
}

.edited {
    font-size: 12px;
    color: #95a5a6;
}

.btn {
    color: #fff;
}

.btn-secondary {
    background-color: #7f8c8d; 
    border-color: #7f8c8d; 
}

.btn-primary {
    background-color: #3498db; 
    border-color: #3498db; 
}

.btn-warning {
    background-color: #f39c12;
    border-color: #f39c12; 
}

.btn-danger {
    background-color: #e74c3c; 
    border-color: #e74c3c; 
}

.btn-info {
    background-color: #1abc9c; 
    border-color: #1abc9c;
}

@media (max-width: 768px) {
    .container {
        margin-top: 10px;
    }

    .input-group {
        margin-top: 10px;
    }
}

    </style>
</head>
<body>
<div class="container">
    <div class="chat-container">
        <?php displayMessages($chat_messages); ?>
    </div>

    <div class="input-group">
        <textarea id="message" class="form-control" rows="2" placeholder="Type your message here..."></textarea>
        <div class="input-group-append">
            <button id="emoji-btn" class="btn btn-secondary"><i class="far fa-smile"></i></button>
            <button id="send-btn" class="btn btn-primary">Send</button>
        </div>
    </div>

    <div class="emoji-picker">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%8A" alt="😊" title="😊">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%83" alt="😃" title="😃">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%84" alt="😄" title="😄">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%86" alt="😆" title="😆">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%89" alt="😉" title="😉">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%8D" alt="😍" title="😍">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%9C" alt="😜" title="😜">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%9D" alt="😝" title="😝">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%B3" alt="😳" title="😳">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%B7" alt="😷" title="😷">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%82" alt="😂" title="😂">
        <img src="https://emojicdn.elk.sh/%F0%9F%91%8D" alt="👍" title="👍">
        <img src="https://emojicdn.elk.sh/%F0%9F%91%8E" alt="👎" title="👎">
        <img src="https://emojicdn.elk.sh/%F0%9F%91%8F" alt="👏" title="👏">
        <img src="https://emojicdn.elk.sh/%F0%9F%91%80" alt="👀" title="👀">
        <img src="https://emojicdn.elk.sh/%F0%9F%91%BE" alt="👾" title="👾">
        <img src="https://emojicdn.elk.sh/%E2%9C%8C" alt="✌" title="✌">
        <img src="https://emojicdn.elk.sh/%F0%9F%92%95" alt="💕" title="💕">
        <img src="https://emojicdn.elk.sh/%E2%9D%A4" alt="❤️" title="❤️">
        <img src="https://emojicdn.elk.sh/%F0%9F%92%94" alt="💔" title="💔">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%A0" alt="😠" title="😠">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%A1" alt="😡" title="😡">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%AC" alt="😬" title="😬">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%B1" alt="😱" title="😱">
        <img src="https://emojicdn.elk.sh/%F0%9F%98%88" alt="😈" title="😈">
        <img src="https://emojicdn.elk.sh/%F0%9F%91%BB" alt="👻" title="👻">
        <img src="https://emojicdn.elk.sh/%F0%9F%92%8B" alt="💋" title="💋">
        <img src="https://emojicdn.elk.sh/%F0%9F%91%BD" alt="👽" title="👽">
        <img src="https://emojicdn.elk.sh/%F0%9F%92%80" alt="💀" title="💀">
        <img src="https://emojicdn.elk.sh/%F0%9F%92%9B" alt="💛" title="💛">
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#send-btn').on('click', function (e) {
            e.preventDefault();
            sendMessage();
        });

        $('#message').on('keypress', function (e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        $('#emoji-btn').on('click', function () {
            $('.emoji-picker').toggle();
        });

        $('.emoji-picker img').on('click', function () {
            var message = $('#message').val();
            var emoji = $(this).attr('alt');
            $('#message').val(message + ' ' + emoji);
            $('#message').focus();
        });

        function sendMessage() {
    var message = $('#message').val().trim();
    if (message !== '') {
        var chatContainer = $('.chat-container');
        var messageBox = $('<div>').addClass('message-box');
        messageBox.html('<div class="message-content">' + message + '</div>' +
            '<div class="message-sender"><?php echo htmlspecialchars($_SESSION["username"]); ?></div>' +
            '<div class="timestamp">' + getCurrentTime() + '</div>');
        chatContainer.append(messageBox);
        $('#message').val('');

        chatContainer.scrollTop(chatContainer[0].scrollHeight);

        $.ajax({
            type: 'POST',
            url: 'chat.php',
            data: {action: 'send', message: message},
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    console.log(response.message);

                    // Notify Discord about the new message
                    $.ajax({
                        type: 'POST',
                        url: 'discord_webhook.php',
                        data: {message: 'New message from ' + '<?php echo htmlspecialchars($_SESSION["username"]); ?>' + ': ' + message},
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                console.log('Discord notification sent successfully.');
                            } else {
                                console.error(response.message);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX error: ' + error);
                        }
                    });
                } else {
                    console.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error: ' + error);
            }
        });
    }
}


        $(document).on('click', '.edit-btn', function () {
            var messageBox = $(this).closest('.message-box');
            var messageId = messageBox.data('id');
            var messageContent = messageBox.find('.message-content').text().trim();
            var newMessage = prompt('Edit your message:', messageContent);
            if (newMessage !== null && newMessage.trim() !== '') {
                messageBox.find('.message-content').text(newMessage + ' (edited)');
                $.ajax({
                    type: 'POST',
                    url: 'chat.php',
                    data: {action: 'edit', id: messageId, message: newMessage},
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            console.log(response.message);
                        } else {
                            console.error(response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX error: ' + error);
                    }
                });
            }
        });

        $(document).on('click', '.delete-btn', function () {
            if (confirm('Are you sure you want to delete this message?')) {
                var messageBox = $(this).closest('.message-box');
                var messageId = messageBox.data('id');
                messageBox.remove();
                $.ajax({
                    type: 'POST',
                    url: 'chat.php',
                    data: {action: 'delete', id: messageId},
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            console.log(response.message);
                        } else {
                            console.error(response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX error: ' + error);
                    }
                });
            }
        });


        function getCurrentTime() {
        var now = new Date();
        var options = {
        timeZone: 'Europe/Berlin',
        day: '2-digit',
        month: '2-digit',
        year: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    };
    var dateTime = now.toLocaleString('de-DE', options);
    var date = dateTime.split(', ')[0].replace(/\./g, '/');
    var time = dateTime.split(', ')[1];
    return date + ' ' + time;
}



console.log(getCurrentTime());

    });
</script>
</body>
</html>