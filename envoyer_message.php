<?php
require_once 'db_connect.php';

if (isset($_POST['message']) && isset($_POST['id_destinataire']) && isset($_POST['role_destinataire'])) {
    
    // Pour l'instant, on récupère l'expéditeur via les champs cachés (plus tard, ça viendra des sessions de connexion)
    $id_expediteur = intval($_POST['id_expediteur']);
    $role_expediteur = $_POST['role_expediteur'];
    
    $id_destinataire = intval($_POST['id_destinataire']);
    $role_destinataire = $_POST['role_destinataire'];
    $contenu = trim($_POST['message']);

    if (!empty($contenu)) {
        $sql = "INSERT INTO messages (id_expediteur, role_expediteur, id_destinataire, role_destinataire, contenu) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isiss", $id_expediteur, $role_expediteur, $id_destinataire, $role_destinataire, $contenu);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "success";
        } else {
            echo "Erreur BDD";
        }
    } else {
        echo "Message vide";
    }
} else {
    echo "Données manquantes";
}
?>
