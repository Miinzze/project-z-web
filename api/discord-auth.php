<?php
require_once __DIR__.'/includes/config.php';

$clientId = '1241093263872626708';
$clientSecret = 'J3eGqwKgybUlDvzoFFaIjv8LXjuFlYvN';
$redirectUri = 'https://project-z-rp.de/discord-auth';

if (!isset($_GET['code'])) {
    // Weiterleitung zu Discord Login
    $authUrl = "https://discord.com/api/oauth2/authorize?client_id=$clientId&redirect_uri=".urlencode($redirectUri)."&response_type=code&scope=identify";
    header("Location: $authUrl");
    exit;
} else {
    // Token austauschen
    $code = $_GET['code'];
    $tokenUrl = "https://discord.com/api/oauth2/token";
    
    $data = [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => $redirectUri,
        'scope' => 'identify'
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($tokenUrl, false, $context);
    $token = json_decode($result, true);
    
    // Benutzerdaten abrufen
    $userUrl = "https://discord.com/api/users/@me";
    $options = [
        'http' => [
            'header' => "Authorization: Bearer {$token['access_token']}\r\n"
        ]
    ];
    
    $context = stream_context_create($options);
    $user = json_decode(file_get_contents($userUrl, false, $context), true);
    
    $_SESSION['discord_user'] = $user;
    header('Location: /whitelist');
    exit;
}