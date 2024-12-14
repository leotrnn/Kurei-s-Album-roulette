<?php
include("config.php");

$favoriteGenre = filter_input(INPUT_POST, 'favoriteGenre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

function getRandomAlbumByGenre($favoriteGenre, $accessToken)
{
    // Étape 1 : Récupérer les artistes associés au genre
    $artists = getArtistsByGenre($favoriteGenre, $accessToken);
    if (empty($artists)) {
        return "Aucun artiste trouvé pour le genre '$favoriteGenre'.";
    }

    // Étape 2 : Choisir un artiste aléatoire
    $randomArtist = $artists[array_rand($artists)];

    // Étape 3 : Récupérer les albums de l'artiste
    $albums = getAlbumsByArtist($randomArtist['id'], $accessToken);
    if (empty($albums)) {
        return "Aucun album trouvé pour l'artiste '{$randomArtist['name']}'.";
    }

    // Étape 4 : Choisir un album aléatoire
    $randomAlbum = $albums[array_rand($albums)];

    // Retourner l'album choisi avec sa couverture
    return [
        'artist' => $randomArtist['name'],
        'album' => $randomAlbum['name'],
        'url' => $randomAlbum['external_urls']['spotify'], // Lien Spotify pour l'album
        'cover' => $randomAlbum['images'][0]['url'] ?? null // URL de la couverture
    ];
}

function getArtistsByGenre($genre, $accessToken)
{
    $url = "https://api.spotify.com/v1/search?q=genre:" . urlencode($genre) . "&type=artist";
    $headers = [
        "Authorization: Bearer " . $accessToken
    ];

    $options = [
        "http" => [
            "header"  => implode("\r\n", $headers),
            "method"  => "GET"
        ]
    ];

    $context  = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === FALSE) {
        return [];
    }

    $data = json_decode($response, true);

    return $data['artists']['items'] ?? [];
}

function getAlbumsByArtist($artistId, $accessToken)
{
    $url = "https://api.spotify.com/v1/artists/" . $artistId . "/albums?include_groups=album";
    $headers = [
        "Authorization: Bearer " . $accessToken
    ];

    $options = [
        "http" => [
            "header"  => implode("\r\n", $headers),
            "method"  => "GET"
        ]
    ];

    $context  = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === FALSE) {
        return [];
    }

    $data = json_decode($response, true);
    return $data['items'] ?? [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($favoriteGenre)) {
    $result = getRandomAlbumByGenre($favoriteGenre, $accessToken);

    if (is_array($result)) {
        echo "<p>Artiste : <strong>{$result['artist']}</strong></p>";
        echo "<p>Album : <strong>{$result['album']}</strong></p>";
        if ($result['cover']) {
            echo "<img src='{$result['cover']}' alt='Cover de l’album' style='max-width:300px; border-radius:10px;'>";
        } else {
            echo "<p>Aucune couverture disponible.</p>";
        }
        echo "<p><a href='{$result['url']}' target='_blank'>Écouter sur Spotify</a></p>";
    } else {
        echo "<p>$result</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
    <title>Kurei's album roulette</title>
</head>
<body>

</body>
</html>
