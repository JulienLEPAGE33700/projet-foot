// Fonction pour afficher les bons champs selon le type d'import sélectionné
function toggleImportFields() {
    let importType = document.getElementById("importType").value;
    
    document.getElementById("seasonField").style.display = importType === "season" ? "block" : "none";
    document.getElementById("periodFields").style.display = importType === "period" ? "block" : "none";
    document.getElementById("dateField").style.display = importType === "date" ? "block" : "none";

    // Solution : Forcer le rechargement des champs date
    if (importType === "period" || importType === "date") {
        document.getElementById("dateFrom").value = "";
        document.getElementById("dateTo").value = "";
        document.getElementById("date").value = "";
    }
}

// Fonction pour récupérer les résultats selon les paramètres choisis
function fetchResults() {
    let competition = document.getElementById("competition").value;
    let importType = document.getElementById("importType").value;
    let url = `fetch_results.php?competition=${competition}`;

    if (importType === "season") {
        let season = document.getElementById("season").value;
        url += `&season=${season}`;
    } else if (importType === "period") {
        let dateFrom = document.getElementById("dateFrom").value;
        let dateTo = document.getElementById("dateTo").value;
        if (!dateFrom || !dateTo) {
            document.getElementById("message").innerText = "Veuillez sélectionner une période valide.";
            return;
        }
        url += `&dateFrom=${dateFrom}&dateTo=${dateTo}`;
    } else if (importType === "date") {
        let date = document.getElementById("date").value;
        if (!date) {
            document.getElementById("message").innerText = "Veuillez choisir une date.";
            return;
        }
        url += `&date=${date}`;
    }

    fetch(url)
        .then(response => response.text())
        .then(data => document.getElementById('message').innerHTML = data)
        .catch(error => document.getElementById('message').innerHTML = "Erreur : " + error);
}

// Ajouter un Event Listener au chargement de la page
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("importType").addEventListener("change", toggleImportFields);
});
