<?php
// Liste des ligues et leurs codes (exemples)
$competitions = [
    'FL1' => 'Ligue 1',
    'PL' => 'Premier League',
    'BL1' => 'Bundesliga',
    'PD' => 'La Liga',
    'SA' => 'Serie A',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prédictions de matchs</title>
</head>
<body>

    <h1>Choisir une ligue et récupérer les matchs à venir</h1>

    <!-- Formulaire pour sélectionner la ligue -->
    <form id="competitionForm">
        <label for="competitionSelect">Sélectionner une ligue :</label>
        <select name="competition" id="competitionSelect">
            <option value="">Choisissez une ligue</option>
            <?php
            foreach ($competitions as $code => $name) {
                echo "<option value='$code'>$name</option>";
            }
            ?>
        </select>
        <button type="button" onclick="getMatches()">Récupérer les matchs des 7 prochains jours</button>
    </form>

    <br>

    <!-- Liste déroulante pour afficher les matchs -->
    <form id="matchForm">
        <label for="matchSelect">Sélectionner un match :</label>
        <select name="match" id="matchSelect">
            <option value="">Choisissez un match</option>
        </select>
        <button type="button" onclick="showPredictions()">Afficher les prédictions</button>
    </form>

    <br>

    <!-- Tableau récapitulatif pour les prédictions -->
    <div id="predictionResult">
        <!-- Résultats des prédictions seront affichés ici -->
    </div>

    <script>
        // Fonction pour récupérer les matchs des 7 prochains jours en fonction de la ligue
        function getMatches() {
            const competition = document.getElementById('competitionSelect').value;
            if (!competition) {
                alert("Veuillez sélectionner une ligue !");
                return;
            }

            const today = new Date();
            const next7Days = new Date(today);
            next7Days.setDate(today.getDate() + 7);

            const startDate = today.toISOString().split('T')[0];  // Format YYYY-MM-DD
            const endDate = next7Days.toISOString().split('T')[0];  // Format YYYY-MM-DD

            // Requête AJAX pour récupérer les matchs de l'API
            fetch(`get_matches.php?competition=${competition}&start_date=${startDate}&end_date=${endDate}`)
                .then(response => response.json())
                .then(data => {
                    let matchSelect = document.getElementById('matchSelect');
                    matchSelect.innerHTML = '<option value="">Choisissez un match</option>';  // Reset

                    // Vérifier si des matchs ont été récupérés
                    if (data.matches && data.matches.length > 0) {
                        data.matches.forEach(match => {
                            const option = document.createElement('option');
                            option.value = match.id;
                            option.innerText = `${match.homeTeam.name} vs ${match.awayTeam.name} - ${match.utcDate}`;
                            matchSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.innerText = "Aucun match trouvé pour cette période";
                        matchSelect.appendChild(option);
                    }
                });
        }

        // Fonction pour afficher les prédictions lorsque l'utilisateur sélectionne un match
        function showPredictions() {
            const matchId = document.getElementById('matchSelect').value;
            if (!matchId) {
                alert("Veuillez sélectionner un match !");
                return;
            }

            // Requête AJAX pour récupérer les données du match et les prédictions
            fetch(`predictions.php?match_id=${matchId}`)
                .then(response => response.json())
                .then(data => {
                    let predictionDiv = document.getElementById('predictionResult');
                    predictionDiv.innerHTML = `
                        <h3>Prédiction pour ${data.home_team} vs ${data.away_team}</h3>
                        <table border="1">
                            <tr><th>Score estimé</th><td>${data.predicted_home_score} - ${data.predicted_away_score}</td></tr>
                            <tr><th>Over 1.5</th><td>${data.over_1_5}</td></tr>
                            <tr><th>Over 2.5</th><td>${data.over_2_5}</td></tr>
                            <tr><th>Over 3.5</th><td>${data.over_3_5}</td></tr>
                        </table>
                    `;
                });
        }
    </script>

</body>
</html>
