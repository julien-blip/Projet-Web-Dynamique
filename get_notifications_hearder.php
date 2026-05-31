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
$res = mysqli_stmt_get_result($stmt);

$notifs = [];
while ($row = mysqli_fetch_assoc($res)) {
    // Récupérer le nom de l'expéditeur
    $nom = 'Inconnu';
    if ($row['role_expediteur'] === 'Etudiant') {
        $s2 = mysqli_prepare($conn, "SELECT CONCAT(prenom,' ',nom) AS nom FROM ETUDIANT WHERE id_etudiant = ?");
    } else {
        $s2 = mysqli_prepare($conn, "SELECT CONCAT(prenom,' ',nom) AS nom FROM ENSEIGNANT WHERE id_enseignant = ?");
    }
    if ($s2) {
        mysqli_stmt_bind_param($s2, "i", $row['id_expediteur']);
        mysqli_stmt_execute($s2);
        $r2 = mysqli_stmt_get_result($s2);
        if ($n = mysqli_fetch_assoc($r2)) $nom = $n['nom'];
    }

    $notifs[] = [
        'id'    => $row['id_expediteur'],
        'role'  => $row['role_expediteur'],
        'nom'   => $nom,
        'nb'    => (int)$row['nb'],
        'apercu'=> mb_substr($row['apercu'], 0, 50) . (mb_strlen($row['apercu']) > 50 ? '…' : ''),
        'heure' => $row['dernier'],
    ];
}

echo json_encode($notifs);
