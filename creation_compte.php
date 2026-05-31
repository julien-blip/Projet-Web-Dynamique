<?php
require_once 'db_connect.php';
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['inscription'])) {
    $email = trim($_POST['email']);
    $mdp = $_POST['mdp'];
    $role = "Etudiant"; // Rôle par défaut
    $mdp_hache = password_hash($mdp, PASSWORD_DEFAULT);
    $verif_stmt = $conn->prepare("SELECT id_compte FROM compte_utilisateur WHERE email = ?");
    $verif_stmt->bind_param("s", $email);
    $verif_stmt->execute();
    $verif_stmt->store_result();
    if ($verif_stmt->num_rows > 0) {
        $message = "<span style='color:#cc0000;'>Cet email est déjà utilisé.</span>";
    } else {
        $stmt = $conn->prepare("INSERT INTO compte_utilisateur (email, mot_de_passe, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $mdp_hache, $role);
        if ($stmt->execute()) {
            $message = "<span style='color:#28a745;'>Compte créé avec succès ! <a href='connexion.php' style='color:#d62828;font-weight:bold;'>Connectez-vous ici</a>.</span>";
        } else {
            $message = "<span style='color:#cc0000;'>Erreur lors de la création du compte.</span>";
        }
        $stmt->close();
    }
    $verif_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Inscription - Smart Campus</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #d62828;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .carte-connexion {
      display: flex;
      width: 100%;
      max-width: 1000px;
      min-height: 560px;
      background: #ffffff;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    }

    .zone-form {
      flex: 1;
      padding: 50px 45px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .zone-form h2 {
      font-size: 28px;
      color: #d62828;
      margin-bottom: 8px;
    }

    .zone-form .sous-titre {
      color: #888;
      font-size: 14px;
      margin-bottom: 30px;
    }

    .champ { margin-bottom: 20px; }

    .champ label {
      display: block;
      font-size: 13px;
      font-weight: bold;
      color: #444;
      margin-bottom: 6px;
    }

    .champ input {
      width: 100%;
      padding: 13px 15px;
      border: 1px solid #ddd;
      border-radius: 10px;
      font-size: 14px;
      outline: none;
      transition: border 0.2s;
    }

    .champ input:focus { border-color: #d62828; }

    .btn-connexion {
      width: 100%;
      padding: 14px;
      background: #d62828;
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 15px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 10px;
      transition: background 0.2s;
    }

    .btn-connexion:hover { background: #b21e1e; }

    .message {
      background: #f4f4f4;
      padding: 12px;
      border-radius: 8px;
      font-size: 14px;
      margin-bottom: 20px;
      text-align: center;
    }

    .lien-creation {
      text-align: center;
      margin-top: 25px;
      font-size: 13px;
      color: #666;
    }

    .lien-creation a {
      color: #d62828;
      font-weight: bold;
      text-decoration: none;
    }

    .lien-creation a:hover { text-decoration: underline; }

    .zone-photo {
      flex: 1.5;
      background: url('photo.webp') center/cover no-repeat;
      position: relative;
    }

    .zone-photo::after {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(214,40,40,0.25);
    }

    @media (max-width: 768px) {
      .carte-connexion { flex-direction: column; }
      .zone-photo { min-height: 200px; }
    }
  </style>
</head>
<body>

  <div class="carte-connexion">

    <!-- GAUCHE : FORMULAIRE -->
    <div class="zone-form">
      <h2>Créer un compte</h2>
      <p class="sous-titre">Rejoignez Smart Campus</p>

      <?php if (!empty($message)): ?>
        <div class="message"><?= $message ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="champ">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Votre email" required>
        </div>
        <div class="champ">
          <label for="mdp">Mot de passe</label>
          <input type="password" id="mdp" name="mdp" placeholder="Mot de passe" required>
        </div>
        <button type="submit" name="inscription" class="btn-connexion">Créer mon compte</button>
      </form>

      <div class="lien-creation">
        Déjà un compte ? <a href="connexion.php">Se connecter</a>
      </div>
    </div>

    <!-- DROITE : PHOTO -->
    <div class="zone-photo"></div>

  </div>

</body>
</html>
