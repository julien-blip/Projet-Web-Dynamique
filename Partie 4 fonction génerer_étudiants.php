// Insertion dans ETUDIANT
        $sql_etu = "INSERT INTO etudiant (id_compte, numero_etudiant, nom, prenom, email_etu, date_naissance, genre, adresse, nationalite, niveau, annee_academique, statut) VALUES (?, ?, ?, ?, ?, ?, ?, 'Campus Central', ?, ?, ?, ?)";
        $stmt_etu = mysqli_prepare($conn, $sql_etu);
        mysqli_stmt_bind_param($stmt_etu, "issssssssss", $id_compte, $numero_etudiant, $nom, $prenom, $email, $date_naiss, $genre, $nationalite, $niveau, $annee, $statut);
        mysqli_stmt_execute($stmt_etu);
    }

    mysqli_commit($conn);
    echo "<div style='padding: 50px; font-family: sans-serif; text-align: center;'>";
    echo "<h2 style='color: green;'>✅ Base de données des étudiants mise à jour !</h2>";
    echo "<p>Les anciens profils ont été effacés. 25 nouveaux étudiants (ING1 à ING5) ont été créés.</p>";
    echo "<a href='index.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Retourner au tableau de bord</a>";
    echo "</div>";

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "<h3 style='color:red; text-align:center;'>Erreur critique : </h3>" . $e->getMessage();
}
?>
