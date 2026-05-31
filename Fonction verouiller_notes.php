<?php
require_once 'db_connect.php';

$id_cours  = isset($_GET['id_cours']) ? intval($_GET['id_cours']) : 0;
$type_eval = isset($_GET['type_eval']) ? $_GET['type_eval'] : '';
$id_prof   = isset($_GET['id_prof']) ? intval($_GET['id_prof']) : 0;

if ($id_cours > 0 && $type_eval != '') {
    // On ajoute l'entrée dans la table de verrouillage
    $stmt = mysqli_prepare($conn, "INSERT IGNORE INTO verrouillage_notes (id_cours, evaluation) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "is", $id_cours, $type_eval);
    mysqli_stmt_execute($stmt);
}

// On redirige vers la page du prof
header("Location: index.php?role_simule=Professeur&id={$id_prof}&onglet=notes&id_cours_prof={$id_cours}&type_eval=" . urlencode($type_eval));
exit();
?>
