<?php
require_once 'db_connect.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { echo "<p>ID invalide.</p>"; exit; }

$sql = "SELECT E.*, C.email, C.telephone 
        FROM ETUDIANT E 
        LEFT JOIN COMPTE_UTILISATEUR C ON E.id_compte = C.id_compte 
        WHERE E.id_etudiant = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$e = mysqli_fetch_assoc($res);

if (!$e) { echo "<p>Étudiant introuvable.</p>"; exit; }
?>

<h3><u><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></u></h3>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
  <div><strong>N° Étudiant :</strong><br><?= htmlspecialchars($e['numero_etudiant'] ?? 'N/A') ?></div>
  <div><strong>Email :</strong><br><?= htmlspecialchars($e['email'] ?? 'N/A') ?></div>
  <div><strong>Téléphone :</strong><br><?= htmlspecialchars($e['tel'] ?? 'N/A') ?></div>
  <div><strong>Date de naissance :</strong><br><?= htmlspecialchars($e['date_naissance'] ?? 'N/A') ?></div>
  <div><strong>Genre :</strong><br><?= htmlspecialchars($e['genre'] ?? 'N/A') ?></div>
  <div><strong>Adresse :</strong><br><?= htmlspecialchars($e['adresse'] ?? 'N/A') ?></div>
  <div><strong>Nationalité :</strong><br><?= htmlspecialchars($e['nationalite'] ?? 'N/A') ?></div>
  <div><strong>Niveau :</strong><br><?= htmlspecialchars($e['niveau'] ?? 'N/A') ?></div>
  <div><strong>Année académique :</strong><br><?= htmlspecialchars($e['annee_academique'] ?? 'N/A') ?></div>
  <div><strong>Statut :</strong><br><?= htmlspecialchars($e['statut'] ?? 'N/A') ?></div>
</div>

<div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
  <a href="modifier_etudiant.php?id=<?= $e['id_etudiant'] ?>" style="background: #0056b3; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;">Modifier</a>
  <a href="supprimer_etudiant.php?id=<?= $e['id_etudiant'] ?>" style="background: #ffcccc; color: #cc0000; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;" onclick="return confirm('Supprimer cet étudiant ?')">Supprimer</a>
</div>
