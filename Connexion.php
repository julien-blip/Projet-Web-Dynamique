<?php
session_start();
require_once 'db_connect.php';
$erreur = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['connexion'])) {
    $email = trim($_POST['email']);
    $mdp = $_POST['mdp'];
    $stmt = $conn->prepare("SELECT mot_de_passe, email FROM compte_utilisateur WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultat = $stmt->get_result();
    if ($row = $resultat->fetch_assoc()) {
        if (password_verify($mdp, $row['mot_de_passe']) || $mdp === $row['mot_de_passe']) {
            $_SESSION['email'] = $row['email'];
            header("Location: index.php");
            exit();
        } else {
            $erreur = "Email ou mot de passe mauvais.";
        }
    } else {
        $erreur = "Email ou mot de passe mauvais.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Connexion - Smart Campus</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #d62828;            /* fond rouge */
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    /* La carte blanche arrondie */
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

    /* Colonne gauche : le formulaire (plus étroite) */
    .zone-form {
      flex: 1;                        /* 1 part */
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

    .champ input:focus {
      border-color: #d62828;
    }

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

    .erreur {
      background: #fdecea;
      color: #cc0000;
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

    /* Colonne droite : la photo (plus large) */
    .zone-photo {
      flex: 1.5;                      /* 1.5 part → plus large que le form */
      background: url('photo.webp') center/cover no-repeat;
      position: relative;
    }

    /* Voile rouge léger pour fondre la photo avec le thème */
    .zone-photo::after {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(214,40,40,0.25);
    }

    /* Responsive : sur mobile, photo en haut, form en bas */
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
      <h2>Connexion Smart Campus</h2>
      <p class="sous-titre">Connectez-vous à votre espace</p>

      <?php if (!empty($erreur)): ?>
        <div class="erreur"><strong><?= htmlspecialchars($erreur) ?></strong></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="champ">
          <label for="email">Identifiant (Email)</label>
          <input type="email" id="email" name="email" placeholder="Votre email" required>
        </div>
        <div class="champ">
          <label for="mdp">Mot de passe</label>
          <input type="password" id="mdp" name="mdp" placeholder="Mot de passe" required>
        </div>
        <button type="submit" name="connexion" class="btn-connexion">Se connecter</button>
      </form>

      <div class="lien-creation">
        Pas encore de compte ? <a href="creation_compte.php">Créer un compte</a>
      </div>
    </div>

    <!-- DROITE : PHOTO -->
    <div class="zone-photo"></div>
  </div>

</body>
</html>
