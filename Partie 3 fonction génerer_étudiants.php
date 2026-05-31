for ($i = 0; $i < 25; $i++) {
        $prenom = $prenoms[array_rand($prenoms)];
        $nom = $noms[array_rand($noms)];
        $email = strtolower($prenom . "." . $nom . $i . "@smartcampus.fr");
        $tel = "06" . rand(10000000, 99999999);
        $date_naiss = rand(2002, 2005) . "-" . str_pad(rand(1, 12), 2, "0", STR_PAD_LEFT) . "-" . str_pad(rand(1, 28), 2, "0", STR_PAD_LEFT);
        $genre = (rand(0, 1) == 0) ? "Femme" : "Homme";
        $nationalite = $nationalites[array_rand($nationalites)];
        $niveau = $niveaux[array_rand($niveaux)];
       
        $numero_etudiant = "E" . date("Y") . "-" . rand(1000, 9999);
        $annee = "2025-2026";
        $statut = "actif";

        // Insertion dans COMPTE_UTILISATEUR
        $sql_compte = "INSERT INTO compte_utilisateur (email, mot_de_passe, role, telephone) VALUES (?, ?, 'Etudiant', ?)";
        $stmt_compte = mysqli_prepare($conn, $sql_compte);
        mysqli_stmt_bind_param($stmt_compte, "sss", $email, $mot_de_passe, $tel);
        mysqli_stmt_execute($stmt_compte);
       
        $id_compte = mysqli_insert_id($conn);

        // INSERT INTO etudiant avec $id_compte comme clé étrangère
