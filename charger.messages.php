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
    $messages_html = "";
    while ($msg = mysqli_fetch_assoc($resultat)) {
        // Si c'est moi l'expéditeur, le message s'affiche en bleu à droite ("envoye")
        if ($msg['id_expediteur'] == $id_moi && $msg['role_expediteur'] == $role_moi) {
            $classe = "envoye";
        } else {
            // Sinon il s'affiche en gris à gauche ("recu")
            $classe = "recu";
        }
        
        $heure = date('d/m/Y H:i', strtotime($msg['date_envoi']));
        
        $messages_html .= '<div class="message ' . $classe . '">';
        $messages_html .= nl2br(htmlspecialchars($msg['contenu']));
        $messages_html .= '<div style="font-size: 10px; opacity: 0.6; margin-top: 5px; text-align: right;">' . $heure . '</div>';
        $messages_html .= '</div>';
    }
    if (empty($messages_html)) {
        echo '<div style="text-align: center; color: #999; margin-top: 20px;">Aucun message. Dites bonjour !</div>';
    } else {
        echo $messages_html;
    }
}
?>
