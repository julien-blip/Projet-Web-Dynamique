<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';

if (isset($_POST["button_ajouter_prof"])) {
    
    // Récupération des données du formulaire
    $nom = trim($_POST["ajout_nom"]);
    $prenom = trim($_POST["ajout_prenom"]);
    $email = trim($_POST["ajout_email"]);
    $tel = trim($_POST["ajout_tel"]);
    $departement = $_POST["ajout_departement"];

    // Paramètres par défaut
    $mot_de_passe_clair = "Prof2026!"; 
    $mot_de_passe_hashe = password_hash($mot_de_passe_clair, PASSWORD_DEFAULT);
    $role = "Professeur";

    //  Début de la Transaction 
    mysqli_begin_transaction($conn);

    try {
        //  Création du compte utilisateur
        $sql_compte = "INSERT INTO COMPTE_UTILISATEUR (email, mot_de_passe, role, telephone) VALUES (?, ?, ?, ?)";
        $stmt_compte = mysqli_prepare($conn, $sql_compte);
        mysqli_stmt_bind_param($stmt_compte, "ssss", $email, $mot_de_passe_hashe, $role, $tel);
        mysqli_stmt_execute($stmt_compte);
        
        $id_compte = mysqli_insert_id($conn); // On récupère l'ID du compte créé

        //  Création du profil enseignant 
        $sql_prof = "INSERT INTO ENSEIGNANT (id_compte, nom, prenom, departement) VALUES (?, ?, ?, ?)";
        $stmt_prof = mysqli_prepare($conn, $sql_prof);
        mysqli_stmt_bind_param($stmt_prof, "isss", $id_compte, $nom, $prenom, $departement);
        mysqli_stmt_execute($stmt_prof);

        //  On valide l'enregistrement
        mysqli_commit($conn);

        //  Retour au tableau de bord
        header("Location: test.php?succes_ajout_prof=1");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<h3 style='color: red;'>Erreur critique.</h3>";
        echo "<p>Détail de l'erreur : " . $e->getMessage() . "</p>";
        echo "<a href='test.php'>Retour au tableau de bord</a>";
    }
}
?>
