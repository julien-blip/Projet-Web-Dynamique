<?php
require_once 'db_connect.php';

$id_note  = intval($_GET['id_note']  ?? 0);
$id_prof  = intval($_GET['id_prof']  ?? 0);
$id_cours = intval($_GET['id_cours'] ?? 0);
$type_eval = $_GET['type_eval'] ?? '';

if ($id_note > 0) {
    $stmt = mysqli_prepare($conn, "DELETE FROM note WHERE id_note = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_note);
    mysqli_stmt_execute($stmt);
}

header("Location: index.php?role_simule=Professeur&id={$id_prof}"
    . "&onglet=notes&id_cours_prof={$id_cours}"
    . "&type_eval=" . urlencode($type_eval));
exit();
?>
