<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connect.php';

$prenoms = ["Alice", "Marc", "Sophie", "Jean", "Claire", "David", "Hélène", "Luc", "Marie", "Pierre"];
$noms = ["Dupont", "Lefevre", "Martin", "Rousseau", "Leroy", "Moreau", "Simon", "Laurent", "Michel", "Garcia"];
// On remplace les matières par des départements pour coller à ta base
$departements = ["Informatique", "Mathématiques", "Physique", "Génie Civil", "Électronique", "Langues"];

echo "<h2>Création des enseignants en cours...</h2>";

mysqli_begin_transaction($conn);
$erreurs = 0;

try {
    for ($i = 0; $i < 15; $i++) {
        $prenom = $prenoms[array_rand($prenoms)];
        $nom = $noms[array_rand($noms)];
       
        $email = strtolower($prenom . "." . $nom . rand(1, 99) . "@ecole.com");
        $tel = "06" . rand(10000000, 99999999);
        $mot_de_passe_hashe = password_hash("Prof2026!", PASSWORD_DEFAULT);
        $role = "Professeur";

        // 1. Création du compte (Ça ne change pas)
        $sql_compte = "INSERT INTO COMPTE_UTILISATEUR (email, mot_de_passe, role, telephone) VALUES (?, ?, ?, ?)";
        $stmt_compte = mysqli_prepare($conn, $sql_compte);
        mysqli_stmt_bind_param($stmt_compte, "ssss", $email, $mot_de_passe_hashe, $role, $tel);
        mysqli_stmt_execute($stmt_compte);
       
        $id_compte = mysqli_insert_id($conn);

        // 2. Création de l'enseignant (Adapté à tes colonnes exactes !)
        $departement_choisi = $departements[array_rand($departements)];

        // On insère uniquement dans id_compte, nom, prenom, et departement
        $sql_prof = "INSERT INTO ENSEIGNANT (id_compte, nom, prenom, departement) VALUES (?, ?, ?, ?)";
        $stmt_prof = mysqli_prepare($conn, $sql_prof);
       
        // "isss" = 1 Integer (id_compte) et 3 Strings (nom, prenom, departement)
        mysqli_stmt_bind_param($stmt_prof, "isss", $id_compte, $nom, $prenom, $departement_choisi);
        mysqli_stmt_execute($stmt_prof);
    }

    mysqli_commit($conn);
    echo "<h3 style='color: green;'>✅ Succès : 15 professeurs générés !</h3>";
    echo "<a href='index.php' style='padding: 10px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Retourner au tableau de bord</a>";

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "<h3 style='color: red;'>Erreur de génération.</h3>" . $e->getMessage();
}
?>



