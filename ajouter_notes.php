<?php

require_once 'db_connect.php';

if (isset($_POST['Enregistrer_notes'])) {
    
    $id_cours = $_POST['id_cours'];
    $evaluation = $_POST['type_evaluation'];
    
   
    $tableau_notes = $_POST['notes']; 
    $tableau_commentaires = $_POST['commentaires'];
    
    mysqli_begin_transaction($conn);

    try {
      
        $sql = "INSERT INTO NOTE (evaluation, note, commentaire, id_etudiant, id_cours) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        
        foreach ($tableau_notes as $id_etudiant => $valeur_note) {
            
            if ($valeur_note !== "" && $valeur_note !== null) {
               
                $commentaire = !empty($tableau_commentaires[$id_etudiant]) ? $tableau_commentaires[$id_etudiant] : NULL;
                
              
                mysqli_stmt_bind_param($stmt, "sdsii", $evaluation, $valeur_note, $commentaire, $id_etudiant, $id_cours);
                
                mysqli_stmt_execute($stmt);
            }
        }

        mysqli_commit($conn);
        header("Location: test.php?succes_notes=1");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
} else {
    echo "Accès non autorisé.";
}
?>
