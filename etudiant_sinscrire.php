<?php
// Fichier : etudiant_sinscrire.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

// 1. Récupération et sécurisation des paramètres envoyés dans l'URL (GET)
$id_cours = isset($_GET['id_cours']) ? intval($_GET['id_cours']) : 0;
$id_etu = isset($_GET['id_etu']) ? intval($_GET['id_etu']) : 0;

if ($id_cours > 0 && $id_etu > 0) {
    
    // 2. Vérification anti-doublon : on s'assure que l'étudiant n'est pas DÉJÀ inscrit à ce cours
    $sql_check = "SELECT id_inscription FROM inscription WHERE id_etudiant = ? AND id_cours = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "ii", $id_etu, $id_cours);
    mysqli_stmt_execute($stmt_check);
    $res_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($res_check) == 0) {
        
        // 3. Aucune inscription trouvée, on procède à l'ajout.
        // CURDATE() est une fonction SQL qui insère automatiquement la date du jour (format YYYY-MM-DD)
        $sql_insert = "INSERT INTO inscription (date_inscription, id_etudiant, id_cours) VALUES (CURDATE(), ?, ?)";
        
        try {
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            
            // On lie les 2 variables entières (i, i)
            mysqli_stmt_bind_param($stmt_insert, "ii", $id_etu, $id_cours);
            mysqli_stmt_execute($stmt_insert);
            
            // 4. Succès ! On renvoie l'étudiant sur son tableau de bord de manière fluide
            header("Location: test.php?role_simule=Etudiant&id=" . $id_etu);
            exit();

        } catch (Exception $e) {
            // En cas de problème avec la base de données
            echo "<div style='font-family: sans-serif; padding: 30px; text-align: center;'>";
            echo "<h3 style='color: #cc0000;'>Erreur lors de l'inscription</h3>";
            echo "<p>Détail : " . $e->getMessage() . "</p>";
            echo "<a href='test.php?role_simule=Etudiant&id=" . $id_etu . "' style='padding: 10px 15px; background: #0056b3; color: white; text-decoration: none; border-radius: 5px;'>Retour à mes cours</a>";
            echo "</div>";
        }
        
    } else {
        // Si par hasard l'étudiant a cliqué deux fois vite ou trafiqué l'URL
        echo "<div style='font-family: sans-serif; padding: 30px; text-align: center;'>";
        echo "<h3>Vous êtes déjà inscrit à ce cours !</h3>";
        echo "<a href='test.php?role_simule=Etudiant&id=" . $id_etu . "' style='padding: 10px 15px; background: #0056b3; color: white; text-decoration: none; border-radius: 5px;'>Retour</a>";
        echo "</div>";
    }
} else {
    // Si l'URL est incomplète (ex: quelqu'un tape juste etudiant_sinscrire.php dans son navigateur)
    echo "<div style='font-family: sans-serif; padding: 30px; text-align: center;'>";
    echo "<h3 style='color: #cc0000;'>Informations manquantes</h3>";
    echo "<p>Impossible de procéder à l'inscription (Identifiant du cours ou de l'étudiant introuvable).</p>";
    echo "<a href='test.php' style='padding: 10px 15px; background: #0056b3; color: white; text-decoration: none; border-radius: 5px;'>Retour à l'accueil</a>";
    echo "</div>";
}
?>
