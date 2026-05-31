<?php
// Fichier : supprimer_cours.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
   
    $id_cours = intval($_GET['id']);
   
    try {
        $sql = "DELETE FROM COURS WHERE id_cours = ?";
        $stmt = mysqli_prepare($conn, $sql);
       
        mysqli_stmt_bind_param($stmt, "i", $id_cours);
        mysqli_stmt_execute($stmt);
       
        // Redirection vers le tableau de bord
        header("Location: index.php?msg=suppression_cours_reussie");
        exit();

    } catch (Exception $e) {
        // En cas de blocage (ex: il y a déjà des notes pour ce cours)
        echo "<div style='font-family: sans-serif; padding: 20px;'>";
        echo "<h3 style='color:red;'>Impossible de supprimer ce cours !</h3>";
        echo "<p><strong>Raison :</strong> " . $e->getMessage() . "</p>";
        echo "<a href='index.php' style='padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Retour</a>";
        echo "</div>";
    }

} else {
    echo "Aucun identifiant de cours fourni.";
}
?>
