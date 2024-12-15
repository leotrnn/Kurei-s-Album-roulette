<?php

$clientId = "";
$clientSecret = "";

function getAccessToken($clientId, $clientSecret)
{
    // URL pour récupérer le token
    $url = 'https://accounts.spotify.com/api/token';

    // Création des paramètres du body de la requête
    $data = [
        'grant_type' => 'client_credentials',
    ];

    // Préparation des headers pour l'authentification
    $headers = [
        'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
        'Content-Type: application/x-www-form-urlencoded'
    ];

    // Configuration de la requête HTTP
    $options = [
        'http' => [
            'header'  => implode("\r\n", $headers),
            'method'  => 'POST',
            'content' => http_build_query($data),
        ]
    ];

    // Créer le contexte de la requête
    $context  = stream_context_create($options);

    // Exécuter la requête
    $response = file_get_contents($url, false, $context);

    // Vérifier si la requête a échoué
    if ($response === FALSE) {
        return null;
    }

    // Décoder la réponse JSON
    $data = json_decode($response, true);

    // Retourner le token d'accès si trouvé
    return $data['access_token'] ?? null;
}

$accessToken = getAccessToken($clientId, $clientSecret);

?>
