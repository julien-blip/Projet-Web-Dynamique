<?php 
require_once 'db_connect.php'; 

$TYPES_EVAL = [
    'Suivi'   => ['label' => 'Note de suivi',     'coef' => 1],
    'DS'      => ['label' => 'Devoir surveillé',   'coef' => 2],
    'TP'      => ['label' => 'Travaux pratiques',  'coef' => 1],
    'Projet'  => ['label' => 'Projet',             'coef' => 2],
    'Examen'  => ['label' => 'Examen final',       'coef' => 3],
];

// 1. LOGIQUE DE SIMULATION D'UTILISATEUR
$role_actuel = "Administrateur";
$nom_actuel = "Admin Principal";
$id_actuel = null;

if (isset($_GET['role_simule'])) {
    $role_actuel = $_GET['role_simule'];
    $id_actuel = isset($_GET['id']) ? intval($_GET['id']) : null;

    if ($role_actuel == "Etudiant" && $id_actuel) {
        $req = mysqli_prepare($conn, "SELECT nom, prenom FROM ETUDIANT WHERE id_etudiant = ?");
        mysqli_stmt_bind_param($req, "i", $id_actuel);
        mysqli_stmt_execute($req);
        $res = mysqli_stmt_get_result($req);
        if ($etu = mysqli_fetch_assoc($res)) {
            $nom_actuel = htmlspecialchars($etu['prenom'] . ' ' . $etu['nom']);
        }
    } elseif ($role_actuel == "Professeur" && $id_actuel) {
        $req = mysqli_prepare($conn, "SELECT nom, prenom FROM ENSEIGNANT WHERE id_enseignant = ?");
        mysqli_stmt_bind_param($req, "i", $id_actuel);
        mysqli_stmt_execute($req);
        $res = mysqli_stmt_get_result($req);
        if ($prof = mysqli_fetch_assoc($res)) {
            $nom_actuel = htmlspecialchars($prof['prenom'] . ' ' . $prof['nom']);
        }
    }
}

// 2. GÉNÉRATION DU MENU DÉROULANT BLINDÉ
$menu_comptes_html = '<div class="menu_deroulant_isole" style="max-height: 400px; overflow-y: auto; background: white; border: 2px solid #0056b3; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); width: 250px; text-align: left; position: absolute; right: 10px; bottom: 60px; z-index: 9999999; display: none;">';

$menu_comptes_html .= '<div style="padding: 8px; background: #0056b3; color: white; font-size: 12px; font-weight: bold;">ADMINISTRATEUR</div>';
$menu_comptes_html .= '<a href="index.php" style="display: block; padding: 10px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">👨‍💼 Admin Principal</a>';

$menu_comptes_html .= '<div style="padding: 8px; background: #0056b3; color: white; font-size: 12px; font-weight: bold;">PROFESSEURS</div>';
$req_p = mysqli_query($conn, "SELECT id_enseignant, nom, prenom FROM ENSEIGNANT ORDER BY nom ASC");
while($p = mysqli_fetch_assoc($req_p)) {
    $menu_comptes_html .= '<a href="index.php?role_simule=Professeur&id='.$p['id_enseignant'].'" style="display: block; padding: 10px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">🎓 '.htmlspecialchars($p['prenom'].' '.$p['nom']).'</a>';
}

$menu_comptes_html .= '<div style="padding: 8px; background: #0056b3; color: white; font-size: 12px; font-weight: bold;">ÉTUDIANTS</div>';
$req_e = mysqli_query($conn, "SELECT id_etudiant, nom, prenom FROM ETUDIANT ORDER BY nom ASC");
while($e = mysqli_fetch_assoc($req_e)) {
    $menu_comptes_html .= '<a href="index.php?role_simule=Etudiant&id='.$e['id_etudiant'].'" style="display: block; padding: 10px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">🧑‍🎓 '.htmlspecialchars($e['prenom'].' '.$e['nom']).'</a>';
}
$menu_comptes_html .= '</div>';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Gestion des Étudiants</title>
  <link rel="stylesheet" href="style.css">
  <script src="javascript.js"></script>
</head>
<body>
<header class="app-header">
  <div class="titrre">SmartCampus</div>

      <div id="notif_header_wrapper" style="position: relative; margin-left: auto; margin-right: 15px; display: flex; align-items: center;">
    <button id="btn_notif_header" title="Notifications" style="
        background: rgba(255,255,255,0.15);
        border: none;
        cursor: pointer;
        padding: 8px 10px;
        border-radius: 50%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    " onmouseover="this.style.background='rgba(255,255,255,0.28)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
      <!-- Icône cloche SVG -->
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
      </svg>
      <!-- Badge rouge -->
      <span id="badge_notif_header" style="
          display: none;
          position: absolute;
          top: 2px; right: 2px;
          background: #ff3b30;
          color: white;
          font-size: 10px;
          font-weight: bold;
          min-width: 17px;
          height: 17px;
          border-radius: 9px;
          padding: 0 4px;
          text-align: center;
          line-height: 17px;
          border: 2px solid #0056b3;
          animation: pulse_notif 1.5s infinite;
      ">0</span>
    </button>

    <!-- Dropdown notifications -->
    <div id="dropdown_notif_header" style="
        display: none;
        position: absolute;
        top: 48px; right: 0;
        width: 320px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.22);
        z-index: 99999;
        overflow: hidden;
        border: 1px solid #e0e0e0;
    ">
      <div style="padding: 14px 18px; background: #0056b3; color: white; font-weight: bold; font-size: 14px; display: flex; justify-content: space-between; align-items: center;">
        <span>🔔 Notifications</span>
        <span id="notif_total_count" style="background:rgba(255,255,255,0.2); padding:2px 8px; border-radius:10px; font-size:12px;"></span>
      </div>
      <div id="liste_notifs_dropdown" style="max-height: 360px; overflow-y: auto;">
        <div style="padding: 30px; text-align: center; color: #aaa; font-size: 14px;">Aucune nouvelle notification</div>
      </div>
      <div style="padding: 10px; text-align: center; border-top: 1px solid #eee;">
        <button id="btn_voir_tous_messages" style="
            background: #0056b3; color: white; border: none;
            padding: 8px 20px; border-radius: 6px;
            cursor: pointer; font-weight: bold; font-size: 13px; width: 100%;
        ">Ouvrir la messagerie</button>
      </div>
    </div>
  </div>

  <div class="amigo"><img src="logo_smart.png" height=50></div>
</header>

<style>
@keyframes pulse_notif {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.18); }
}
</style>
<div class="milieu">

<?php
    include 'navigation/navigation.php';
	?>
	<?php
// ── Alertes flash ──
if (isset($_GET['erreur_insc'])) {
    if ($_GET['erreur_insc'] === 'cours_plein') {
        echo '<div style="background:#ffe6e6; border-left:5px solid #cc0000; padding:14px 20px; margin:15px; border-radius:8px; color:#cc0000; font-weight:bold;">
            ❌ Inscription impossible : ce cours a atteint sa capacité maximale.
        </div>';
    }
}
if (isset($_GET['erreur_cours'])) {
    if ($_GET['erreur_cours'] === 'complet') {
        echo '<div style="background:#ffe6e6; border-left:5px solid #cc0000; padding:14px 20px; margin:15px; border-radius:8px; color:#cc0000; font-weight:bold;">
            ❌ Inscription impossible : ce cours est complet.
        </div>';
    } elseif ($_GET['erreur_cours'] === 'deja_inscrit') {
        echo '<div style="background:#fff3cd; border-left:5px solid #ffc107; padding:14px 20px; margin:15px; border-radius:8px; color:#856404; font-weight:bold;">
            ⚠️ Vous êtes déjà inscrit à ce cours.
        </div>';
    }
}

?>
	<?php
// =========================================================
// DONNÉES DYNAMIQUES DES TABLEAUX DE BORD
// =========================================================
function dash_val($conn, $sql, $type = "", $val = null) {
    try {
        $s = mysqli_prepare($conn, $sql);
        if (!$s) return null;
        if ($type !== "") mysqli_stmt_bind_param($s, $type, $val);
        mysqli_stmt_execute($s);
        $row = mysqli_fetch_row(mysqli_stmt_get_result($s));
        return $row ? $row[0] : 0;
    } catch (\Throwable $e) { return null; }
}
function dash_liste($conn, $sql, $type = "", $val = null) {
    $out = [];
    try {
        $s = mysqli_prepare($conn, $sql);
        if (!$s) return $out;
        if ($type !== "") mysqli_stmt_bind_param($s, $type, $val);
        mysqli_stmt_execute($s);
        $r = mysqli_stmt_get_result($s);
        while ($x = mysqli_fetch_assoc($r)) $out[] = $x;
    } catch (\Throwable $e) {}
    return $out;
}

// ----- ADMIN -----
$adm_nb_etu   = dash_val($conn, "SELECT COUNT(*) FROM ETUDIANT");
$adm_nb_prof  = dash_val($conn, "SELECT COUNT(*) FROM ENSEIGNANT");
$adm_nb_cours = dash_val($conn, "SELECT COUNT(*) FROM COURS");
$adm_nb_insc  = dash_val($conn, "SELECT COUNT(*) FROM INSCRIPTION");
$adm_moy      = dash_val($conn, "SELECT ROUND(AVG(note),2) FROM note WHERE note IS NOT NULL");

// ----- PROFESSEUR -----
$prof_cours = []; $prof_nb_etu = 0; $prof_nb_notes = 0; $prof_seances_jour = 0; $prof_nb_msg = null;
if ($role_actuel == 'Professeur' && $id_actuel) {
    $prof_cours        = dash_liste($conn, "SELECT nom_matiere, code_cours FROM cours WHERE id_enseignant = ? ORDER BY nom_matiere", "i", $id_actuel);
    $prof_nb_etu       = dash_val($conn, "SELECT COUNT(DISTINCT I.id_etudiant) FROM inscription I JOIN cours C ON I.id_cours=C.id_cours WHERE C.id_enseignant = ?", "i", $id_actuel);
    $prof_nb_notes     = dash_val($conn, "SELECT COUNT(*) FROM inscription I JOIN cours C ON I.id_cours=C.id_cours LEFT JOIN note N ON N.id_cours=I.id_cours AND N.id_etudiant=I.id_etudiant AND N.evaluation='Examen Final' WHERE C.id_enseignant = ? AND N.note IS NULL", "i", $id_actuel);
    $prof_seances_jour = dash_val($conn, "SELECT COUNT(*) FROM cours WHERE id_enseignant = ? AND date_cours = CURDATE()", "i", $id_actuel);
    // Messages : adaptez le nom de table/colonnes à votre base si besoin
    $prof_nb_msg       = dash_val($conn, "SELECT COUNT(*) FROM messages WHERE id_destinataire = ? AND role_destinataire = 'Professeur'", "i", $id_actuel);
}

// ----- ÉTUDIANT -----
$etu_nb_cours = 0; $etu_prochaines = []; $etu_notes = []; $etu_absences = null; $etu_notifs = [];
if ($role_actuel == 'Etudiant' && $id_actuel) {
    $etu_nb_cours   = dash_val($conn, "SELECT COUNT(*) FROM inscription WHERE id_etudiant = ?", "i", $id_actuel);
    $etu_prochaines = dash_liste($conn, "SELECT C.nom_matiere, C.date_cours, C.heure FROM inscription I JOIN cours C ON I.id_cours=C.id_cours WHERE I.id_etudiant = ? AND C.date_cours >= CURDATE() ORDER BY C.date_cours ASC, C.heure ASC LIMIT 3", "i", $id_actuel);
    $etu_notes      = dash_liste($conn, "SELECT C.nom_matiere, N.note FROM note N JOIN cours C ON N.id_cours=C.id_cours WHERE N.id_etudiant = ? AND N.note IS NOT NULL LIMIT 3", "i", $id_actuel);
    // Absences / Notifications : adaptez aux tables réelles si vous les créez
    $etu_absences   = dash_val($conn, "SELECT COUNT(*) FROM absence WHERE id_etudiant = ? AND justifiee = 0", "i", $id_actuel);
    $etu_notifs     = dash_liste($conn, "SELECT contenu FROM notification WHERE id_etudiant = ? ORDER BY id_notification DESC LIMIT 3", "i", $id_actuel);
}
?>

	
<div class="vue-tb-admin" style="display: <?php echo ($role_actuel == 'Administrateur') ? 'block' : 'none'; ?>; width: 100%;">
  <div class="haut12" style="padding: 20px;"><h2>Vue d'ensemble - Administrateur</h2></div>
  <div class="dashboard-grid" style="min-height : 575px;">
    <div class="dashboard-card tuile-dash" data-cle="adm_etudiants">
      <h3>Gestion des étudiants</h3>
      <p><?php echo $adm_nb_etu ?? '—'; ?></p>
      <span>Étudiants inscrits</span>
    </div>
    <div class="dashboard-card tuile-dash" data-cle="adm_enseignants">
      <h3>Gestion des enseignants</h3>
      <p><?php echo $adm_nb_prof ?? '—'; ?></p>
      <span>Enseignants enregistrés</span>
    </div>
    <div class="dashboard-card tuile-dash" data-cle="adm_cours">
      <h3>Gestion des cours</h3>
      <p><?php echo $adm_nb_cours ?? '—'; ?></p>
      <span>Cours créés</span>
    </div>
    <div class="dashboard-card tuile-dash" data-cle="adm_stats">
      <h3>Statistiques simples</h3>
      <ul>
        <li>Inscriptions totales : <?php echo $adm_nb_insc ?? '—'; ?></li>
        <li>Moyenne générale : <?php echo ($adm_moy !== null && $adm_moy !== '') ? $adm_moy.'/20' : '—'; ?></li>
        <li>Cours / enseignant : <?php echo ($adm_nb_prof) ? round($adm_nb_cours / max(1,$adm_nb_prof),1) : '—'; ?></li>
      </ul>
    </div>
  </div>
</div>

<div class="vue-tb-prof" style="display: <?php echo ($role_actuel == 'Professeur') ? 'block' : 'none'; ?>; width: 100%;">
  <div class="haut12" style="padding: 20px; display:flex; justify-content:space-between; align-items:center;">
    <h2 style="margin:0;">Vue d'ensemble - Professeur</h2>
    <div style="position:relative;">
      <button class="btn-perso-dash" style="background:#0056b3; color:white; border:none; padding:8px 14px; border-radius:6px; cursor:pointer; font-weight:bold; font-size:13px;">⚙️ Personnaliser</button>
      <div class="popup-perso-dash" style="display:none; position:absolute; right:0; top:42px; background:black; border:1px solid #ccc; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.15); padding:15px; width:240px; z-index:1000; text-align:left;">
        <h4 style="margin:0 0 10px;">Afficher jusqu'à 4 cartes</h4>
        <label style="display:block; margin:6px 0; cursor:pointer;"><input type="checkbox" class="chk-tuile" value="prof_cours"> Cours enseignés</label>
        <label style="display:block; margin:6px 0; cursor:pointer;"><input type="checkbox" class="chk-tuile" value="prof_etudiants"> Étudiants inscrits</label>
        <label style="display:block; margin:6px 0; cursor:pointer;"><input type="checkbox" class="chk-tuile" value="prof_notes"> Notes à saisir</label>
        <label style="display:block; margin:6px 0; cursor:pointer;"><input type="checkbox" class="chk-tuile" value="prof_presences"> Présences à enregistrer</label>
        <label style="display:block; margin:6px 0; cursor:pointer;"><input type="checkbox" class="chk-tuile" value="prof_messages"> Messages</label>
        <button class="btn-valider-perso" style="margin-top:10px; width:100%; background:#28a745; color:white; border:none; padding:8px; border-radius:5px; cursor:pointer; font-weight:bold;">Valider</button>
      </div>
    </div>
  </div>
  <div class="dashboard-grid"style="min-height : 600px;">
    <div class="dashboard-card tuile-dash" data-cle="prof_cours">
      <h3>Cours enseignés</h3>
      <?php if (count($prof_cours)): ?>
        <ul><?php foreach ($prof_cours as $c): ?><li><?php echo htmlspecialchars($c['code_cours'].' : '.$c['nom_matiere']); ?></li><?php endforeach; ?></ul>
      <?php else: ?><p>0</p><span>Aucun cours assigné</span><?php endif; ?>
    </div>
    <div class="dashboard-card tuile-dash" data-cle="prof_etudiants">
      <h3>Étudiants inscrits</h3>
      <p><?php echo $prof_nb_etu ?? '—'; ?></p>
      <span>Dans vos cours</span>
    </div>
    <div class="dashboard-card tuile-dash" data-cle="prof_notes">
      <h3>Notes à saisir</h3>
      <p style="color:#cc0000;"><?php echo $prof_nb_notes ?? '—'; ?></p>
      <span>Évaluations en attente</span>
    </div>
    <div class="dashboard-card tuile-dash" data-cle="prof_presences">
      <h3>Présences à enregistrer</h3>
      <p><?php echo $prof_seances_jour ?? '—'; ?></p>
      <span>Séance(s) aujourd'hui</span>
    </div>
    <div class="dashboard-card tuile-dash" id="tuile_messages_prof" data-cle="prof_messages" style="cursor:pointer;">
      <h3>Messages</h3>
      <p><?php echo $prof_nb_msg ?? '—'; ?></p>
      <span>Message(s) reçu(s)</span>
    </div>
  </div>
</div>

<div class="vue-tb-etudiant" style="display: <?php echo ($role_actuel == 'Etudiant') ? 'block' : 'none'; ?>; width: 100%;">
  <div class="haut12" style="padding: 20px; display:flex; justify-content:space-between; align-items:center;">
    <h2 style="margin:0;">Vue d'ensemble - Étudiant</h2>
    <div style="position:relative;">
      <button class="btn-perso-dash" style="background:#0056b3; color:white; border:none; padding:8px 14px; border-radius:6px; cursor:pointer; font-weight:bold; font-size:13px;">⚙️ Personnaliser</button>
      <div class="popup-perso-dash" style="display:none; position:absolute; right:0; top:42px; background:white; border:1px solid #ccc; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.15); padding:15px; width:240px; z-index:1000; text-align:left;">
        <h4 style="margin:0 0 10px;">Afficher jusqu'à 4 cartes</h4>
        <label style="display:block; margin:6px 0; cursor:pointer;"><input type="checkbox" class="chk-tuile" value="etu_cours"> Cours suivis</label>
        <label style="display:block; margin:6px 0; cursor:pointer;"><input type="checkbox" class="chk-tuile" value="etu_seances"> Prochaines séances</label>
        <label style="display:block; margin:6px 0; cursor:pointer;"><input type="checkbox" class="chk-tuile" value="etu_notes"> Notes récentes</label>
        <label style="display:block; margin:6px 0; cursor:pointer;"><input type="checkbox" class="chk-tuile" value="etu_absences"> Absences</label>
        <label style="display:block; margin:6px 0; cursor:pointer;"><input type="checkbox" class="chk-tuile" value="etu_notifs"> Notifications</label>
        <button class="btn-valider-perso" style="margin-top:10px; width:100%; background:#28a745; color:white; border:none; padding:8px; border-radius:5px; cursor:pointer; font-weight:bold;">Valider</button>
      </div>
    </div>
  </div>
  <div class="dashboard-grid"style="min-height : 600px;">
    <div class="dashboard-card tuile-dash" data-cle="etu_cours">
      <h3>Cours suivis</h3>
      <p><?php echo $etu_nb_cours ?? '—'; ?></p>
      <span>Modules ce semestre</span>
    </div>
    <div class="dashboard-card tuile-dash" data-cle="etu_seances">
      <h3>Prochaines séances</h3>
      <?php if (count($etu_prochaines)): ?>
        <ul><?php foreach ($etu_prochaines as $s): ?><li><?php echo htmlspecialchars($s['date_cours'].' ('.$s['heure'].') : '.$s['nom_matiere']); ?></li><?php endforeach; ?></ul>
      <?php else: ?><span>Aucune séance à venir</span><?php endif; ?>
    </div>
    <div class="dashboard-card tuile-dash" data-cle="etu_notes">
      <h3>Notes récentes</h3>
      <?php if (count($etu_notes)): ?>
        <ul><?php foreach ($etu_notes as $n): ?><li><?php echo htmlspecialchars($n['nom_matiere'].' : '.$n['note'].'/20'); ?></li><?php endforeach; ?></ul>
      <?php else: ?><span>Aucune note disponible</span><?php endif; ?>
    </div>
    <div class="dashboard-card tuile-dash" data-cle="etu_absences">
      <h3>Absences</h3>
      <p style="color:#cc0000;"><?php echo $etu_absences ?? '—'; ?></p>
      <span>Absence(s) injustifiée(s)</span>
    </div>
    <div class="dashboard-card tuile-dash" data-cle="etu_notifs">
      <h3>Notifications</h3>
      <?php if (count($etu_notifs)): ?>
        <ul><?php foreach ($etu_notifs as $no): ?><li><?php echo htmlspecialchars($no['contenu']); ?></li><?php endforeach; ?></ul>
      <?php else: ?><span>Aucune notification</span><?php endif; ?>
    </div>
  </div>
</div>

