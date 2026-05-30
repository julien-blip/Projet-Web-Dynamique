<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

$id_moi   = isset($_POST['id_moi'])   ? intval($_POST['id_moi'])   : 0;
$role_moi = isset($_POST['role_moi']) ? $_POST['role_moi']         : '';

$resultat = [];
if ($id_moi > 0 && $role_moi !== '') {
    $sql = "SELECT id_expediteur, role_expediteur, COUNT(*) AS nb
            FROM messages
            WHERE id_destinataire = ? AND role_destinataire = ? AND lu = 0
            GROUP BY id_expediteur, role_expediteur";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $id_moi, $role_moi);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        // clé "role_id" pour retrouver facilement le contact côté JS
        $resultat[$r['role_expediteur'].'_'.$r['id_expediteur']] = intval($r['nb']);
    }
}
echo json_encode($resultat);
?>
