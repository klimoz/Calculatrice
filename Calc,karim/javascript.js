// Fonction pour effacer l'écran de la calculatrice
function clearDisplay() {
  document.getElementById("display").value = "";
}

// Fonction pour mettre à jour l'écran de la calculatrice avec l'opérateur sélectionné
function updateDisplay(operator) {
  document.getElementById("display").value += operator;
}

// Fonction pour effectuer le calcul en envoyant les données au serveur avec AJAX
function calculate() {
  // Récupération de l'expression mathématique à calculer
  const expression = document.getElementById("display").value;

  // Envoi de la requête AJAX pour calculer l'expression mathématique
  const xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      // Affichage du résultat sur l'écran de la calculatrice
      document.getElementById("display").value = this.responseText;

      // Enregistrement de l'opération dans l'historique
      const history = document.getElementById("history");
      history.value += `${expression} = ${this.responseText} (${new Date().toLocaleString()})\n`;
    }
  };
  xhr.open("POST", "calculate.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.send(`expression=${encodeURIComponent(expression)}`);
}

// Fonction pour afficher ou masquer l'historique des opérations avec la touche (↓) 
function showHistory() {
  const history = document.getElementById("history");
  history.style.display = history.style.display === "none" ? "block" : "none";
}

// Fonction pour afficher la dernière opération dans l'écran de la calculatrice
function displayLastOperation() {
  const history = document.getElementById("history").value.trim();
  const expressions = history.split("\n");

  // Vérifier s'il y a des opérations dans l'historique
  if (expressions.length > 0) {
    // Récupérer la dernière opération dans l'historique
    const lastExpression = expressions[expressions.length - 1];

    // Afficher la dernière opération dans l'écran de la calculatrice
    document.getElementById("display").value = lastExpression.split(" = ")[0];
  }
}

let refreshCounter = 0;
let currentOperation = "";
// Fonction pour afficher l'historique des opérations dans le champ de saisie avec la touche (↺)
function displayHistory() {
  const history = document.getElementById("history").value.trim();
  const expressions = history.split("\n");

  // Je vérifier s'il y a des opérations dans l'historique
  if (expressions.length > 0) {
    // Récupérer l'opération correspondant au compteur
    currentOperation = expressions[refreshCounter % expressions.length].split(" = ")[0];

    // J'affiche l'opération dans le champ de saisie
    document.getElementById("display").value = currentOperation;

    // Incrémenter le compteur
    refreshCounter++;
  }
}


//Une petit Précisions la touche supprimée sur mon clavier n'a pas voulu marcher, j'ai essayé avec "delete" "Backspace" Suppr" "Effacer" "Supprimer" et aucune n'a voulu marcher

// Attacher des événements aux touches du clavier
document.addEventListener("keydown", function (event) {
  const key = event.key;
  const validKeys = ["0", "1", "2", "3", "4", "5", "(", "6", "7", "8", "9",")", ".", "/", "*", "-", "+", "13", "Suppr"]; // le numéro 13 coresspandes a la touche "enter"

  // Vérifier si la touche appuyée est valide
  if (validKeys.indexOf(key) >= 0) {
    // Mettre à jour l'écran de la calculatrice
    updateDisplay(key);

    // Empêcher l'événement par défaut du clavier
    event.preventDefault();
  } else if (key === "Enter") {
    // Calculer l'expression mathématique lorsque la touche Entrée est enfoncée
    calculate();

    // Empêcher l'événement par défaut du clavier
    event.preventDefault();
  } else if (key === "Suppr") {
    // Effacer le dernier caractère saisi de l'écran de la calculatrice
    clearLastCharacter();

    // Empêcher l'événement par défaut du clavier
    event.preventDefault();
  } else if (key === "+") {
    // Afficher la dernière opération dans l'écran de la calculatrice
    displayLastOperation();

    // Empêcher l'événement par défaut du clavier
    event.preventDefault();
  }
});
