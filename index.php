<?php

include("config.php");

$genre = filter_input(INPUT_POST, 'genre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$artist1 = filter_input(INPUT_POST, 'artist1', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$artist2 = filter_input(INPUT_POST, 'artist2', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$artist3 = filter_input(INPUT_POST, 'artist3', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


function getBestGenre(string $genre, string $artist1, string $artist2, string $artist3, $accessToken)
{
    $return = "";

    $genreWeight = 3.0;
    $artistWeight = 1.1;

    $tabGenres = [];

    $tabGenres["genre"] = [$genre];
    if ($artist1 !== "") {
        $tabGenres["artist1"] = GetArtistGenre($artist1, $accessToken);
    }
    if ($artist2 !== "") {
        $tabGenres["artist2"] = GetArtistGenre($artist2, $accessToken);
    }
    if ($artist3 !== "") {
        $tabGenres["artist3"] = GetArtistGenre($artist3, $accessToken);
    }


    echo "<pre>";
    var_dump(json_encode($tabGenres, JSON_PRETTY_PRINT));
    echo "</pre>";


    $tabScores = [];


    foreach ($tabGenres as $category => $genres) {

        foreach ($genres as $genreItem) {

            if ($category == "genre") {
                if (!isset($tabScores[$genreItem])) {
                    $tabScores[$genreItem] = $genreWeight;
                } else {
                    $tabScores[$genreItem] += $artistWeight;
                }
            } else {
                if (!isset($tabScores[$genreItem])) {
                    $tabScores[$genreItem] = $artistWeight;
                } else {
                    $tabScores[$genreItem] += $artistWeight;
                }
            }
        }
    }

    arsort($tabScores);

    echo "<pre>";
    var_dump(json_encode($tabScores, JSON_PRETTY_PRINT));
    echo "</pre>";

    return $return;
}

if ($genre != "") {
    $genres = getBestGenre($genre, $artist1, $artist2, $artist3, $accessToken);
}

function searchArtistsByGenre(string $genre) {}

function GetArtistGenre($artistName, $accessToken)
{
    $return = null;

    $url = "https://api.spotify.com/v1/search?q=" . urlencode($artistName) . "&type=artist&limit=1";
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

    if ($response !== FALSE) {
        $data = json_decode($response, true);
        if (isset($data['artists']['items'][0])) {
            $artist = $data['artists']['items'][0];
            $return =  $artist["genres"];
        } else {
            echo "Aucun artiste trouvé.";
        }
    } else {
        echo "Erreur lors de la récupération de l'artiste.";
    }

    return $return;
}


?>


<form action="#" method="post">
    <input type="text" name="genre" placeholder="genre" value="<?= $genre ?>" id="">
    <input type="text" name="artist1" placeholder="artist1" value="<?= $artist1 ?>" id="">
    <input type="text" name="artist2" placeholder="artist2" value="<?= $artist2 ?>" id="">
    <input type="text" name="artist3" placeholder="artist3" value="<?= $artist3 ?>" id="">
    <input type="submit" value="nigga">
</form>