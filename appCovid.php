<?php
session_start();

if (!isset($_SESSION['historique'])) {
    $_SESSION['historique'] = [];
}

// Initialisation du tableau d'erreurs
$erreurs = [];

// Initialisation des variables avec des valeurs par défaut
$nom = "";
$prenom = "";
$score = 0;
$resultat = "Les informations fournies ne permettent pas de déterminer le résultat.";
$temperature_error = "";

// Fonction pour vérifier si une chaîne de caractères contient uniquement des lettres et des espaces
function estValideNom($str) {
    return preg_match("/^[a-zA-ZÀ-ÿ\s]+$/u", $str);
}

// Vérification si le formulaire a été soumis
if (isset($_POST['submit'])) {
    // Récupération des données du formulaire
    if (empty($_POST['nom'])) {
        $erreurs[] = "Le champ nom ne doit contenir que des lettres et des espaces.";
    } else {
        $nom = $_POST['nom'];
        if(!estValideNom($nom)){
            $erreurs[]= "Le champ prenom ne doit contenir que des lettres et des espaces.";
        }
    }

    if (empty($_POST['prenom'])) {
        $erreurs[] = "Le champ prénom est obligatoire.";
    } else {
        $prenom = $_POST['prenom'];
        if(!estValideNom($prenom)){
            $erreurs[]= "le prenom doit être valide";
        }
    }

    if (isset($_POST['poids'])) {
        $poids = $_POST['poids'];
        // Vérification que le poids est un nombre positif
        if (!is_numeric($poids) || $poids <= 0 || $poids > 250) {
            $erreurs[] = "Le poids doit être un nombre positif ne dépassant pas 250 kg.";
        }
    }

    if (isset($_POST['temperature'])) {
        $temperature = $_POST['temperature'];
        // Vérification de la température minimale et maximale
        if ($temperature < 28 || $temperature > 41) {
            $erreurs[] = "La température doit être entre 28°C et 41°C.";
        } else {
            // Attribution des points en fonction de la température
            if ($temperature >= 38) {
                $score += 20;
            } elseif ($temperature >= 37) {
                $score += 10;
            }
        }
    }

        // Vérification de l'âge
        if (isset($_POST['age'])) {
             $age = $_POST['age'];
                // Liste des options valides pour l'âge
             $options_age = ['2_10', '15_30', '45_100'];

        if (!in_array($age, $options_age)) {
              $erreurs[] = "Veuillez sélectionner une option d'âge valide.";
     }
    } else {
         $erreurs[] = "Le champ âge est obligatoire.";
    }

    // Vérification des symptômes
$symptomes_oui = [
    isset($_POST['maux_de_tete']) && $_POST['maux_de_tete'] === 'oui',
    isset($_POST['diarrhee']) && $_POST['diarrhee'] === 'oui',
    isset($_POST['toux']) && $_POST['toux'] === 'oui',
    isset($_POST['pertes_odorat']) && $_POST['pertes_odorat'] === 'oui',
];

if (!in_array(true, $symptomes_oui)) {
    $erreurs[] = "Veuillez sélectionner au moins un symptôme.";
}



   

    // Vérification que tous les champs obligatoires sont remplis
    if (empty($nom) || empty($prenom) || empty($poids) || empty($temperature) || empty($age)) {
        $erreurs[] = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Vérification que les champs nom et prénom ne contiennent que des lettres et des espaces
        if (!estValideNom($nom)) {
            $erreurs[] = "Le champ nom ne doit contenir que des lettres et des espaces.";
        }
        if (!estValideNom($prenom)) {
            $erreurs[] = "Le champ prénom ne doit contenir que des lettres et des espaces.";
        }

       

        // Détermination du résultat en fonction du score
        if ($resultat === "Les informations fournies ne permettent pas de déterminer le résultat.") {
            if ($score >= 80) {
                $resultat = "Vous avez le covid.";
            } elseif ($score >= 50 && $score < 80) {
                $resultat = "Vous êtes susceptible d'avoir le covid.";
            } else {
                $resultat = "Vous n'avez probablement pas le covid.";
            }
        }

        // Enregistrement dans l'historique si aucune erreur générale
        if (empty($erreurs)) {
            $utilisateur_historique = [
                'nom' => $nom,
                'prenom' => $prenom,
                'score' => $score,
                'resultat' => $resultat,
            ];
            // 
          
            // 
            $doublon = false;
            foreach ($_SESSION['historique'] as $existing_data) {
                if ($existing_data['nom'] === $utilisateur_historique['nom'] && $existing_data['prenom'] === $utilisateur_historique['prenom']) {
                    $doublon = true;
                    break;
                }
            }

            if (!$doublon) {
                $_SESSION['historique'][] = $utilisateur_historique;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Formulaire et Résultat COVID-19</title>
</head>
<body>
    <h1>Test COVID-19</h1>

  
        <form action="#" method="post">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" value="<?php echo $nom;?>"><br>
        

        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" value="<?php echo $prenom;?>" ><br>
        

        <label for="poids">Poids (en kg) :</label>
        <input type="number" id="poids" name="poids" value="<?php echo $poids;?>" ><br>
        

        <label for="temperature">Température (en °C) :</label>
        <input type="number" id="temperature" name="temperature" value="<?php echo $temperature;?>"><br>
       

        <label for="age">Âge :</label><br>
        <input type="radio" id="age_2_10" name="age" <?php if (isset($age) && $age=="2_10") echo "checked";?> value="2_10">
        <label for="age_2_10">2 à 10 ans</label><br>
        <input type="radio" id="age_15_30" name="age" <?php if (isset($age) && $age=="15_30") echo "checked";?> value="15_30">
        <label for="age_15_30">15 à 30 ans</label><br>
        <input type="radio" id="age_45_100" name="age" <?php if (isset($age) && $age=="45_100") echo "checked";?> value="45_100">
        <label for="age_45_100">45 à 100 ans</label><br>

        <label for="maux_de_tete">Avez-vous des maux de tête ?</label>
        <input type="radio" id="maux_de_tete_oui" name="maux_de_tete" <?php if (isset($maux_de_tete) && $maux_de_tete=="oui") echo "checked";?> value="oui">
        <label for="maux_de_tete_oui">Oui</label>
        <input type="radio" id="maux_de_tete_non" name="maux_de_tete" <?php if (isset($maux_de_tete) && $maux_de_tete=="non") echo "checked";?> value="non">
        <label for="maux_de_tete_non">Non</label><br>

        <label for="diarrhee">Avez-vous de la diarrhée ?</label>
        <input type="radio" id="diarrhee_oui" name="diarrhee" <?php if (isset($diarrhee) && $diarrhee=="oui") echo "checked";?> value="oui">
        <label for="diarrhee_oui">Oui</label>
        <input type="radio" id="diarrhee_non" name="diarrhee" <?php if (isset($diarrhee) && $diarrhee=="non") echo "checked";?> value="non">
        <label for="diarrhee_non">Non</label><br>

        <label for="toux">Avez-vous de la toux ?</label>
        <input type="radio" id="toux_oui" name="toux" <?php if (isset($toux) && $toux=="oui") echo "checked";?> value="oui">
        <label for="toux_oui">Oui</label>
        <input type="radio" id="toux_non" name="toux" <?php if (isset($toux) && $toux=="non") echo "checked";?> value="non">
        <label for="toux_non">Non</label><br>

        <label for="pertes_odorat">Avez-vous des pertes d'odorat ?</label>
        <input type="radio" id="pertes_odorat_oui" name="pertes_odorat" <?php if (isset($pertes_odorat) && $pertes_odorat=="oui") echo "checked";?> value="oui">
        <label for="pertes_odorat_oui">Oui</label>
        <input type="radio" id="pertes_odorat_non" name="pertes_odorat"<?php if (isset($pertes_odorat) && $pertes_odorat=="non") echo "checked";?>  value="non">
        <label for="pertes_odorat_non">Non</label><br>

        <input type="submit" name="submit" value="Soumettre">
    </form>
    
    
    <?php if (!empty($erreurs)): ?>
    <h2>Erreurs :</h2>
    <ul>
        <?php foreach ($erreurs as $erreur): ?>
            <li><?php echo $erreur; ?></li>
        <?php endforeach; ?>
    </ul>
    <?php elseif (isset($_POST['submit']) && empty($general_error)): ?>
    <h2>Résultat du test :</h2>
    <p>Nom : <?php echo htmlspecialchars($nom); ?></p>
    <p>Prénom : <?php echo htmlspecialchars($prenom); ?></p>
    <p>Score : <?php echo $score; ?>%</p>
    <p>Résultat : <?php echo $resultat; ?></p>
    <?php endif; ?>

    <!-- Affichage de l'historique -->
    <h2>Historique des tests :</h2>
    <ul>
    <?php foreach ($_SESSION['historique'] as $utilisateur_histo): ?>
        <li>Nom : <?php echo $utilisateur_histo['nom']; ?></li>
        <li>Prénom : <?php echo $utilisateur_histo['prenom']; ?></li>
        <?php if ($utilisateur_histo['score'] !== null): ?>
        <li>Score : <?php echo $utilisateur_histo['score']; ?>%</li>
        <?php endif; ?>
        <li>Résultat : <?php echo $utilisateur_histo['resultat']; ?></li>
        <hr>
    <?php endforeach; ?>
    </ul>
</body>
</html>
