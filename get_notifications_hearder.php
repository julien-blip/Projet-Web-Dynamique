?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$id_moi   = isset($_POST['id_moi'])   ? intval($_POST['id_moi'])   : 0;
$role_moi = isset($_POST['role_moi']) ? $_POST['role_moi']         : '';

if (!$id_moi || !$role_moi) { echo json_encode([]); exit; }

// Récupère les expéditeurs distincts + nb messages non lus + dernier message
$sql = "SELECT 
    m.id_expediteur,
    m.role_expediteur,
    COUNT(*) AS nb,
    MAX(m.date_envoi) AS dernier,
    MAX(m.contenu) AS apercu
FROM messages m
WHERE m.id_destinataire = ?
  AND m.role_destinataire = ?
  AND (m.lu = 0 OR m.lu IS NULL)
GROUP BY m.id_expediteur, m.role_expediteur
ORDER BY dernier DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) { echo json_encode([]); exit; }

mysqli_stmt_bind_param($stmt, "is", $id_moi, $role_moi);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt); ?>
