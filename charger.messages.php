<?php
require_once 'db_connect.php';
if (isset($_POST['id_expediteur']) && isset($_POST['id_destinataire'])) {
$id_moi = intval($_POST['id_expediteur']);
    $role_moi = $_POST['role_expediteur'];
    $id_lui = intval($_POST['id_destinataire']);
    $role_lui = $_POST['role_destinataire'];

    $sql = "SELECT * FROM messages 
            WHERE (id_expediteur = ? AND role_expediteur = ? AND id_destinataire = ? AND role_destinataire = ?)
               OR (id_expediteur = ? AND role_expediteur = ? AND id_destinataire = ? AND role_destinataire = ?)
            ORDER BY date_envoi ASC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isisisis", $id_moi, $role_moi, $id_lui, $role_lui, $id_lui, $role_lui, $id_moi, $role_moi);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
}
?>
