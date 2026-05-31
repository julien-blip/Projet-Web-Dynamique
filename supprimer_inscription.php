<?php
// Fichier : supprimer_inscription.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connect.php';

if (isset($_GET['id_etu']) && isset($_GET['id_cours'])) {
    $id_etu = intval($_GET['id_etu']);
    $id_cours = intval($_GET['id_cours']);

    $sql = "DELETE FROM INSCRIPTION WHERE id_etudiant = ? AND id_cours = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id_etu, $id_cours);

    if (mysqli_stmt_execute($stmt)) {
        // L'astuce "?onglet=inscriptions" permet de rouvrir la page automatiquement
        header("Location: index.php?onglet=inscriptions");
        exit();
    } else {
        echo "Erreur lors de la désinscription : " . mysqli_error($conn);
    }
} else {
    echo "Informations manquantes.";
}
?>
