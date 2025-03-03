// Remplacez par votre propre clé API pour récupérer les données
const API_KEY = 'YOUR_API_KEY';

// Fonction pour récupérer les matchs d'une équipe donnée
async function fetchTeamMatches(teamId) {
    const url = `https://api.football-data.org/v4/teams/${teamId}/matches`;
    const response = await fetch(url, {
        method: 'GET',
        headers: {
            'X-Auth-Token': API_KEY
        }
    });

    const data = await response.json();
    return data.matches;  // Renvoie la liste des matchs de l'équipe
}

// Fonction pour récupérer les statistiques d'une équipe (buts marqués et encaissés)
function getTeamStats(teamName) {
    // Exemple de valeurs moyennes pour une équipe. Ces données doivent être calculées dynamiquement à partir des matchs précédents.
    const stats = {
        avgGoalsScored: 1.5,  // Moyenne des buts marqués
        avgGoalsConceded: 1.0 // Moyenne des buts encaissés
    };
    return stats;
}

// Fonction pour récupérer la forme actuelle d'une équipe (ex: 1.0 = forme moyenne, >1.0 = forme bonne)
function getTeamForm(teamName) {
    // Exemples de forme d'équipe. Ce calcul doit être basé sur les derniers matchs de l'équipe.
    return 1.1;  // Forme actuelle
}

// Fonction qui génère une prédiction basée sur les statistiques des équipes
function generatePrediction(homeTeam, awayTeam) {
    const homeTeamStats = getTeamStats(homeTeam);
    const awayTeamStats = getTeamStats(awayTeam);

    // Prédiction des scores en fonction des moyennes des buts marqués et encaissés
    const homeTeamPredictedScore = (homeTeamStats.avgGoalsScored + awayTeamStats.avgGoalsConceded) / 2;
    const awayTeamPredictedScore = (awayTeamStats.avgGoalsScored + homeTeamStats.avgGoalsConceded) / 2;

    // Calcul des Over/Under
    const overUnder = {
        "0.5": (homeTeamPredictedScore + awayTeamPredictedScore) > 0.5 ? "Over" : "Under",
        "1.5": (homeTeamPredictedScore + awayTeamPredictedScore) > 1.5 ? "Over" : "Under",
        "2.5": (homeTeamPredictedScore + awayTeamPredictedScore) > 2.5 ? "Over" : "Under",
        "3.5": (homeTeamPredictedScore + awayTeamPredictedScore) > 3.5 ? "Over" : "Under"
    };

    return {
        result: homeTeamPredictedScore > awayTeamPredictedScore ? 'Home Win' : (homeTeamPredictedScore < awayTeamPredictedScore ? 'Away Win' : 'Draw'),
        score: `${Math.round(homeTeamPredictedScore)} - ${Math.round(awayTeamPredictedScore)}`,
        overUnder
    };
}

// Fonction qui améliore la prédiction en tenant compte de la forme et de l'avantage à domicile
function enhancedPrediction(homeTeam, awayTeam) {
    const homeTeamStats = getTeamStats(homeTeam);
    const awayTeamStats = getTeamStats(awayTeam);

    const homeTeamForm = getTeamForm(homeTeam);
    const awayTeamForm = getTeamForm(awayTeam);

    // Avantage à domicile
    const homeAdvantage = 0.1;  // Par exemple, un petit bonus pour l'équipe à domicile

    // Calcul des scores prévus en fonction de la forme et de l'avantage à domicile
    const homeTeamPredictedScore = (homeTeamStats.avgGoalsScored + awayTeamStats.avgGoalsConceded + homeAdvantage) * homeTeamForm;
    const awayTeamPredictedScore = (awayTeamStats.avgGoalsScored + homeTeamStats.avgGoalsConceded) * awayTeamForm;

    const overUnder = {
        "0.5": (homeTeamPredictedScore + awayTeamPredictedScore) > 0.5 ? "Over" : "Under",
        "1.5": (homeTeamPredictedScore + awayTeamPredictedScore) > 1.5 ? "Over" : "Under",
        "2.5": (homeTeamPredictedScore + awayTeamPredictedScore) > 2.5 ? "Over" : "Under",
        "3.5": (homeTeamPredictedScore + awayTeamPredictedScore) > 3.5 ? "Over" : "Under"
    };

    return {
        result: homeTeamPredictedScore > awayTeamPredictedScore ? 'Home Win' : (homeTeamPredictedScore < awayTeamPredictedScore ? 'Away Win' : 'Draw'),
        score: `${Math.round(homeTeamPredictedScore)} - ${Math.round(awayTeamPredictedScore)}`,
        overUnder
    };
}

// Fonction pour afficher les matchs à venir avec leurs prédictions
async function displayUpcomingMatches() {
    // Remplacez cette URL par l'API que vous utilisez pour récupérer les matchs à venir
    const url = 'https://api.football-data.org/v4/competitions/FL1/matches?status=SCHEDULED';
    const response = await fetch(url, {
        method: 'GET',
        headers: {
            'X-Auth-Token': API_KEY
        }
    });

    const data = await response.json();
    const matches = data.matches.slice(0, 7);  // Récupère les 7 prochains matchs

    const table = document.getElementById("matchTable");
    table.innerHTML = "";  // Vide le tableau avant d'ajouter les nouveaux matchs

    matches.forEach(match => {
        const homeTeam = match.homeTeam.name;
        const awayTeam = match.awayTeam.name;

        // Générer la prédiction pour ce match
        const prediction = enhancedPrediction(homeTeam, awayTeam);

        // Ajouter une ligne dans le tableau
        const row = table.insertRow();
        row.innerHTML = `
            <td>${homeTeam}</td>
            <td>vs</td>
            <td>${awayTeam}</td>
            <td>${prediction.result}</td>
            <td>${prediction.score}</td>
            <td>Over 0.5: ${prediction.overUnder["0.5"]}</td>
            <td>Over 1.5: ${prediction.overUnder["1.5"]}</td>
            <td>Over 2.5: ${prediction.overUnder["2.5"]}</td>
            <td>Over 3.5: ${prediction.overUnder["3.5"]}</td>
        `;
    });
}

// Appel de la fonction pour afficher les matchs et les prédictions
displayUpcomingMatches();
