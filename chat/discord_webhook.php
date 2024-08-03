<?php
// discord_webhook.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $webhookUrl = 'https://discord.com/api/webhooks/1268568000584220834/V1k0ojk6S4PA-UfXxUvyTttvdI28f7Fk72dwNsAsAKDGte3cA-WfkOh6Aw3uIpQ7ayVM';

    $message = $_POST['message'] ?? 'Keine Nachricht angegeben';

    $data = [
        'content' => '<@&1160097274165207080> Neue Nachricht: ' . $message
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($webhookUrl, false, $context);

    if ($result === FALSE) {
        echo json_encode(['status' => 'error', 'message' => 'Error sending notification.']);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Notification sent successfully.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
