<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_sauvegarder_profil_prof'])) {
    $id_enseignant = intval($_POST['id_enseignant']);
    $tel    = trim($_POST['maj_tel_prof']);
    $adresse = trim($_POST['maj_adresse']);

    $sql = "UPDATE ENSEIGNANT SET tel_prof = ?, adresse = ? WHERE id_enseignant = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $tel, $adresse, $id_enseignant);

    if (mysqli_stmt_execute($stmt)) {
        // Retour sur la vue paramètres du prof
        header("Location: test.php?role_simule=Professeur&id=" . $id_enseignant . "&onglet=parametres");
        exit();
    } else {
        echo "Erreur lors de la mise à jour : " . mysqli_error($conn);
    }
}
?>
