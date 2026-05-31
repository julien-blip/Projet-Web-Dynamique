try {
    // 1. Nettoyage total et sécurisé (On efface d'abord ce qui est lié)
    mysqli_query($conn, "DELETE FROM inscription");
    mysqli_query($conn, "DELETE FROM note");
    mysqli_query($conn, "DELETE FROM presence");
    mysqli_query($conn, "DELETE FROM etudiant");
    mysqli_query($conn, "DELETE FROM compte_utilisateur WHERE role = 'Etudiant'");
    //Supprime les enfants avant les parents (contraintes FK)

    // 2. Banques de données pour la génération
    $prenoms = ["Emma", "Lucas", "Léa", "Louis", "Chloé", "Gabriel", "Manon", "Jules", "Camille", "Hugo", "Alice", "Arthur", "Juliette", "Paul", "Zoé"];
    $noms = ["Martin", "Bernard", "Thomas", "Petit", "Robert", "Richard", "Durand", "Dubois", "Moreau", "Laurent", "Simon", "Michel"];
    $nationalites = ["Française", "Belge", "Suisse", "Marocaine", "Sénégalaise", "Canadienne"];
    $niveaux = ["ING1", "ING2", "ING3", "ING4", "ING5"];
   
    // Mot de passe par défaut : "SmartCampus2026!"
    $mot_de_passe = password_hash("SmartCampus2026!", PASSWORD_DEFAULT);
    //Données sources + hachage sécurisé du mot de passe commun
