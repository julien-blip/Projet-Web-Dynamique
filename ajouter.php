<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once 'db_connect.php';


if (isset($_POST["button_ajouter"])) {
    
   
    $nom_complet = trim($_POST["ajout_nom"]);
    $email       = trim($_POST["ajout_email"]);
    $date_naiss  = $_POST["ajout_date"]; 
    $tel         = trim($_POST["ajout_tel"]);
    $genre       = $_POST["ajout_genre"];
    $adresse     = trim($_POST["ajout_adresse"]);
    $nationalite = trim($_POST["ajout_nationalite"]);
    $filiere     = trim($_POST["ajout_filiere"]);
    $niveau      = trim($_POST["ajout_niveau"]);
    $annee       = trim($_POST["ajout_annee"]);
    $statut      = $_POST["ajout_statut"];

   
    $parties_nom = explode(" ", $nom_complet, 2);
    $prenom = isset($parties_nom[0]) ? $parties_nom[0] : "Inconnu";
    $nom = isset($parties_nom[1]) ? $parties_nom[1] : $prenom;

    $numero_etudiant = "E" . date("Y") . "-" . rand(1000, 9999);
    $mot_de_passe_clair = "SmartCampus2026!"; 
    $mot_de_passe_hashe = password_hash($mot_de_passe_clair, PASSWORD_DEFAULT);
    $role = "Etudiant";

    // 5. DÉBUT DE LA TRANSACTION
    mysqli_begin_transaction($conn);

    try {
       
        $sql_compte = "INSERT INTO COMPTE_UTILISATEUR (email, mot_de_passe, role, telephone) VALUES (?, ?, ?, ?)";
        $stmt_compte = mysqli_prepare($conn, $sql_compte);
        mysqli_stmt_bind_param($stmt_compte, "ssss", $email, $mot_de_passe_hashe, $role, $tel);
        mysqli_stmt_execute($stmt_compte);
        
        $id_compte_genere = mysqli_insert_id($conn);

        
        $sql_etudiant = "INSERT INTO ETUDIANT (id_compte, numero_etudiant, nom, prenom, date_naissance, genre, adresse, nationalite, filiere, niveau, annee_academique, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_etudiant = mysqli_prepare($conn, $sql_etudiant);
        mysqli_stmt_bind_param($stmt_etudiant, "isssssssssss", $id_compte_genere, $numero_etudiant, $nom, $prenom, $date_naiss, $genre, $adresse, $nationalite, $filiere, $niveau, $annee, $statut);
        mysqli_stmt_execute($stmt_etudiant);

      
        mysqli_commit($conn);

        header("Location: test.php?succes=1");
        exit();

    } catch (Exception $e) {
      
        mysqli_rollback($conn);
        echo "<h3 style='color: red;'>Erreur critique. Les données n'ont pas été enregistrées.</h3>";
        echo "<p>Détail de l'erreur : " . $e->getMessage() . "</p>";
        echo "<a href='test.html'>Retour au tableau de bord</a>";
    }
}
?>
