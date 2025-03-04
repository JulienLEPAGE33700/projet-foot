<?php
// Affichage des erreurs pour le debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration de la base de données
$host = 'localhost';
$dbname = 'football_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Vérification des paramètres
if (!isset($_GET['competition'])) {
    die("Le paramètre 'competition' est obligatoire !");
}

$competition = $_GET['competition'];
$apiKey = 'c753fab793164e5291a698e3ac27a6aa';

// Déterminer le type de requête : saison, période ou jour unique
if (isset($_GET['season'])) {
    // Import par saison complète
    $season = $_GET['season'];
    $apiUrl = "https://api.football-data.org/v4/competitions/$competition/matches?season=$season";
} elseif (isset($_GET['dateFrom']) && isset($_GET['dateTo'])) {
    // Import par période (ex: du 01/03/2024 au 10/03/2024)
    $dateFrom = $_GET['dateFrom'];
    $dateTo = $_GET['dateTo'];
    $apiUrl = "https://api.football-data.org/v4/competitions/$competition/matches?dateFrom=$dateFrom&dateTo=$dateTo";
} elseif (isset($_GET['date'])) {
    // Import par jour unique
    $date = $_GET['date'];
    $apiUrl = "https://api.football-data.org/v4/competitions/$competition/matches?dateFrom=$date&dateTo=$date";
} else {
    die("Vous devez fournir soit 'season', soit 'dateFrom' et 'dateTo', soit 'date' !");
}

// Configuration de la requête API
$options = [
    "http" => [
        "header" => "X-Auth-Token: $apiKey\r\n"
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($apiUrl, false, $context);
$data = json_decode($response, true);

// Vérifier si l'API a retourné des matchs
if (!isset($data['matches']) || empty($data['matches'])) {
    die("Aucun match trouvé pour cette requête.");
}

$inserted = 0;
foreach ($data['matches'] as $match) {
    // Vérifier si le match a un score final
    if (!isset($match['score']['fullTime']['home']) || !isset($match['score']['fullTime']['away'])) {
        continue; // Ignore les matchs sans résultat
    }

    $matchId = $match['id'];
    $homeTeam = $match['homeTeam']['name'];
    $awayTeam = $match['awayTeam']['name'];
    $homeScore = $match['score']['fullTime']['home'];
    $awayScore = $match['score']['fullTime']['away'];
    $matchDate = $match['utcDate'];

    // Déterminer le résultat 1/N/2
    if ($homeScore > $awayScore) {
        $matchResult = '1';
    } elseif ($homeScore < $awayScore) {
        $matchResult = '2';
    } else {
        $matchResult = 'N';
    }

    // Calcul des over/under
    $totalGoals = $homeScore + $awayScore;
    $over_0_5 = $totalGoals > 0 ? 1 : 0;
    $over_1_5 = $totalGoals > 1 ? 1 : 0;
    $over_2_5 = $totalGoals > 2 ? 1 : 0;
    $over_3_5 = $totalGoals > 3 ? 1 : 0;

    // Vérifier si le match existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE match_id = ?");
    $stmt->execute([$matchId]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        // Insérer en base
        $stmt = $pdo->prepare("
            INSERT INTO matches (match_id, competition, home_team, away_team, home_score, away_score, match_date, match_result, over_0_5, over_1_5, over_2_5, over_3_5)
            VALUES (:match_id, :competition, :home_team, :away_team, :home_score, :away_score, :match_date, :match_result, :over_0_5, :over_1_5, :over_2_5, :over_3_5)
        ");

        $stmt->execute([
            ':match_id' => $matchId,
            ':competition' => $competition,
            ':home_team' => $homeTeam,
            ':away_team' => $awayTeam,
            ':home_score' => $homeScore,
            ':away_score' => $awayScore,
            ':match_date' => $matchDate,
            ':match_result' => $matchResult,
            ':over_0_5' => $over_0_5,
            ':over_1_5' => $over_1_5,
            ':over_2_5' => $over_2_5,
            ':over_3_5' => $over_3_5
        ]);

        $inserted++;
    }
}

echo "Données mises à jour avec succès ! ($inserted matchs ajoutés)";
?>
