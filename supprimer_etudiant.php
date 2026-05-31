<?php
// Fichier : supprimer_etudiant.php
require_once 'db_connect.php';

// 1. On vérifie si un identifiant a bien été envoyé dans l'URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    // 2. SÉCURITÉ : On utilise intval() pour forcer la donnée à être un nombre entier.
    // Cela empêche un pirate d'écrire du texte dangereux dans l'URL.
    $id_etudiant = intval($_GET['id']);
    
    // 3. Préparation de la requête de suppression
    $sql = "DELETE FROM ETUDIANT WHERE id_etudiant = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        // "i" signifie qu'on attend un Integer (entier)
        mysqli_stmt_bind_param($stmt, "i", $id_etudiant);
        
        // 4. Exécution de la suppression
        if (mysqli_stmt_execute($stmt)) {
            // Si la suppression réussit, on redirige vers le tableau de bord
            header("Location: index.php?msg=suppression_reussie");
            exit();
        } else {
            echo "<h3 style='color:red;'>Erreur lors de la suppression.</h3>";
            echo "<p>" . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "Erreur de préparation SQL.";
    }

} else {
    // Si quelqu'un accède à la page sans cliquer sur le bouton (ex: en tapant juste l'URL)
    echo "Opération refusée : Aucun identifiant d'étudiant fourni.";
}
?>
