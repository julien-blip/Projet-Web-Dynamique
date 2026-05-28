<?php
// Fichier : modifier_cours.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connect.php';

// --- 1. TRAITEMENT DE LA MODIFICATION (Quand on valide le formulaire) ---
if (isset($_POST['button_modifier_cours'])) {
    $id_cours = intval($_POST['id_cours']);
    $titre = trim($_POST['modif_titre']);
    $code = trim($_POST['modif_code']);
    $departement = trim($_POST['modif_dep']);
    $niveau = trim($_POST['modif_niveau']);
    $semestre = $_POST['modif_semestre'];
    $categorie = $_POST['modif_categorie'];
    
    // Si on a choisi "Aucun enseignant", on met NULL dans la base
    $id_enseignant = !empty($_POST['modif_prof']) ? intval($_POST['modif_prof']) : NULL;

    $sql_update = "UPDATE COURS SET nom_matiere = ?, code_cours = ?, departement = ?, niveau = ?, semestre = ?, categorie = ?, id_enseignant = ? WHERE id_cours = ?";
    $stmt = mysqli_prepare($conn, $sql_update);
    
    // "ssssssii" = 6 strings, 2 integers
    mysqli_stmt_bind_param($stmt, "ssssssii", $titre, $code, $departement, $niveau, $semestre, $categorie, $id_enseignant, $id_cours);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: test.php?msg=modification_cours_reussie");
        exit();
    } else {
        echo "Erreur lors de la modification : " . mysqli_error($conn);
    }
}

// --- 2. RÉCUPÉRATION DES DONNÉES DU COURS (Pour pré-remplir) ---
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql_select = "SELECT * FROM COURS WHERE id_cours = ?";
    $stmt_select = mysqli_prepare($conn, $sql_select);
    mysqli_stmt_bind_param($stmt_select, "i", $id);
    mysqli_stmt_execute($stmt_select);
    $resultat = mysqli_stmt_get_result($stmt_select);
    
    $cours = mysqli_fetch_assoc($resultat);
    
    if (!$cours) {
        die("Cours introuvable dans la base de données.");
    }
} else {
    die("Aucun cours sélectionné.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Modifier un cours</title>
  <link rel="stylesheet" href="style.css">
  <style>
      .input-group { margin-bottom: 15px; display: flex; flex-direction: column; }
      .input-group label { margin-bottom: 5px; font-weight: bold; color: #555; }
      .input-group input, .input-group select { padding: 10px; border: 1px solid #CCC; border-radius: 5px; }
  </style>
</head>
<body style="padding: 50px; justify-content: center; background: #DDDDDD;">

  <div class="struct" style="max-width: 600px; margin: auto; background: #FFFFFF; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    
    <div class="haut1" style="border-bottom: 2px solid #EEEEEE; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between;">
      <div class="gauche" style="font-size: 20px;"><strong>Modifier le cours</strong></div>
      <div class="droite">
        <a href="test.php" style="padding: 8px 15px; background: #CDCDCD; color: black; text-decoration: none; border-radius: 5px; font-weight: bold;">Annuler</a>
      </div>
    </div>

    <form method="post" action="modifier_cours.php">
      
      <input type="hidden" name="id_cours" value="<?php echo $cours['id_cours']; ?>">

      <div class="input-group">
        <label>Titre du cours</label>
        <input type="text" name="modif_titre" value="<?php echo htmlspecialchars($cours['nom_matiere']); ?>" required>
      </div>

      <div class="input-group">
        <label>Code du cours</label>
        <input type="text" name="modif_code" value="<?php echo htmlspecialchars($cours['code_cours']); ?>" required>
      </div>

      <div class="input-group">
        <label>Département</label>
        <input type="text" name="modif_dep" value="<?php echo htmlspecialchars($cours['departement']); ?>" required>
      </div>

      <div class="input-group">
        <label>Niveau (ex: L1, L2)</label>
        <input type="text" name="modif_niveau" value="<?php echo htmlspecialchars($cours['niveau']); ?>" required>
      </div>

      <div style="display: flex; gap: 20px;">
          <div class="input-group" style="flex: 1;">
            <label>Semestre</label>
            <select name="modif_semestre" required>
              <option value="S1" <?php if($cours['semestre'] == 'S1') echo 'selected'; ?>>Semestre 1</option>
              <option value="S2" <?php if($cours['semestre'] == 'S2') echo 'selected'; ?>>Semestre 2</option>
              <option value="S3" <?php if($cours['semestre'] == 'S3') echo 'selected'; ?>>Semestre 3</option>
              <option value="S4" <?php if($cours['semestre'] == 'S4') echo 'selected'; ?>>Semestre 4</option>
              <option value="S5" <?php if($cours['semestre'] == 'S5') echo 'selected'; ?>>Semestre 5</option>
              <option value="S6" <?php if($cours['semestre'] == 'S6') echo 'selected'; ?>>Semestre 6</option>
            </select>
          </div>

          <div class="input-group" style="flex: 1;">
            <label>Catégorie</label>
            <select name="modif_categorie" required>
              <option value="tronc_commun" <?php if($cours['categorie'] == 'tronc_commun') echo 'selected'; ?>>Tronc commun</option>
              <option value="optionnel" <?php if($cours['categorie'] == 'optionnel') echo 'selected'; ?>>Optionnel</option>
              <option value="specialite" <?php if($cours['categorie'] == 'specialite') echo 'selected'; ?>>Spécialité</option>
            </select>
          </div>
      </div>

      <div class="input-group" style="margin-bottom: 20px;">
        <label>Enseignant assigné</label>
        <select name="modif_prof">
          <option value="">-- Aucun enseignant --</option>
          <?php
          // On va chercher la liste de TOUS les profs
          $sql_profs = "SELECT id_enseignant, nom, prenom FROM ENSEIGNANT ORDER BY nom ASC";
          $resultat_profs = mysqli_query($conn, $sql_profs);
          
          while ($prof = mysqli_fetch_assoc($resultat_profs)) {
              // On vérifie si ce prof est celui qui donne déjà ce cours pour le pré-sélectionner
              $selected = ($prof['id_enseignant'] == $cours['id_enseignant']) ? 'selected' : '';
              echo '<option value="' . $prof['id_enseignant'] . '" ' . $selected . '>' . htmlspecialchars($prof['nom'] . ' ' . $prof['prenom']) . '</option>';
          }
          ?>
        </select>
      </div>

      <button type="submit" name="button_modifier_cours" style="width: 100%; background: #0056b3; color: white; padding: 12px; border: none; border-radius: 7px; font-weight: bold; cursor: pointer; font-size: 16px;">
        Enregistrer les modifications
      </button>
    </form>
    
  </div>

</body>
</html>
