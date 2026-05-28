<?php
// Fichier : ajouter_cours.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

if (isset($_POST["button_ajouter_cours"])) {
    
    $titre_cours = trim($_POST["titre_cours"]);
    $code_cours  = trim($_POST["code_cours"]);
    $departement = trim($_POST["dep_cours"]);
    $niveau      = trim($_POST["niveau_cours"]);
    $semestre    = $_POST["semestre_cours"];
    $categorie   = $_POST["categorie_cours"];
    
    // Si aucun prof n'est sélectionné, on met NULL
    $id_prof = !empty($_POST["prof_cours"]) ? intval($_POST["prof_cours"]) : NULL;

    $credits_par_defaut = 5; 
    $coefficient_par_defaut = 2.0;

    $sql = "INSERT INTO COURS (nom_matiere, code_cours, departement, niveau, semestre, categorie, credits, coefficient, id_enseignant) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    try {
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssidi", $titre_cours, $code_cours, $departement, $niveau, $semestre, $categorie, $credits_par_defaut, $coefficient_par_defaut, $id_prof);
        
        mysqli_stmt_execute($stmt);
        
        // Redirection corrigée vers test.php !
        header("Location: test.php?succes_cours=1");
        exit();

    } catch (Exception $e) {
        // Au lieu de faire crasher la page en blanc, on affiche une belle erreur
        echo "<div style='font-family: sans-serif; padding: 30px;'>";
        echo "<h3 style='color: red;'>Erreur SQL lors de la création du cours.</h3>";
        echo "<p><strong>Détail technique :</strong> " . $e->getMessage() . "</p>";
        echo "<a href='test.php' style='padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Retour aux cours</a>";
        echo "</div>";
    }
} else {
    echo "Accès refusé.";
}
?>
