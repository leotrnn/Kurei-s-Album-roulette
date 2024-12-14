<?php

// Fonction pour récupérer le token d'accès
function getSpotifyAccessToken($clientId, $clientSecret) {
    $url = "https://accounts.spotify.com/api/token";
    $headers = [
        "Authorization: Basic " . base64_encode($clientId . ":" . $clientSecret),
        "Content-Type: application/x-www-form-urlencoded"
    ];
    $data = [
        "grant_type" => "client_credentials"
    ];

    $options = [
        "http" => [
            "header"  => implode("\r\n", $headers),
            "method"  => "POST",
            "content" => http_build_query($data)
        ]
    ];

    $context  = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $responseData = json_decode($response, true);
    
    return $responseData['access_token'] ?? null;
}

