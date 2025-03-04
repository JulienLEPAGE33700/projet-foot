<?php
if (!isset($_GET['match_id']) || empty($_GET['match_id'])) {
    die("Match non spécifié.");
}

$matchId = $_GET['match_id'];
$apiKey = 'c753fab793164e5291a698e3ac27a6aa';  // Ta clé API

// URL pour récupérer les informations du match
$apiUrl = "https://api.football-data.org/v4/matches/$matchId";
$options = [
    "http" => [
        "header" => "X-Auth-Token: $apiKey\r\n"
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($apiUrl, false, $context);
$data = json_decode($response, true);

if (!isset($data['match'])) {
    die("Match non trouvé.");
}

$match = $data['match'];
$homeTeam = $match['homeTeam']['name'];
$awayTeam = $match['awayTeam']['name'];
$homeScore = $match['score']['fullTime']['home'];
$awayScore = $match['score']['fullTime']['away'];

// Exemple de calcul des prédictions
$predictedHomeScore = $homeScore ?? 0;  // Exemple simple
$predictedAwayScore = $awayScore ?? 0;  // Exemple simple

$over_1_5 = ($predictedHomeScore + $predictedAwayScore > 1.5) ? "Oui" : "Non";
$over_2_5 = ($predictedHomeScore + $predictedAwayScore > 2.5) ? "Oui" : "Non";
$over_3_5 = ($predictedHomeScore + $predictedAwayScore > 3.5) ? "Oui" : "Non";

echo json_encode([
    'home_team' => $homeTeam,
    'away_team' => $awayTeam,
    'predicted_home_score' => $predictedHomeScore,
    'predicted_away_score' => $predictedAwayScore,
    'over_1_5' => $over_1_5,
    'over_2_5' => $over_2_5,
    'over_3_5' => $over_3_5
]);
?>
