<?php
// Fichier : supprimer_prof.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_enseignant = intval($_GET['id']);
    
    
    mysqli_begin_transaction($conn);

    try {
         suppression !
        $sql_cours = "UPDATE COURS SET id_enseignant = NULL WHERE id_enseignant = ?";
        $stmt_cours = mysqli_prepare($conn, $sql_cours);
        mysqli_stmt_bind_param($stmt_cours, "i", $id_enseignant);
        mysqli_stmt_execute($stmt_cours);

      
        $sql_prof = "DELETE FROM ENSEIGNANT WHERE id_enseignant = ?";
        $stmt_prof = mysqli_prepare($conn, $sql_prof);
        mysqli_stmt_bind_param($stmt_prof, "i", $id_enseignant);
        mysqli_stmt_execute($stmt_prof);
        
        // On valide les deux actions d'un coup
        mysqli_commit($conn);
        
        // Et on redirige vers le tableau de bord, ni vu ni connu !
        header("Location: index.php?msg=suppression_prof_reussie");
        exit();

    } catch (Exception $e) {
        
        mysqli_rollback($conn);
        echo "<h3 style='color:red;'>Erreur imprévue lors de la suppression.</h3>";
        echo "<p>Détails : " . $e->getMessage() . "</p>";
    }

} else {
    echo "Aucun identifiant fourni.";
}
?>
