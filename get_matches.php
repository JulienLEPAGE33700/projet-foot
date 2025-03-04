<?php
if (!isset($_GET['competition']) || !isset($_GET['start_date']) || !isset($_GET['end_date'])) {
    die("Paramètres manquants.");
}

$competition = $_GET['competition'];
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$apiKey = 'c753fab793164e5291a698e3ac27a6aa';  // Ta clé API

// URL de l'API pour récupérer les matchs
$apiUrl = "https://api.football-data.org/v4/competitions/$competition/matches?dateFrom=$start_date&dateTo=$end_date";
$options = [
    "http" => [
        "header" => "X-Auth-Token: $apiKey\r\n"
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($apiUrl, false, $context);
$data = json_decode($response, true);

// Vérifier si des matchs sont trouvés
if (isset($data['matches']) && !empty($data['matches'])) {
    echo json_encode(['matches' => $data['matches']]);
} else {
    echo json_encode(['matches' => []]);
}
?>
