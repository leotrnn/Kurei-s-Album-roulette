<?php

include("config.php");

$genre = filter_input(INPUT_POST, 'genre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$artist1 = filter_input(INPUT_POST, 'artist1', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$artist2 = filter_input(INPUT_POST, 'artist2', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$artist3 = filter_input(INPUT_POST, 'artist3', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$submit = filter_input(INPUT_POST, 'submit', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

function getBestGenre(string $genre, string $artist1, string $artist2, string $artist3, $accessToken)
{
    $return = "";

    $genreWeight = 3.0;
    $artistWeight = 1.1;

    $tabGenres = [];

    if($genre != ""){
        $tabGenres["genre"] = [$genre];
    }

    if ($artist1 !== "") {
        $artistGenres = GetGenreByArtist($artist1, $accessToken);
        if ($artistGenres[1] !== null) {
            $tabGenres["artist1"] = $artistGenres;
        }
    }
    if ($artist2 !== "") {
        $artistGenres = GetGenreByArtist($artist2, $accessToken);
        if ($artistGenres[1] !== null) {
            $tabGenres["artist2"] = $artistGenres;
        }
    }
    if ($artist3 !== "") {
        $artistGenres = GetGenreByArtist($artist3, $accessToken);
        if ($artistGenres[1] !== null) {
            $tabGenres["artist3"] = $artistGenres;
        }
    }


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

   


    $highestScore = max($tabScores);
    $bestGenres = array_keys($tabScores, $highestScore);


    $return = $bestGenres;

 

    return $return;
}

function GetGenreByArtist($artistName, $accessToken)
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

if ($submit == "search") {
    $selectedGenre = getBestGenre($genre, $artist1, $artist2, $artist3, $accessToken);
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
    <form action="index.php" method="post">
        <h1>What genre of music do you like ?</h1>
        <input type="text" name="genre" placeholder="genre"  value="<?= $genre ?>" id="">
        <h1>Add up to 3 artists you like to perfect the search (optional)</h1>
        <input type="text" name="artist1" placeholder="artist N°1" value="<?= $artist1 ?>" id="">
        <input type="text" name="artist2" placeholder="artist N°2" value="<?= $artist2 ?>" id="">
        <input type="text" name="artist3" placeholder="artist N°3" value="<?= $artist3 ?>" id="">
        <input type="submit" name="submit"  value="search">
    </form>
    <?php if ($selectedGenre != "") { ?>
        <?php if (count($selectedGenre) > 1) { ?>
            <form action="spin.php" method="post">
                <h1>What's your favorite genre between those ?</h1>
                <select name="favoriteGenre">
                    <?php for ($i = 0; $i < count($selectedGenre); $i++) { ?>
                        <option value="<?= $selectedGenre[$i] ?>"><?= $selectedGenre[$i] ?></option>
                    <?php } ?>
                    <input type="submit" value="this one">
                </select>
            </form>
        <?php } else { ?>
            <?php if ($selectedGenre != "") { ?>
                <h1>Basically you like <?= $selectedGenre[0] ?> huh</h1>
                <form action="spin.php" method="post">
                    <input type="hidden" name="favoriteGenre" value="<?= $selectedGenre[0] ?>">
                    <input type="submit" value="ye">
                </form>
    <?php }
        }
    } ?>
</body>

</html>