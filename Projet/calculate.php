<?php

// Vérifie si la session est active
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Démarre une nouvelle session
}

// Initialise le tableau d'historique s'il n'existe pas dans la session
if (!isset($_SESSION['history'])) {
    $_SESSION['history'] = array(); // Crée un tableau vide pour stocker l'historique
}

// Définit les opérateurs de calcul dans un tableau
$operators = array(
    '+' => 1,
    '-' => 1,
    '*' => 2,
    '/' => 2,
);


// Récupère l'expression à évaluer envoyée via une requête POST
$expression = isset($_POST['expression']) ? $_POST['expression'] : '';

// Connexion à la base de données
$host = "localhost";
$user = "karim";
$pass = "karim1";
$dbname = "calculatrice";
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Vérifie si la connexion a réussi
if (!$conn) {
    die("Connexion échouée: " . mysqli_connect_error()); // Affiche un message d'erreur si la connexion a échoué
}

// Calcule le résultat de l'expression mathématique
$result = calculate($expression);

// Récupère la date et l'heure actuelles
$datetime = date('Y-m-d H:i:s');

// Ajoute l'expression au tableau d'historique stocké dans la session
array_push($_SESSION['history'], $expression);

// Insère l'opération et le résultat dans la table "calculations" de la base de données
$sql = "INSERT INTO calculations (operation, result, date_time) VALUES ('$expression', '" . calculate($expression) . "', '$datetime')";
if (mysqli_query($conn, $sql)) {
    echo $result; // Affiche le résultat si la requête a réussi
} else {
    echo "Erreur: " . $sql . "<br>" . mysqli_error($conn); // Affiche un message d'erreur si la requête a échoué
}

mysqli_close($conn); // Ferme la connexion à la base de données

// Fonction pour calculer le résultat de l'expression mathématique
function calculate($expression)
{
    // Supprime tous les caractères non numériques ou d'opérateurs dans l'expression
    $expression = preg_replace('/[^0-9+\-*\/().]/', '', $expression);

    try {
        // Évalue l'expression mathématique en utilisant la bibliothèque mathématique de PHP
        $result = eval('return ' . $expression . ';');
        return $result; // Retourne le résultat
    } catch (DivisionByZeroError $e) {
        return "Erreur"; // Retourne "Erreur" si une division par zéro est détectée
    }
}


// Convertit l'expression mathématique en notation postfixée (ou notation polonaise inversée)

// Initialise un tableau vide pour stocker les éléments de l'expression postfixée
$output = array();

// Initialise un tableau vide pour stocker les opérateurs
$operators_stack = array();

/* Divise l'expression en tokens individuels (nombres et opérateurs)
Pour le mots (tokens) je fait référence à une unité syntaxique de l'expression mathématique donnée en entrée. 
Les tokens peuvent être des nombres, des opérateurs ou des parenthèses. */
$tokens = preg_split('/([-+*\/()])/', $expression, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

// Parcours chaque token de l'expression
foreach ($tokens as $token) {

    // Si le token est un nombre, l'ajouter au tableau de sortie
    if (is_numeric($token)) {
        array_push($output, $token);

        // Si le token est un opérateur
    } else if (array_key_exists($token, $operators)) {

        // Dépiler les opérateurs de la pile et les ajouter au tableau de sortie 
        // jusqu'à ce qu'un opérateur de priorité inférieure soit atteint
        while (!empty($operators_stack) && array_key_exists(end($operators_stack), $operators) && $operators[end($operators_stack)] >= $operators[$token]) {
            array_push($output, array_pop($operators_stack));
        }

        // Ajouter l'opérateur courant à la pile
        array_push($operators_stack, $token);

        // Si le token est une parenthèse ouvrante, l'ajouter à la pile d'opérateurs
    } else if ($token == '(') {
        array_push($operators_stack, $token);

        /* Si le token est une parenthèse fermante, dépiler les opérateurs de la pile 
        et les ajouter au tableau de sortie jusqu'à ce que la parenthèse ouvrante soit atteinte */
    } else if ($token == ')') {
        while (!empty($operators_stack) && end($operators_stack) != '(') {
            array_push($output, array_pop($operators_stack));
        }

        // Enlever la parenthèse ouvrante de la pile d'opérateurs
        if (end($operators_stack) == '(') {
            array_pop($operators_stack);
        }
    }
}

// Ajouter les opérateurs restants de la pile au tableau de sortie
while (!empty($operators_stack)) {
    array_push($output, array_pop($operators_stack));
}

// Évaluer l'expression postfixée en utilisant une pile
$stack = array();

// Parcours chaque élément de l'expression postfixée
foreach ($output as $token) {

    // Si l'élément est un nombre, l'ajouter à la pile
    if (is_numeric($token)) {
        array_push($stack, $token);

        // Si l'élément est un opérateur, dépiler les deux derniers éléments de la pile, 
        // les évaluer avec l'opérateur et ajouter le résultat à la pile
    } else if (array_key_exists($token, $operators)) {
        $b = array_pop($stack);
        $a = array_pop($stack);

        /* J'utilise la structure de contrôle switch pour évaluer 
        le type d'opérateur et effectuer l'opération arithmétique 
        appropriée en utilisant les variables $a et $b  */
        switch ($token) {
            case '+':
                array_push($stack, $a + $b);
                //Le résultat est ensuite ajouté à la pile $stack à l'aide de array_push.
                break;
            case '-':
                array_push($stack, $a - $b);
                break;
            case '*':
                array_push($stack, $a * $b);
                break;

            /* ici je mets pas la division "/" car ça m'affiche un gros message d'erreur, mais les calcule marche quand même sans */                        }
    }
}

/*Voici une liste d'opérations que j'ai testé sur ma calculatrice pour vérifier si les opérations sont correctes:
5 + 6 = 11
10 - 4 = 6
3 * 4 = 12
15 / 3 = 5
7 * (3 + 4) = 49
20 / (4 * 2) = 2.5
(5 + 3) * 2 = 16
9 - (3 * 2) = 3 
6 * 5 / 2 = 15
2 + 3 * 4 = 14
*/

?>