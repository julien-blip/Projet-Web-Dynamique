<?php
// Fichier : modifier_prof.php
require_once 'db_connect.php';

// --- 1. TRAITEMENT DE LA MODIFICATION (Quand on clique sur Enregistrer) ---
if (isset($_POST['button_modifier_prof'])) {
    $id_enseignant = intval($_POST['id_enseignant']);
    $nom = trim($_POST['modif_nom']);
    $prenom = trim($_POST['modif_prenom']);
    $departement = trim($_POST['modif_departement']);

    // On met à jour les 3 seules colonnes modifiables de ta table
    $sql_update = "UPDATE ENSEIGNANT SET nom = ?, prenom = ?, departement = ? WHERE id_enseignant = ?";
    $stmt = mysqli_prepare($conn, $sql_update);
    
    // "sssi" = String, String, String, Integer
    mysqli_stmt_bind_param($stmt, "sssi", $nom, $prenom, $departement, $id_enseignant);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: test.php?msg=modification_prof_reussie");
        exit();
    } else {
        echo "Erreur lors de la modification : " . mysqli_error($conn);
    }
}

// --- 2. RÉCUPÉRATION DES DONNÉES (Pour pré-remplir les cases) ---
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql_select = "SELECT * FROM ENSEIGNANT WHERE id_enseignant = ?";
    $stmt_select = mysqli_prepare($conn, $sql_select);
    mysqli_stmt_bind_param($stmt_select, "i", $id);
    mysqli_stmt_execute($stmt_select);
    $resultat = mysqli_stmt_get_result($stmt_select);
    
    $prof = mysqli_fetch_assoc($resultat);
    
    if (!$prof) {
        die("Enseignant introuvable dans la base de données.");
    }
} else {
    die("Aucun enseignant sélectionné.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Modifier un enseignant</title>
  <link rel="stylesheet" href="style.css">
</head>
<body style="padding: 50px; justify-content: center; background: #DDDDDD;">

  <div class="struct" style="max-width: 600px; margin: auto; background: #FFFFFF; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    
    <div class="haut1" style="border-bottom: 2px solid #EEEEEE; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between;">
      <div class="gauche" style="font-size: 20px;"><strong>Modifier l'enseignant</strong></div>
      <div class="droite">
        <a href="test.php" style="padding: 8px 15px; background: #CDCDCD; color: black; text-decoration: none; border-radius: 5px; font-weight: bold;">Annuler</a>
      </div>
    </div>

    <form method="post" action="modifier_prof.php">
      
      <input type="hidden" name="id_enseignant" value="<?php echo $prof['id_enseignant']; ?>">

      <div class="input-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
        <label style="margin-bottom: 5px; font-weight: bold; color: #555;">Nom</label>
        <input type="text" name="modif_nom" value="<?php echo htmlspecialchars($prof['nom']); ?>" style="padding: 10px; border: 1px solid #CCC; border-radius: 5px;" required>
      </div>

      <div class="input-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
        <label style="margin-bottom: 5px; font-weight: bold; color: #555;">Prénom</label>
        <input type="text" name="modif_prenom" value="<?php echo htmlspecialchars($prof['prenom']); ?>" style="padding: 10px; border: 1px solid #CCC; border-radius: 5px;" required>
      </div>

      <div class="input-group" style="margin-bottom: 20px; display: flex; flex-direction: column;">
        <label style="margin-bottom: 5px; font-weight: bold; color: #555;">Département</label>
        <input type="text" name="modif_departement" value="<?php echo htmlspecialchars($prof['departement']); ?>" style="padding: 10px; border: 1px solid #CCC; border-radius: 5px;" required>
      </div>

      <button type="submit" name="button_modifier_prof" style="width: 100%; background: #0056b3; color: white; padding: 12px; border: none; border-radius: 7px; font-weight: bold; cursor: pointer; font-size: 16px;">
        Enregistrer les modifications
      </button>
    </form>
    
  </div>

</body>
</html>
