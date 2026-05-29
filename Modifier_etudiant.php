
<?php
require_once 'db_connect.php';

// --- PARTIE 1 : TRAITEMENT DE LA MISE À JOUR (Si le prof a cliqué sur "Enregistrer") ---
// Note : Le bouton a été renommé 'button_modifier' pour correspondre au traitement
if (isset($_POST['button_modifier'])) {
    $id_etudiant = intval($_POST['id_etudiant']); // Reçu grâce au input type="hidden"
    $nom = trim($_POST['modif_nom']);
    $email = trim($_POST['modif_email']);
    $date_naissance = $_POST['modif_date'];
    $telephone = trim($_POST['modif_tel']);
    $genre = $_POST['modif_genre'];
    $adresse = trim($_POST['modif_adresse']);
    $nationalite = $_POST['modif_nationalite'];
    $filiere = trim($_POST['modif_filiere']); // Niveau Actuel
    $groupe = trim($_POST['modif_groupe']);
    $annee_academique = $_POST['modif_annee'];
    $statut = $_POST['modif_statut'];

    // Requête UPDATE incluant tous les champs du formulaire
    $sql_update = "UPDATE ETUDIANT SET 
                    nom = ?, email = ?, date_naissance = ?, telephone = ?, 
                    genre = ?, adresse = ?, nationalite = ?, filiere = ?, 
                    groupe = ?, annee_academique = ?, statut = ? 
                   WHERE id_etudiant = ?";
                   
    $stmt = mysqli_prepare($conn, $sql_update);
    
    // "sssssssssssi" signifie 11 chaînes de caractères (string) et 1 entier (integer pour l'ID)
    mysqli_stmt_bind_param($stmt, "sssssssssssi", $nom, $email, $date_naissance, $telephone, $genre, $adresse, $nationalite, $filiere, $groupe, $annee_academique, $statut, $id_etudiant);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: test.php?msg=modification_reussie");
        exit();
    } else {
        echo "Erreur lors de la modification.";
    }
}

// --- PARTIE 2 : PRÉPARATION DU FORMULAIRE (Si on arrive depuis le bouton "Modifier") ---
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // On va chercher les infos de l'étudiant
    $sql_select = "SELECT * FROM ETUDIANT WHERE id_etudiant = ?";
    $stmt_select = mysqli_prepare($conn, $sql_select);
    mysqli_stmt_bind_param($stmt_select, "i", $id);
    mysqli_stmt_execute($stmt_select);
    $resultat = mysqli_stmt_get_result($stmt_select);
    
    $etudiant = mysqli_fetch_assoc($resultat);
    
    if (!$etudiant) {
        die("Étudiant introuvable.");
    }
} else {
    die("Aucun étudiant sélectionné.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Modifier un étudiant</title>
  <link rel="stylesheet" href="style.css">
</head>
<body style="padding: 50px; justify-content: center;">

  <div class="struct" style="max-width: 600px; margin: auto;">
    <div class="haut1">
      <div class="gauche"><strong>Modifier les infos de l'étudiant</strong></div>
      <div class="droite"><a href="test.php" class="btn-submit" style="text-decoration: none; background: #CDCDCD; color: black;">Annuler</a></div>
    </div>

    <form method="post" action="">
          
          <input type="hidden" name="id_etudiant" value="<?= $etudiant['id_etudiant'] ?>">

          <div class="form-container">
            <div class="struct">
              <h3>Informations personnelles</h3>
              
              <div class="input-group">
                <label for="nom">Nom Complet</label>
                <input type="text" id="nom" name="modif_nom" value="<?= htmlspecialchars($etudiant['nom']) ?>" required>
              </div>
              
              <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="modif_email" value="<?= htmlspecialchars($etudiant['email']) ?>" required>
              </div>
              
              <div class="input-group">
                <label for="date_naissance">Date de naissance</label>
                <input type="date" id="date_naissance" name="modif_date" value="<?= $etudiant['date_naissance'] ?>" required>
              </div>
              
              <div class="input-group">
                <label for="telephone">Téléphone</label>
                <input type="tel" id="telephone" name="modif_tel" value="<?= htmlspecialchars($etudiant['telephone']) ?>" required>
              </div>
              
              <div class="input-group">
                <label for="genre">Genre</label>
                <select id="genre" name="modif_genre" required>
                  <option value="">-- Sélectionner --</option>
                  <option value="Homme" <?= $etudiant['genre'] == 'Homme' ? 'selected' : '' ?>>Homme</option>
                  <option value="Femme" <?= $etudiant['genre'] == 'Femme' ? 'selected' : '' ?>>Femme</option>
                  <option value="Autre" <?= $etudiant['genre'] == 'Autre' ? 'selected' : '' ?>>Autre</option>
                </select>
              </div>
              
              <div class="input-group">
                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" name="modif_adresse" value="<?= htmlspecialchars($etudiant['adresse']) ?>" required>
              </div>
              
              <div class="input-group">
                <label for="nationalite">Nationalité</label>
                <select id="nationalite" name="modif_nationalite" required>
                  <option value="">-- Sélectionner --</option>
                  <option value="Française" <?= $etudiant['nationalite'] == 'Française' ? 'selected' : '' ?>>Française</option>
                  <option value="Algérienne" <?= $etudiant['nationalite'] == 'Algérienne' ? 'selected' : '' ?>>Algérienne</option>
                  <option value="Allemande" <?= $etudiant['nationalite'] == 'Allemande' ? 'selected' : '' ?>>Allemande</option>
                  <option value="Américaine" <?= $etudiant['nationalite'] == 'Américaine' ? 'selected' : '' ?>>Américaine</option>
                  <option value="Belge" <?= $etudiant['nationalite'] == 'Belge' ? 'selected' : '' ?>>Belge</option>
                  <option value="Béninoise" <?= $etudiant['nationalite'] == 'Béninoise' ? 'selected' : '' ?>>Béninoise</option>
                  <option value="Britannique" <?= $etudiant['nationalite'] == 'Britannique' ? 'selected' : '' ?>>Britannique</option>
                  <option value="Burkinabè" <?= $etudiant['nationalite'] == 'Burkinabè' ? 'selected' : '' ?>>Burkinabè</option>
                  <option value="Camerounaise" <?= $etudiant['nationalite'] == 'Camerounaise' ? 'selected' : '' ?>>Camerounaise</option>
                  <option value="Canadienne" <?= $etudiant['nationalite'] == 'Canadienne' ? 'selected' : '' ?>>Canadienne</option>
                  <option value="Chinoise" <?= $etudiant['nationalite'] == 'Chinoise' ? 'selected' : '' ?>>Chinoise</option>
                  <option value="Congolaise" <?= $etudiant['nationalite'] == 'Congolaise' ? 'selected' : '' ?>>Congolaise</option>
                  <option value="Espagnole" <?= $etudiant['nationalite'] == 'Espagnole' ? 'selected' : '' ?>>Espagnole</option>
                  <option value="Ivoirienne" <?= $etudiant['nationalite'] == 'Ivoirienne' ? 'selected' : '' ?>>Ivoirienne</option>
                  <option value="Italienne" <?= $etudiant['nationalite'] == 'Italienne' ? 'selected' : '' ?>>Italienne</option>
                  <option value="Japonaise" <?= $etudiant['nationalite'] == 'Japonaise' ? 'selected' : '' ?>>Japonaise</option>
                  <option value="Malienne" <?= $etudiant['nationalite'] == 'Malienne' ? 'selected' : '' ?>>Malienne</option>
                  <option value="Marocaine" <?= $etudiant['nationalite'] == 'Marocaine' ? 'selected' : '' ?>>Marocaine</option>
                  <option value="Sénégalaise" <?= $etudiant['nationalite'] == 'Sénégalaise' ? 'selected' : '' ?>>Sénégalaise</option>
                  <option value="Suisse" <?= $etudiant['nationalite'] == 'Suisse' ? 'selected' : '' ?>>Suisse</option>
                  <option value="Togolaise" <?= $etudiant['nationalite'] == 'Togolaise' ? 'selected' : '' ?>>Togolaise</option>
                  <option value="Tunisienne" <?= $etudiant['nationalite'] == 'Tunisienne' ? 'selected' : '' ?>>Tunisienne</option>
                </select>
              </div>
            </div>
            
            <div class="struct">
              <h3>Informations académiques</h3>
              
              <div class="input-group">
                <label for="filiere">Niveau Actuel</label>
                <select id="filiere" name="modif_filiere" required>
                  <option value="">-- Sélectionner --</option>
                  <option value="ING1" <?= $etudiant['filiere'] == 'ING1' ? 'selected' : '' ?>>ING1</option>
                  <option value="ING2" <?= $etudiant['filiere'] == 'ING2' ? 'selected' : '' ?>>ING2</option>
                  <option value="ING3" <?= $etudiant['filiere'] == 'ING3' ? 'selected' : '' ?>>ING3</option>
                  <option value="ING4" <?= $etudiant['filiere'] == 'ING4' ? 'selected' : '' ?>>ING4</option>
                  <option value="ING5" <?= $etudiant['filiere'] == 'ING5' ? 'selected' : '' ?>>ING5</option>
                </select>
              </div>

              <div class="input-group">
                <label for="niveau">Groupe</label>
                <select id="niveau" name="modif_groupe" required>
                  <option value="">-- Sélectionner --</option>
                </select>
              </div>

              <div class="input-group">
                <label for="annee">Année académique</label>
                <select id="annee" name="modif_annee" required>
                  <option value="">-- Sélectionner --</option>
                  <option value="2025-2026" <?= $etudiant['annee_academique'] == '2025-2026' ? 'selected' : '' ?>>2025 - 2026</option>
                  <option value="2024-2025" <?= $etudiant['annee_academique'] == '2024-2025' ? 'selected' : '' ?>>2024 - 2025</option>
                  <option value="2023-2024" <?= $etudiant['annee_academique'] == '2023-2024' ? 'selected' : '' ?>>2023 - 2024</option>
                  <option value="2022-2023" <?= $etudiant['annee_academique'] == '2022-2023' ? 'selected' : '' ?>>2022 - 2023</option>
                  <option value="2021-2022" <?= $etudiant['annee_academique'] == '2021-2022' ? 'selected' : '' ?>>2021 - 2022</option>
                </select>
              </div>
              
              <div class="input-group">
                <label for="statut">Statut</label>
                <select id="statut" name="modif_statut" required>
                  <option value="">-- Sélectionner --</option>
                  <option value="actif" <?= $etudiant['statut'] == 'actif' ? 'selected' : '' ?>>Actif</option>
                  <option value="inactif" <?= $etudiant['statut'] == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                </select>
              </div>
              
              <button type="submit" name="button_modifier" class="btn-submit">Enregistrer les modifications</button>
            </div>
          </div>
        </form>
  </div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    
    const groupesParNiveau = {
      "ING1": ["Grp1", "Grp2", "Grp3", "Grp4", "Grp5", "Grp6", "Grp7", "Grp8", "Grp9", "Grp10", "Grp11", "Grp12", "Grp13"],
      "ING2": ["Grp1", "Grp2", "Grp3", "Grp4", "Grp5", "Grp6", "Grp7", "Grp8", "Grp9", "Grp10", "Grp11", "Grp12", "Grp13"],
      "ING3": ["Grp1", "Grp2", "Grp3", "Grp4", "Grp5", "Grp6", "Grp7", "Grp8", "Grp9", "Grp10", "Grp11", "Grp12", "Grp13"],
      "ING4": ["Systeme Embarqué - Grp1", "Systeme Embarqué - Grp2", "Finance - Grp1", "Finance - Grp2", "Energie - Grp1", "Energie - Grp2", "Cyber - Grp1", "Cyber - Grp2"],
      "ING5": ["Systeme Embarqué - Grp1", "Systeme Embarqué - Grp2", "Finance - Grp1", "Finance - Grp2", "Energie - Grp1", "Energie - Grp2", "Cyber - Grp1", "Cyber - Grp2"]
    };

    const selectFiliere = document.getElementById('filiere');
    const selectNiveau = document.getElementById('niveau');
    
    // On mémorise le groupe actuellement enregistré en BDD pour cet étudiant
    const groupeActuel = "<?= isset($etudiant['groupe']) ? $etudiant['groupe'] : '' ?>";

    function mettreAJourGroupes(niveauChoisi, groupeSelectionne) {
      selectNiveau.innerHTML = '<option value="">-- Sélectionner --</option>';

      if (niveauChoisi && groupesParNiveau[niveauChoisi]) {
        const groupes = groupesParNiveau[niveauChoisi];

        groupes.forEach(function(groupe) {
          const nouvelleOption = document.createElement('option');
          nouvelleOption.value = groupe;
          nouvelleOption.textContent = groupe;
          
          // Si le groupe correspond à celui de la BDD, on le sélectionne par défaut
          if (groupe === groupeSelectionne) {
            nouvelleOption.selected = true;
          }
          
          selectNiveau.appendChild(nouvelleOption);
        });
      }
    }

    // Écouteur pour les changements de l'utilisateur
    selectFiliere.addEventListener('change', function() {
      mettreAJourGroupes(this.value, '');
    });

    // Initialisation au chargement de la page pour afficher le groupe actuel de l'étudiant
    if (selectFiliere.value) {
        mettreAJourGroupes(selectFiliere.value, groupeActuel);
    }
  });
</script>

</body>
</html>
