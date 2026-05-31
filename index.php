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

			
<div class="page" style="display: none;">
    <div class="contenu1">
      <div id="vue_dashboard">
        <div class="haut1">
          <div class="gauche">
            <strong>Gestion des étudiants</strong>
          </div>
          <div class="droite">
            <div class="recherche">
              <input type="text" placeholder="Rechercher un étudiant...">
              <div class="btn-filtre" id="Bouton_filtre_etudiant">
                <img src="filtre.png" height="15" alt="Filtre"> Filtre
              </div>
			  <!-- Popup Filtre Étudiants -->
<div id="menu_filtre_etudiant" class="menu-filtre-popup" style="display: none;">
  <h4>Filtrer les étudiants</h4>

  <div class="groupe-filtre">
    <strong>Niveau</strong>
    <label><input type="checkbox" class="filtre-etudiant" data-champ="niveau" value="ING1"> ING1</label>
    <label><input type="checkbox" class="filtre-etudiant" data-champ="niveau" value="ING2"> ING2</label>
    <label><input type="checkbox" class="filtre-etudiant" data-champ="niveau" value="ING3"> ING3</label>
    <label><input type="checkbox" class="filtre-etudiant" data-champ="niveau" value="ING4_Systeme"> ING4_Système</label>
    <label><input type="checkbox" class="filtre-etudiant" data-champ="niveau" value="ING4_Cyber"> ING4_Cyber</label>
    <label><input type="checkbox" class="filtre-etudiant" data-champ="niveau" value="ING4_Finance"> ING4_Finance</label>
    <label><input type="checkbox" class="filtre-etudiant" data-champ="niveau" value="ING4_Energie"> ING4_Energie</label>
    <label><input type="checkbox" class="filtre-etudiant" data-champ="niveau" value="ING5_Systeme"> ING5_Système</label>
    <label><input type="checkbox" class="filtre-etudiant" data-champ="niveau" value="ING5_Cyber"> ING5_Cyber</label>
    <label><input type="checkbox" class="filtre-etudiant" data-champ="niveau" value="ING5_Finance"> ING5_Finance</label>
    <label><input type="checkbox" class="filtre-etudiant" data-champ="niveau" value="ING5_Energie"> ING5_Energie</label>
  </div>

  <div class="groupe-filtre">
    <strong>Nationalité</strong>
    <select id="filtre_nationalite_etu" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; margin-top:5px;">
      <option value="">-- Toutes --</option>
      <option value="Francaise">Française</option>
      <option value="Algérienne">Algérienne</option>
      <option value="Allemande">Allemande</option>
      <option value="Américaine">Américaine</option>
      <option value="Belge">Belge</option>
      <option value="Béninoise">Béninoise</option>
      <option value="Britannique">Britannique</option>
      <option value="Burkinabè">Burkinabè</option>
      <option value="Camerounaise">Camerounaise</option>
      <option value="Canadienne">Canadienne</option>
      <option value="Chinoise">Chinoise</option>
      <option value="Congolaise">Congolaise</option>
      <option value="Espagnole">Espagnole</option>
      <option value="Ivoirienne">Ivoirienne</option>
      <option value="Italienne">Italienne</option>
      <option value="Japonaise">Japonaise</option>
      <option value="Malienne">Malienne</option>
      <option value="Marocaine">Marocaine</option>
      <option value="Sénégalaise">Sénégalaise</option>
      <option value="Suisse">Suisse</option>
      <option value="Togolaise">Togolaise</option>
      <option value="Tunisienne">Tunisienne</option>
    </select>
  </div>

  <div style="display:flex; gap:10px; margin-top:10px;">
    <button class="btn-appliquer-filtre" id="btn_appliquer_filtre_etudiant">Appliquer</button>
    <button id="btn_reset_filtre_etudiant" style="padding:8px 15px; background:#CDCDCD; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">Réinitialiser</button>
  </div>
</div>
            </div>
            <div class="btn">
              <div id="Ajouter_etudiant"><img src="plus.png" height="15" alt="PLUS"> Ajouter un étudiant</div>
              <div id="Importer">Importer</div>
            </div>
          </div>
        </div>
		

      <div class="haut21">
          <?php
          // 1. On force l'affichage des erreurs pour ne plus JAMAIS avoir de page blanche
          error_reporting(E_ALL);
          ini_set('display_errors', 1);

          // 2. Requête SQL sécurisée (LEFT JOIN permet de ne perdre aucun étudiant)
          $sql_cartes = "SELECT E.*, C.email, C.telephone 
                         FROM ETUDIANT E 
                         LEFT JOIN COMPTE_UTILISATEUR C ON E.id_compte = C.id_compte";
          $resultat_cartes = mysqli_query($conn, $sql_cartes);

          // 3. Blindage : Si la requête plante, on affiche l'erreur en rouge
          if (!$resultat_cartes) {
              echo "<div style='color: red; padding: 20px; border: 2px solid red; background: #ffeeee; width: 100%; border-radius: 10px;'>";
              echo "<strong>Erreur SQL critique :</strong> " . mysqli_error($conn) . "<br><br>";
              echo "Vérifie tes tables dans phpMyAdmin.";
              echo "</div>";
          } else {
              // 4. Blindage : Si la base est vide, on l'indique proprement
              if (mysqli_num_rows($resultat_cartes) == 0) {
                  echo "<div style='padding: 30px; font-size: 16px; color: #666; width: 100%; text-align: center; background: #fff; border-radius: 10px; border: 1px dashed #ccc;'>";
                  echo "Aucun étudiant n'est enregistré pour le moment.<br>";
                  echo "Cliquez sur <strong>+ Ajouter un étudiant</strong> pour commencer.";
                  echo "</div>";
              }

              // 5. La boucle d'affichage
              while ($etudiant_carte = mysqli_fetch_assoc($resultat_cartes)) {
                  // On sécurise chaque variable au cas où une case serait vide dans la base
                  $id_db = $etudiant_carte['id_etudiant'] ?? 'N/A';
                  $prenom = $etudiant_carte['prenom'] ?? 'Inconnu';
                  $nom = $etudiant_carte['nom'] ?? '';
                  $nom_complet = htmlspecialchars($prenom . ' ' . $nom);
                  $numero_etu = htmlspecialchars($etudiant_carte['numero_etudiant'] ?? 'N/A');
                  $email_etu = htmlspecialchars($etudiant_carte['email_etu'] ?? 'Pas d\'email');
                  $tel_etu = htmlspecialchars($etudiant_carte['telephone'] ?? 'Pas de téléphone');
                  $date_naiss_etu = htmlspecialchars($etudiant_carte['date_naissance'] ?? 'N/A');
                  $filiere_etu = htmlspecialchars($etudiant_carte['nationalite'] ?? 'N/A');
                  $statut_etu = htmlspecialchars($etudiant_carte['statut'] ?? 'Actif');
                  $niveau_etu = htmlspecialchars($etudiant_carte['niveau'] ?? 'N/A');
                  $annee_etu = htmlspecialchars($etudiant_carte['annee_academique'] ?? 'N/A');
          ?>
              
              <div class="bloc-etudiant-complet"
     data-niveau="<?php echo htmlspecialchars($etudiant_carte['niveau'] ?? ''); ?>"
     data-statut="<?php echo htmlspecialchars($etudiant_carte['statut'] ?? 'Actif'); ?>"
     data-nationalite="<?php echo htmlspecialchars($etudiant_carte['nationalite'] ?? ''); ?>"
     style="display: flex; gap: 20px; width: 100%; margin-bottom: 20px; flex-wrap: wrap;">
                  <div class="profil" style="margin: 0;">
                    <div class="caracteristiques">
                      <strong><?php echo $nom_complet; ?></strong><br> 
                      ID ÉTUDIANT : <?php echo $numero_etu; ?><br> 
                      <?php echo $email_etu; ?><br>
                      <?php echo $tel_etu; ?><br> 
                      Né(e) le : <?php echo $date_naiss_etu; ?>
                    </div>
                    <div class="barriere"></div>
                    <div class="statut">
                      <div class="ctn1"><strong>Niveau</strong><br> <?php echo $niveau_etu; ?><br><br>
                        <strong>Nationalité</strong><br> <?php echo $filiere_etu; ?><br><br>
                        
                      </div>
                      <div class="ctn2">
                        
                        <strong>Année</strong><br> <?php echo $annee_etu; ?><br><br>
						<strong>Statut</strong><br> <?php echo $statut_etu; ?>
                      </div>
                    </div>
                  </div>

                  <div class="action_rapide">
                    <strong>Actions rapides</strong>
                    <div class="rapide btn-voir-profil" data-id="<?php echo $id_db; ?>" style="cursor: pointer;">Voir le profil complet</div>
                    
                    <a href="modifier_etudiant.php?id=<?php echo $id_db; ?>" class="rapide" style="background-color: #e6f2ff; color: #0056b3; text-decoration: none; display: block;">
                      Modifier les informations
                    </a>

                    <a href="supprimer_etudiant.php?id=<?php echo $id_db; ?>" class="rapide" style="background-color: #ffcccc; color: #cc0000; text-decoration: none; display: block;">
                      Supprimer l'étudiant
                    </a>
                    
                  </div>
              </div>

          <?php
              } // Fin du while
          } // Fin du else
          ?>  ?>
        </div>
        </div>
      <div id="formulaire_ajout">
        <div class="haut1">
          <div class="gauche"><strong>Ajouter un Étudiant</strong></div>
          <div class="droite"><div class="btn"><div id="Retour_dashboard">Retour au tableau de bord</div></div></div>
        </div>

        <form method="post" action="ajouter.php">
          <div class="form-container">
            <div class="struct">
              <h3>Informations personnelles</h3>
              <div class="input-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenoml" name="ajout_prenom_etudiant" placeholder="Ex: Jean" required>
              </div>
			  <div class="input-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="ajout_nom_etudiant" placeholder="Ex: Dupont" required>
              </div>
              <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="ajout_email_etudiant" placeholder="jean.dupont@email.com" required>
              </div>
              <div class="input-group">
                <label for="date_naissance">Date de naissance</label>
                <input type="date" id="date_naissance" name="ajout_date_etudiant" required>
              </div>
              <div class="input-group">
                <label for="telephone">Téléphone</label>
                <input type="tel" id="telephone" name="ajout_tel_etudiant" required>
              </div>
              <div class="input-group">
                <label for="genre">Genre</label>
                <select id="genre" name="ajout_genre_etudiant" required>
                  <option value="">-- Sélectionner --</option>
                  <option value="Homme">Homme</option>
                  <option value="Femme">Femme</option>
                  <option value="Autre">Autre</option>
                </select>
              </div>
              <div class="input-group">
                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" name="ajout_adresse_etudiant" required>
              </div>
              

 <div class="input-group">
                <label for="nationalite">Nationalité</label>
                <select id="nationalite" name="ajout_nationalite_etudiant" required>
                  <option value="">-- Sélectionner --</option>
                  <option value="Francaise">Française</option>
                  <option value="Algérienne">Algérienne</option>
                  <option value="Allemande">Allemande</option>
				  <option value="Américaine">Américaine</option>
                  <option value="Belge">Belge</option>
                  <option value="Béninoise">Béninoise</option>
                  <option value="Britannique">Britannique</option>
				  <option value="Burkinabè">Burkinabè</option>
                  <option value="Camerounaise">Camerounaise</option>
                  <option value="Canadienne">Canadienne</option>
                  <option value="Chinoise">Chinoise</option>
				  <option value="Congolaise">Congolaise</option>
                  <option value="Espagnole">Espagnole</option>
                  <option value="Ivoirienne">Ivoirienne</option>
                  <option value="Italienne">Italienne</option>
				  <option value="Japonaise">Japonaise</option>
                  <option value="Malienne">Malienne</option>
                  <option value="Marocaine">Marocaine</option>
				  <option value="Sénégalaise">Sénégalaise</option>
                  <option value="Suisse">Suisse</option>
                  <option value="Togolaise">Togolaise</option>
                  <option value="Tunisienne">Tunisienne</option>
                </select>
              </div>
            </div>
			
			

            <div class="struct">
              <h3>Informations académiques</h3>
	<div class="input-group">
                <label for="niveau">Niveau Actuel</label>
                <select id="niveau" name="ajout_niveau_etudiant" required>
                  <option value="">-- Sélectionner --</option>
                  <option value="ING1">ING1</option>
                  <option value="ING2">ING2</option>
                  <option value="ING3">ING3</option>
                  <option value="ING4_Systeme">ING4 - Système Embarqué</option>
                  <option value="ING4_Cyber">ING4 - CyberSécurité</option>
				  <option value="ING4_Finance">ING4 - Finance</option>
                  <option value="ING4_Energie">ING4 - Energie</option>
                  <option value="ING5_Systeme">ING5 - Système Embarqué</option>
                  <option value="ING5_Cyber">ING5 - CyberSécurité</option>
				  <option value="ING5_Finance">ING5 - Finance</option>
                  <option value="ING5_Energie">ING5 - Energie</option>
                </select>
              </div>
			  <div class="input-group">
                <label for="annee">Année académique</label>
                <select id="annee" name="ajout_annee_etudiant" required>
                  <option value="">-- Sélectionner --</option>
                  <option value="2025 - 2026">2025 - 2026</option>
                  <option value="2024 - 2025">2024 - 2025</option>
                  <option value="2023 - 2024">2023 - 2024</option>
				  <option value="2022 - 2023">2022 - 2023</option>
                  <option value="2021 - 2022">2021 - 2022</option>
                </select>
              </div>
              <div class="input-group">
                <label for="statut">Statut</label>
                <select id="statut" name="ajout_statut_etudiant" required>
				  <option value="">-- Sélectionner --</option>
                  <option value="Actif">Actif</option>
                  <option value="Inactif">Inactif</option>
                </select>
              </div>
              <button type="submit" name="button_ajouter" class="btn-submit">Ajouter l'étudiant</button>
            </div>
          </div>
        </form>

      </div>
	  <div id="vue_profil_complet" style="display: none;">
  <div class="haut1">
    <div class="gauche"><strong>Profil Complet de l'Étudiant</strong></div>
    <div class="droite">
      <div class="btn">
        <div id="Retour_dashboard_profil" style="background: #CDCDCD; padding: 8px 15px; border-radius: 7px; font-weight: bold; cursor: pointer;">Retour au tableau de bord</div>
      </div>
    </div>
  </div>
  <div id="contenu_profil_complet" style="background: white; padding: 20px; border-radius: 10px; margin-top: 20px;">
    <p style="color: #999; text-align: center;">Chargement...</p>
  </div>
</div>
    </div>
  </div>
 <div class="page-cours-etudiant" style="display: none;">
  <div class="contenu20">
    <div class="haut12" style="padding: 20px 20px 0 20px;">
      <h2>Gestion de mes cours</h2>
    </div>
      <div class="fond" style="background : #f8f9fa;">
    <div class="layout-deux-colonnes" style="display: flex; gap: 20px; padding: 20px; align-items: flex-start;">
        
      <?php
      if ($role_actuel == "Etudiant" && $id_actuel) {
      ?>

		
      <div class="panneau-gauche" style="flex: 2; background: #fefdfe; padding: 20px; border-radius: 10px; border: 1px solid #e0e0e0;">
        <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #0056b3; padding-bottom: 10px;">Catalogue des cours disponibles</h3>
        
        <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; margin-top: 15px; max-height: 500px; overflow-y: auto; padding-right: 10px;">
            
          <?php
          // Requête pour chercher les cours correspondants au NIVEAU de l'étudiant et où il n'est pas inscrit
          $sql_cours_dispo = "SELECT C.id_cours, C.nom_matiere, C.code_cours,C.date_cours, C.niveau,C.heure,E.nom AS prof_nom, E.prenom AS prof_prenom
                              FROM COURS C
                              LEFT JOIN ENSEIGNANT E ON C.id_enseignant = E.id_enseignant
                              WHERE C.niveau = (SELECT niveau FROM ETUDIANT WHERE id_etudiant = ?)
                              AND C.id_cours NOT IN (
                                  SELECT id_cours FROM INSCRIPTION WHERE id_etudiant = ?
                              )";
          $stmt_cd = mysqli_prepare($conn, $sql_cours_dispo);
          // On passe deux fois l'ID actuel car il y a deux "?" dans la requête
          mysqli_stmt_bind_param($stmt_cd, "ii", $id_actuel, $id_actuel);
          mysqli_stmt_execute($stmt_cd);
          $res_cd = mysqli_stmt_get_result($stmt_cd);

          if (mysqli_num_rows($res_cd) == 0) {
              echo "<div style='grid-column: 1 / -1; padding: 20px; background: white; text-align: center; color: #666; border-radius: 8px;'>Aucun nouveau cours disponible pour le moment.</div>";
          } else {
              while ($cd = mysqli_fetch_assoc($res_cd)) {
    $prof_dispo = !empty($cd['prof_nom']) ? htmlspecialchars($cd['prof_prenom'].' '.$cd['prof_nom']) : "Non assigné";
    
    // Compter les inscrits actuels
    $stmt_nb = mysqli_prepare($conn, "SELECT COUNT(*) FROM INSCRIPTION WHERE id_cours = ?");
    mysqli_stmt_bind_param($stmt_nb, "i", $cd['id_cours']);
    mysqli_stmt_execute($stmt_nb);
    $nb_act = mysqli_fetch_row(mysqli_stmt_get_result($stmt_nb))[0];
    $cap    = $cd['capacite_max'] ?? 30;
    $plein  = ($nb_act >= $cap);
    
    echo '<div class="dashboard-card" style="background:white; padding:15px; border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.1); display:flex; flex-direction:column; justify-content:space-between; '.($plein ? 'opacity:0.7;' : '').'">';
    echo '<div>';
    echo '<h4 style="color:#0056b3; margin:0 0 10px 0;">'.htmlspecialchars($cd['nom_matiere']).'</h4>';
    echo '<p style="font-size:14px; margin:5px 0;">Code : <strong>'.htmlspecialchars($cd['code_cours']).'</strong></p>';
    echo '<p style="font-size:13px; color:#666; margin:5px 0;">Niveau: '.htmlspecialchars($cd['niveau']).'&nbsp;&nbsp;&nbsp;&nbsp;Date : '.htmlspecialchars($cd['date_cours']).'</p>';
    echo '<p style="font-size:13px; color:#666; margin:5px 0;">Prof: '.$prof_dispo.'&nbsp;&nbsp;&nbsp;&nbsp;Horaire : '.htmlspecialchars($cd['heure']).'</p>';
    
    // Badge places restantes
    $places = $cap - $nb_act;
    $badge_color = $plein ? '#cc0000' : ($places <= 3 ? '#ffc107' : '#28a745');
    $badge_text  = $plein ? '🔒 Complet' : $places.' place'.($places > 1 ? 's' : '').' restante'.($places > 1 ? 's' : '');
    echo '<p style="margin:8px 0 0;"><span style="background:'.($plein?'#ffe6e6':($places<=3?'#fff3cd':'#e6f7ec')).'; color:'.$badge_color.'; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:bold;">'.$badge_text.'</span></p>';
    echo '</div>';
    
    if ($plein) {
        echo '<div style="display:block; text-align:center; background:#e0e0e0; color:#999; padding:8px; border-radius:5px; font-weight:bold; margin-top:15px; font-size:14px; cursor:not-allowed;">Cours complet</div>';
    } else {
        echo '<a href="etudiant_sinscrire.php?id_cours='.$cd['id_cours'].'&id_etu='.$id_actuel.'" style="display:block; text-align:center; background:#28a745; color:white; padding:8px; text-decoration:none; border-radius:5px; font-weight:bold; margin-top:15px; font-size:14px;">S\'inscrire</a>';
    }
    echo '</div>';
}
          }
          ?>
        </div>
      </div>

      <div class="panneau-droite" style="flex: 1; background: #ffffff; padding: 20px; border-radius: 10px; border: 1px solid #d6d8db; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #6c757d; padding-bottom: 10px;">Mes cours inscrits</h3>
        <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 15px;">
            
          <?php
          // Ta requête d'origine pour chercher les cours de l'étudiant
          $sql_mes_cours = "SELECT C.id_cours, C.nom_matiere, C.code_cours,C.date_cours, C.niveau,C.heure,E.nom AS prof_nom, E.prenom AS prof_prenom
                            FROM INSCRIPTION I
                            JOIN COURS C ON I.id_cours = C.id_cours
                            LEFT JOIN ENSEIGNANT E ON C.id_enseignant = E.id_enseignant
                            WHERE I.id_etudiant = ?";
          $stmt_mc = mysqli_prepare($conn, $sql_mes_cours);
          mysqli_stmt_bind_param($stmt_mc, "i", $id_actuel);
          mysqli_stmt_execute($stmt_mc);
          $res_mc = mysqli_stmt_get_result($stmt_mc);

          if (mysqli_num_rows($res_mc) == 0) {
              echo "<div style='padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center; color: #666;'>Vous n'êtes inscrit à aucun cours.</div>";
          } else {
              while ($mc = mysqli_fetch_assoc($res_mc)) {
                  $prof = !empty($mc['prof_nom']) ? htmlspecialchars($mc['prof_prenom'].' '.$mc['prof_nom']) : "Non assigné";
                  echo '<div style="padding: 15px; border-left: 4px solid #0056b3; background: #f8f9fa; border-radius: 4px;">';
                  echo '<h4 style="margin: 0 0 5px 0; color: #0056b3; font-size: 15px;">' . htmlspecialchars($mc['nom_matiere']??'Non defini') . '</h4>';
                  echo '<p style="margin: 0; font-size: 13px; color: #555;">Code: <strong>' . htmlspecialchars($mc['code_cours']??'Non defini') .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date :&nbsp; ' . htmlspecialchars($mc['date_cours']?? 'Non defini') .'</strong></p>';
                  echo '<p style="margin: 3px 0 0 0; font-size: 12px; color: #777;">Prof: ' . $prof . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date :&nbsp; ' . htmlspecialchars($mc['heure']?? 'Non defini') .'</p>';
                  echo '</div>';
              }
          }
          ?>
        </div>
      </div>

      <?php
      } // Fin du if ($role_actuel == "Etudiant")
      ?>
      
    </div>
	</div>
  </div>
</div>
  <div class="emploi" style="display: none;">
    <div class="contenu2">
      <div id="vue_emploi_temps">
        <div class="haut12" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
          <div class="gauche">
            <strong>EMPLOI DU TEMPS</strong>
          </div>
          <div class="droite" style="display: flex; align-items: center; gap: 15px;">
            <button id="btn_semaine_prec" style="cursor: pointer; padding: 5px 15px; border-radius: 5px; border: 1px solid #ccc; background: #fff;">&lt; Précédent</button>
            <span id="affichage_semaine" style="font-weight: bold; font-size: 16px; color: #333;">Semaine actuelle</span>
            <button id="btn_semaine_suiv" style="cursor: pointer; padding: 5px 15px; border-radius: 5px; border: 1px solid #ccc; background: #fff;">Suivant &gt;</button>
          </div>
        </div>

        <?php
        // 1. Récupération de TOUS les cours de l'étudiant sous format JSON
        $cours_edt_json = "[]";
        if ($role_actuel == 'Etudiant' && $id_actuel) {
            // On récupère directement la date_cours et l'heure dans la table cours
            $sql_edt = "SELECT C.nom_matiere, C.date_cours, C.heure, C.salle,
                   E.nom AS prof_nom, E.prenom AS prof_prenom
            FROM inscription I
            JOIN cours C ON I.id_cours = C.id_cours
            LEFT JOIN ENSEIGNANT E ON C.id_enseignant = E.id_enseignant
            WHERE I.id_etudiant = ? AND C.date_cours IS NOT NULL";
            $stmt_edt = mysqli_prepare($conn, $sql_edt);
            mysqli_stmt_bind_param($stmt_edt, "i", $id_actuel);
            mysqli_stmt_execute($stmt_edt);
            $res_edt = mysqli_stmt_get_result($stmt_edt);

            $cours_etudiant = [];
            while ($row = mysqli_fetch_assoc($res_edt)) {
                $cours_etudiant[] = $row;
            }
            // On transforme le résultat PHP en format compris par le JS
            $cours_edt_json = json_encode($cours_etudiant);
        }
        ?>

        <div class="grille-edt" id="conteneur_grille_edt" data-cours='<?php echo htmlspecialchars($cours_edt_json ?? "[]", ENT_QUOTES, "UTF-8"); ?>'>
           </div>

      </div>
    </div>
  </div>
<div class="notation" style="display: none;">
  <div class="contenu2">
    <div id="vue_note">
      <div class="haut12" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="gauche"><strong>MES NOTES & RÉSULTATS</strong></div>
      </div>

      <?php
      // ====== CALCULS POUR L'ÉTUDIANT ======
      $notes_lignes = [];
$somme_ponderee = 0; $somme_coeffs = 0; $meilleure = null; $pire = null;
      if ($role_actuel == 'Etudiant' && $id_actuel) {
          $sql_notes_etu = "SELECT C.nom_matiere, N.coefficient, N.note, N.commentaire, N.evaluation
                            FROM INSCRIPTION I
                            JOIN COURS C ON I.id_cours = C.id_cours
                            LEFT JOIN note N ON I.id_cours = N.id_cours AND N.id_etudiant = I.id_etudiant
                            WHERE I.id_etudiant = ?
                            ORDER BY C.nom_matiere ASC";
          $stmt_ne = mysqli_prepare($conn, $sql_notes_etu);
          mysqli_stmt_bind_param($stmt_ne, "i", $id_actuel);
          mysqli_stmt_execute($stmt_ne);
          $res_ne = mysqli_stmt_get_result($stmt_ne);
          while ($n = mysqli_fetch_assoc($res_ne)) {
              $notes_lignes[] = $n;
              if (isset($n['note']) && $n['note'] !== null && $n['note'] !== '' && is_numeric($n['note'])) {
    $v     = (float)$n['note'];
    $coeff = (isset($n['coefficient']) && $n['coefficient'] > 0) ? (float)$n['coefficient'] : 1;
    $somme_ponderee += $v * $coeff;
    $somme_coeffs   += $coeff;
    if ($meilleure === null || $v > $meilleure) $meilleure = $v;
    if ($pire === null || $v < $pire) $pire = $v;
}
          }
      }
      $moyenne = ($somme_coeffs > 0) ? round($somme_ponderee / $somme_coeffs, 2) : null;
$nb_notes = count(array_filter($notes_lignes, fn($n) => $n['note'] !== null && $n['note'] !== ''));
      ?>

      <!-- Cartes de synthèse -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:15px; margin-top:20px;">
        <div style="background:white; border-radius:12px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:5px solid #0056b3;">
          <div style="font-size:13px; color:#888; text-transform:uppercase; letter-spacing:.5px;">Moyenne générale</div>
          <div style="font-size:32px; font-weight:bold; color:<?php echo ($moyenne!==null && $moyenne>=10)?'#28a745':($moyenne!==null?'#cc0000':'#999'); ?>; margin-top:6px;">
            <?php echo $moyenne!==null ? $moyenne.' <span style="font-size:16px;color:#aaa;">/20</span>' : '—'; ?>
          </div>
        </div>
        <div style="background:white; border-radius:12px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:5px solid #28a745;">
          <div style="font-size:13px; color:#888; text-transform:uppercase; letter-spacing:.5px;">Meilleure note</div>
          <div style="font-size:32px; font-weight:bold; color:#28a745; margin-top:6px;">
            <?php echo $meilleure!==null ? $meilleure.' <span style="font-size:16px;color:#aaa;">/20</span>' : '—'; ?>
          </div>
        </div>
        <div style="background:white; border-radius:12px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:5px solid #ffc107;">
          <div style="font-size:13px; color:#888; text-transform:uppercase; letter-spacing:.5px;">Notes reçues</div>
          <div style="font-size:32px; font-weight:bold; color:#333; margin-top:6px;">
            <?php echo $nb_notes; ?> <span style="font-size:16px;color:#aaa;">/ <?php echo count($notes_lignes); ?></span>
          </div>
        </div>
      </div>

      <!-- Tableau détaillé -->
      <div class="grille-note" style="margin-top:20px; background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <table style="width:100%; border-collapse:collapse; text-align:left;">
          <thead>
            <tr style="background:#0056b3; color:white;">
              <th style="padding:15px; border-radius:8px 0 0 0;">Matière</th>
<th style="padding:15px;">Évaluation</th>
<th style="padding:15px; text-align:center;">Coeff</th>
<th style="padding:15px; text-align:center;">Note</th>
<th style="padding:15px; border-radius:0 8px 0 0;">Appréciation</th>
            </tr>
          </thead>
          <tbody>
			  <?php
            if (count($notes_lignes) == 0) {
                echo "<tr><td colspan='5' style='padding:20px; text-align:center; color:#666;'>Vous n'êtes inscrit à aucun cours.</td></tr>";
            } else {
                foreach ($notes_lignes as $n) {
                    $matiere = htmlspecialchars($n['nom_matiere']);
                    $eval = htmlspecialchars($n['evaluation'] ?? 'Examen Final');
                    if (isset($n['note']) && $n['note'] !== null && $n['note'] !== '') {
                        $estReussi = ($n['note'] >= 10);
                        $bgBadge = $estReussi ? '#e6f7ec' : '#fdecea';
                        $colBadge = $estReussi ? '#28a745' : '#cc0000';
                        $badge = '<span style="background:'.$bgBadge.'; color:'.$colBadge.'; padding:6px 12px; border-radius:20px; font-weight:bold; font-size:14px; white-space:nowrap;">'.htmlspecialchars($n['note']).' / 20</span>';
                        $commentaire = htmlspecialchars($n['commentaire'] ?? '-');
                    } else {
                        $badge = '<span style="background:#f0f0f0; color:#999; padding:6px 12px; border-radius:20px; font-size:14px;">En attente</span>';
                        $commentaire = '<span style="color:#999; font-style:italic;">En attente d\'évaluation…</span>';
                    }
                    $coeff_affiche = isset($n['coefficient']) && $n['coefficient'] > 0 ? $n['coefficient'] : '1';
echo '<tr style="border-bottom:1px solid #eee;">';
echo '  <td style="padding:15px; font-weight:bold; color:#333;">'.$matiere.'</td>';
echo '  <td style="padding:15px; color:#666;">'.$eval.'</td>';
echo '  <td style="padding:15px; text-align:center; color:#0056b3; font-weight:bold;">× '.$coeff_affiche.'</td>';
echo '  <td style="padding:15px; text-align:center;">'.$badge.'</td>';
echo '  <td style="padding:15px; color:#555;">'.$commentaire.'</td>';
echo '</tr>';
                }
            }
            ?>


          // 2. On liste tous les Étudiants
          $req_etus_msg = mysqli_query($conn, "SELECT id_etudiant AS id, nom, prenom, 'Etudiant' AS role FROM ETUDIANT ORDER BY nom ASC");
          while($e = mysqli_fetch_assoc($req_etus_msg)) {
              if ($role_actuel == 'Etudiant' && $id_actuel == $e['id']) continue;
              
              echo '<div class="contact contact-item" data-id="'.$e['id'].'" data-role="'.$e['role'].'" data-nom="'.htmlspecialchars($e['prenom'].' '.$e['nom']).'" style="cursor: pointer;">';
              echo '  <div class="photo-contact"></div>';
              echo '  <div class="info-contact">';
              echo '    <h4>'.htmlspecialchars($e['prenom'].' '.$e['nom']).'</h4>';
              echo '    <p style="font-size: 11px; color: #888;">Étudiant</p>';
              echo '  </div>';
              echo '</div>';
          }
          ?>


			  <div class="presences">
    <div class="contenu2">
      <div class="haut12">
        <div class="gauche">
          <strong>PRÉSENCES</strong>
        </div>
      </div>
      
      <div class="container-presences">
        <div class="liste-presences">
          <h2>Historique récent</h2>
          
          <div class="item-presence present">
            <div class="date-cours">
              <strong>Aujourd'hui</strong><br>
              Informatique - 10:00
            </div>
            <div class="statut-badge">Présent</div>
          </div>

          <div class="item-presence absent">
            <div class="date-cours">
              <strong>Lundi 14 Octobre</strong><br>
              Mathématiques - 08:00
            </div>
            <div class="statut-badge">Absent</div>
          </div>

          <div class="item-presence present">
            <div class="date-cours">
              <strong>Vendredi 11 Octobre</strong><br>
              Anglais - 14:30
            </div>
            <div class="statut-badge">Présent</div>
          </div>

          <div class="item-presence present">
            <div class="date-cours">
              <strong>Mercredi 9 Octobre</strong><br>
              Physique - 10:00
            </div>
            <div class="statut-badge">Présent</div>
          </div>
          
          <div class="item-presence absent">
            <div class="date-cours">
              <strong>Mardi 8 Octobre</strong><br>
              Sport - 14:30
            </div>
            <div class="statut-badge">Absent</div>
          </div>
        </div>

        <div class="stats-presences">
          <h2>Bilan du semestre</h2>
          
          <div class="camembert"></div>
          
          <div class="legende">
            <div><span class="dot-present"></span> <strong>Présent :</strong> 85% (34 cours)</div>
            <div><span class="dot-absent"></span> <strong>Absent :</strong> 15% (6 cours)</div>
          </div>
        </div>
      </div>

    </div>
  </div>
  <div class="parametres">
    <div class="contenu2">
      <div class="haut12">
        <div class="gauche">
          <strong>PARAMÈTRES DU COMPTE</strong>
        </div>
      </div>
      
      <?php
      // 1. Récupération des informations réelles de l'étudiant connecté
      $info_etudiant = [];
      if ($role_actuel == 'Etudiant' && $id_actuel) {
          $sql_profil = "SELECT E.*, C.email, C.telephone 
                         FROM ETUDIANT E 
                         LEFT JOIN COMPTE_UTILISATEUR C ON E.id_compte = C.id_compte 
                         WHERE E.id_etudiant = ?";
          $stmt_profil = mysqli_prepare($conn, $sql_profil);
          mysqli_stmt_bind_param($stmt_profil, "i", $id_actuel);
          mysqli_stmt_execute($stmt_profil);
          $res_profil = mysqli_stmt_get_result($stmt_profil);
          if ($row = mysqli_fetch_assoc($res_profil)) {
              $info_etudiant = $row;
          }
      }
      ?>

      <div class="container-parametres" style="padding: 20px; background-color: #fff; border-radius: 8px; margin-top: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 20px; color: #333;">Mes Informations Personnelles</h2>
        
        <div id="vue_lecture_profil">
            <div class="infos-profil" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px;">
              <div><strong>Nom :</strong> <br><span><?php echo htmlspecialchars($info_etudiant['nom'] ?? 'Non défini'); ?></span></div>
              <div><strong>Prénom :</strong> <br><span><?php echo htmlspecialchars($info_etudiant['prenom'] ?? 'Non défini'); ?></span></div>
              <div><strong>ID ÉTUDIANT :</strong> <br><span><?php echo htmlspecialchars($info_etudiant['numero_etudiant'] ?? 'Non défini'); ?></span></div>
              <div><strong>Email :</strong> <br><span><?php echo htmlspecialchars($info_etudiant['email_etu'] ?? 'Non défini'); ?></span></div>
              <div><strong>Téléphone :</strong> <br><span><?php echo htmlspecialchars($info_etudiant['telephone'] ?? 'Non défini'); ?></span></div>
              <div><strong>Date de naissance :</strong> <br><span><?php echo htmlspecialchars($info_etudiant['date_naissance'] ?? 'Non définie'); ?></span></div>
              <div><strong>Genre :</strong> <br><span><?php echo htmlspecialchars($info_etudiant['genre'] ?? 'Non défini'); ?></span></div>
              <div><strong>Adresse :</strong> <br><span><?php echo htmlspecialchars($info_etudiant['adresse'] ?? 'Non définie'); ?></span></div>
              <div><strong>Nationalité :</strong> <br><span><?php echo htmlspecialchars($info_etudiant['nationalite'] ?? 'Non définie'); ?></span></div>
              <div><strong>Niveau :</strong> <br><span><?php echo htmlspecialchars($info_etudiant['niveau'] ?? 'Non défini'); ?></span></div>
              <div><strong>Année académique :</strong> <br><span><?php echo htmlspecialchars($info_etudiant['annee_academique'] ?? 'Non définie'); ?></span></div>
            </div>

            <div class="actions-parametres" style="text-align: right; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
              <button id="btn_modifier_profil" class="btn-submit" style="padding: 10px 20px; background-color: #0056b3; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                Modifier mes informations
              </button>
            </div>
        </div>

        <div id="vue_modification_profil" style="display: none;">
            <form method="post" action="modifier_mon_profil.php">
                <input type="hidden" name="id_etudiant" value="<?php echo $id_actuel; ?>">
                
                <div class="infos-profil" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px;">
                    
                    <div class="input-group">
                        <label>Nom (Bloqué par la scolarité)</label>
                        <input type="text" value="<?php echo htmlspecialchars($info_etudiant['nom'] ?? ''); ?>" readonly style="background-color: #e9ecef; cursor: not-allowed; border: 1px solid #ccc; padding: 10px; border-radius: 5px; width: 100%; box-sizing: border-box;">
                    </div>
                    <div class="input-group">
                        <label>Prénom (Bloqué par la scolarité)</label>
                        <input type="text" value="<?php echo htmlspecialchars($info_etudiant['prenom'] ?? ''); ?>" readonly style="background-color: #e9ecef; cursor: not-allowed; border: 1px solid #ccc; padding: 10px; border-radius: 5px; width: 100%; box-sizing: border-box;">
                    </div>
                    <div class="input-group">
                        <label>N° Étudiant (Bloqué)</label>
                        <input type="text" value="<?php echo htmlspecialchars($info_etudiant['numero_etudiant'] ?? ''); ?>" readonly style="background-color: #e9ecef; cursor: not-allowed; border: 1px solid #ccc; padding: 10px; border-radius: 5px; width: 100%; box-sizing: border-box;">
                    </div>
                    <div class="input-group">
                        <label>Email académique (Bloqué)</label>
                        <input type="email" value="<?php echo htmlspecialchars($info_etudiant['email_etu'] ?? ''); ?>" readonly style="background-color: #e9ecef; cursor: not-allowed; border: 1px solid #ccc; padding: 10px; border-radius: 5px; width: 100%; box-sizing: border-box;">
                    </div>
                    <div class="input-group">
                        <label>Niveau (Bloqué)</label>
                        <input type="text" value="<?php echo htmlspecialchars($info_etudiant['niveau'] ?? ''); ?>" readonly style="background-color: #e9ecef; cursor: not-allowed; border: 1px solid #ccc; padding: 10px; border-radius: 5px; width: 100%; box-sizing: border-box;">
                    </div>

                    <div class="input-group">
                        <label>Téléphone</label>
                        <input type="tel" name="maj_telephone" value="<?php echo htmlspecialchars($info_etudiant['telephone'] ?? ''); ?>" style="border: 1px solid #0056b3; padding: 10px; border-radius: 5px; width: 100%; box-sizing: border-box;">
                    </div>
                    <div class="input-group" style="grid-column: span 2;">
                        <label>Adresse Postale</label>
                        <input type="text" name="maj_adresse" value="<?php echo htmlspecialchars($info_etudiant['adresse'] ?? ''); ?>" style="border: 1px solid #0056b3; padding: 10px; border-radius: 5px; width: 100%; box-sizing: border-box;">
                    </div>
                </div>

                <div class="actions-parametres" style="text-align: right; border-top: 1px solid #eee; padding-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" id="btn_annuler_profil" style="padding: 10px 20px; background-color: #CDCDCD; color: #333; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                        Annuler
                    </button>
                    <button type="submit" name="btn_sauvegarder_profil" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>

      </div>
    </div>
  </div>

  <script>
    // Petit script JavaScript pour basculer de la vue lecture au formulaire
    document.addEventListener('DOMContentLoaded', function() {
        const btnModifier = document.getElementById('btn_modifier_profil');
        const btnAnnuler = document.getElementById('btn_annuler_profil');
        const vueLecture = document.getElementById('vue_lecture_profil');
        const vueModif = document.getElementById('vue_modification_profil');

        if(btnModifier && btnAnnuler && vueLecture && vueModif) {
            btnModifier.addEventListener('click', function() {
                vueLecture.style.display = 'none';
                vueModif.style.display = 'block';
            });
            btnAnnuler.addEventListener('click', function() {
                vueModif.style.display = 'none';
                vueLecture.style.display = 'block';
            });
        }
    });
  </script>
<div class="enseignants" style="display: none;">
    <div class="contenu1">
      <div id="vue_dashboard_profs">
        <div class="haut1">
          <div class="gauche">
            <strong>Gestion des enseignants</strong>
          </div>
          <div class="droite">
            <div class="recherche">
              <input type="text" placeholder="Rechercher un professeur...">
              <div class="btn-filtre" id="Bouton_filtre_prof">
                <img src="filtre.png" height="15" alt="Filtre"> Filtre
              </div>
			  <!-- Popup Filtre Professeurs -->
<div id="menu_filtre_prof" class="menu-filtre-popup" style="display: none;">
  <h4>Filtrer les enseignants</h4>

  <div class="groupe-filtre">
    <strong>Département</strong>
    <label><input type="checkbox" class="filtre-prof" data-champ="departement" value="Informatique"> Informatique</label>
    <label><input type="checkbox" class="filtre-prof" data-champ="departement" value="Mathématique"> Mathématique</label>
    <label><input type="checkbox" class="filtre-prof" data-champ="departement" value="Physique"> Physique</label>
    <label><input type="checkbox" class="filtre-prof" data-champ="departement" value="Electronique"> Electronique</label>
    <label><input type="checkbox" class="filtre-prof" data-champ="departement" value="Anglais"> Anglais</label>
    <label><input type="checkbox" class="filtre-prof" data-champ="departement" value="Espagnol"> Espagnol</label>
  </div>


  <div style="display:flex; gap:10px; margin-top:10px;">
    <button class="btn-appliquer-filtre" id="btn_appliquer_filtre_prof">Appliquer</button>
    <button id="btn_reset_filtre_prof" style="padding:8px 15px; background:#CDCDCD; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">Réinitialiser</button>
  </div>
</div>
            </div>
            <div class="btn">
             <div id="Ajouter_prof"><img src="plus.png" height="15" alt="PLUS"> Ajouter un prof</div>
            </div>
          </div>
        </div>
        
       <div class="haut21">
          <?php
          // 1. Requête SQL pour les profs (avec LEFT JOIN pour récupérer l'email et le téléphone)
          $sql_profs = "SELECT P.*, C.email, C.telephone 
                        FROM ENSEIGNANT P 
                        LEFT JOIN COMPTE_UTILISATEUR C ON P.id_compte = C.id_compte";
          $resultat_profs = mysqli_query($conn, $sql_profs);

          if (!$resultat_profs) {
              echo "<div style='color: red; padding: 20px;'>Erreur SQL : " . mysqli_error($conn) . "</div>";
          } else {
              if (mysqli_num_rows($resultat_profs) == 0) {
                  echo "<div style='padding: 30px; color: #666; width: 100%; text-align: center;'>Aucun enseignant enregistré.</div>";
              }

              // 2. La boucle d'affichage pour les professeurs
              while ($prof = mysqli_fetch_assoc($resultat_profs)) {
                 $id_db_prof = $prof['id_enseignant']; 
                  $nom_prof = htmlspecialchars($prof['prenom'] . ' ' . $prof['nom']);
                  $emailprof = htmlspecialchars($prof['email'] ?? 'Pas d\'email');
                  $tel_prof = htmlspecialchars($prof['tel_prof'] ?? 'Pas de téléphone');
                  $departement = htmlspecialchars($prof['departement'] ?? 'Non assigné');
              ?>
              
              <div class="bloc-prof-complet"
     data-departement="<?php echo htmlspecialchars($prof['departement'] ?? ''); ?>"
     data-statut="<?php echo htmlspecialchars($prof['statut'] ?? 'Actif'); ?>"
     style="display: flex; gap: 20px; width: 100%; margin-bottom: 20px; flex-wrap: wrap;">
                  <div class="profil" style="margin: 0; flex: 1;">
                    <div class="caracteristiques">
                      <strong><?php echo $nom_prof; ?></strong><br> 
                      ID PROF : <?php echo $id_db_prof; ?><br> 
                      <?php echo $emailprof; ?><br>
                      <?php echo $tel_prof; ?>
                    </div>
                    <div class="barriere"></div>
                    <div class="statut">
                      <div class="ctn1">
                        <strong>Département</strong><br> <?php echo $departement; ?><br><br>
                        <strong>Statut</strong><br> Actif<br><br>
                      </div>
                      <div class="ctn2">
                         </div>
                    </div>
                  </div>

                  <div class="action_rapide" style="margin-left: 20px; width: 250px;">
                    <strong>Actions</strong>
                    
                    <a href="modifier_prof.php?id=<?php echo $id_db_prof; ?>" class="rapide" style="background-color: #e6f2ff; color: #0056b3; text-decoration: none; display: block; text-align: center;">
                      Modifier les informations
                    </a>
                    
                    <a href="supprimer_prof.php?id=<?php echo $id_db_prof; ?>" class="rapide" style="background-color: #ffcccc; color: #cc0000; text-decoration: none; display: block; text-align: center;">
                      Supprimer l'enseignant
                    </a>
                    
<div class="rapide btn-voir-edt-prof" data-id="<?php echo $id_db_prof; ?>" data-nom="<?php echo htmlspecialchars($prof['prenom'].' '.$prof['nom']); ?>" style="text-align: center; cursor: pointer;">Voir Cours associés</div>
                  </div>
              </div>

          <?php
              }
          }
          ?>
        </div>
		
		<div id="bloc_edt_prof" style="display:none; background:white; border-radius:10px; padding:20px; margin-top:20px; box-shadow:0 2px 5px rgba(0,0,0,0.05);">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
    <h3 id="titre_edt_prof" style="margin:0; color:#0056b3;"></h3>
    <button onclick="document.getElementById('bloc_edt_prof').style.display='none';" style="background:#CDCDCD; border:none; padding:8px 15px; border-radius:5px; cursor:pointer; font-weight:bold;">✕ Fermer</button>
  </div>
  <div id="liste_cours_prof" style="display:flex; flex-direction:column; gap:10px; max-height:400px; overflow-y:auto;"></div>
</div>
      </div>
      <div id="formulaire_ajout_prof" style="display: none;">
        <div class="haut1">
          <div class="gauche"><strong>Ajouter un Enseignant</strong></div>
         <div class="droite"><div class="btn"><div id="Retour_dashboard_prof" onclick="document.getElementById('formulaire_ajout_prof').style.display='none'; document.getElementById('vue_dashboard_profs').style.display='block';" style="background: #CDCDCD; padding: 8px 15px; border-radius: 7px; font-weight: bold; cursor: pointer;">Retour aux enseignants</div></div></div>
        </div>

        <form method="post" action="ajouter_prof.php">
          <div class="form-container">
            <div class="struct">
              <h3>Informations de l'enseignant</h3>
              
              <div class="input-group">
                <label>Nom</label>
                <input type="text" name="ajout_nom_prof" placeholder="Ex: Dubois" required>
              </div>
              <div class="input-group">
                <label>Prénom</label>
                <input type="text" name="ajout_prenom_prof" placeholder="Ex: Thomas" required>
              </div>
			  <div class="input-group">
                <label for="genre">Genre</label>
                <select id="genre" name="ajout_genre_prof" required>
                  <option value="">-- Sélectionner --</option>
                  <option value="Homme">Homme</option>
                  <option value="Femme">Femme</option>
                  <option value="Autre">Autre</option>
                </select>
              </div>
			  <div class="input-group">
                <label for="date_naissance">Date de naissance</label>
                <input type="date" id="date_naissance" name="ajout_date_naissance_prof" required>
              </div>
              <div class="input-group">
                <label>Email</label>
                <input type="email" name="ajout_email_prof" placeholder="thomas.dubois@ecole.com" required>
              </div>
              <div class="input-group">
                <label>Téléphone</label>
                <input type="tel" name="ajout_tel_prof" placeholder="06......../07........" required>
              </div>
			  <div class="input-group">
                <label>Adresse</label>
                <input type="text" name="ajout_adresse_prof" placeholder="Ex: 34 Rue Gustave Eiffel" required>
              </div>
			  
              <div class="input-group">
                <label>Département</label>
                <select name="ajout_departement_prof" required>
				<option value="">-- Selectionnez --</option>
                  <option value="Informatique">Informatique</option>
                  <option value="Mathématique">Mathématique</option>
                  <option value="Physique">Physique</option>
                  <option value="Electronique">Electronique</option>
				  <option value="Anglais">Anglais</option>
				  <option value="Espagnol">Espagnol</option>
                </select>
              </div>
              
              <button type="submit" name="button_ajouter_prof" class="btn-submit" style="margin-top: 15px;">Ajouter le professeur</button>
            </div>
          </div>
        </form>
      </div>
     
    </div>
  </div>
  <div class="cours-admin" style="display: none;">
    <div class="contenu1">
      
      <div id="vue_dashboard_cours">
        <div class="haut1">
          <div class="gauche">
            <strong>Gestion des Cours</strong>
          </div>
          <div class="droite">
            <div class="recherche">
             <input type="text" placeholder="Rechercher un cours..." oninput="filtrerCours(this.value)">
              <div class="btn-filtre" id="Bouton_filtre_cours">
                <img src="filtre.png" height="15" alt="Filtre"> Filtres/Tris
              </div>
			  <div id="menu_filtre_cours" class="menu-filtre-popup" style="display: none;">
  <h4>Filtrer les cours</h4>

  <div class="groupe-filtre">
    <strong>Niveau</strong>
    <label><input type="checkbox" class="filtre-cours" data-champ="niveau" value="ING1"> ING1</label>
    <label><input type="checkbox" class="filtre-cours" data-champ="niveau" value="ING2"> ING2</label>
    <label><input type="checkbox" class="filtre-cours" data-champ="niveau" value="ING3"> ING3</label>
    <label><input type="checkbox" class="filtre-cours" data-champ="niveau" value="ING4_Systeme"> ING4 - Système</label>
    <label><input type="checkbox" class="filtre-cours" data-champ="niveau" value="ING4_Cyber"> ING4 - Cyber</label>
    <label><input type="checkbox" class="filtre-cours" data-champ="niveau" value="ING4_Finance"> ING4 - Finance</label>
    <label><input type="checkbox" class="filtre-cours" data-champ="niveau" value="ING4_Energie"> ING4 - Energie</label>
	<label><input type="checkbox" class="filtre-cours" data-champ="niveau" value="ING5_Systeme"> ING5 - Système</label>
    <label><input type="checkbox" class="filtre-cours" data-champ="niveau" value="ING5_Cyber"> ING5 - Cyber</label>
    <label><input type="checkbox" class="filtre-cours" data-champ="niveau" value="ING5_Finance"> ING5 - Finance</label>
    <label><input type="checkbox" class="filtre-cours" data-champ="niveau" value="ING5_Energie"> ING5 - Energie</label>
  </div>

  <div class="groupe-filtre">
    <strong>Horaire</strong>
    <label><input type="checkbox" class="filtre-cours" data-champ="horaire" value="8h00"> 8:00 - 10:00</label>
    <label><input type="checkbox" class="filtre-cours" data-champ="horaire" value="10h00"> 10:00 - 12:00</label>
    <label><input type="checkbox" class="filtre-cours" data-champ="horaire" value="12h30"> 12:30 - 14:30</label>
    <label><input type="checkbox" class="filtre-cours" data-champ="horaire" value="14h30"> 14:30 - 16:30</label>
    <label><input type="checkbox" class="filtre-cours" data-champ="horaire" value="17h00"> 17:00 - 19:00</label>
  </div>

  <div class="groupe-filtre">
    <strong>Date</strong>
    <input type="date" id="filtre_date_cours" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; margin-top:5px;">
  </div>

  <div class="groupe-filtre">
    <strong>Salle</strong>
    <select id="filtre_salle_cours" style="width:100%; padding:6px; border:1px solid #ccc; border-radius:4px; margin-top:5px;">
      <option value="">-- Toutes les salles --</option>
      <option value="EM01">EM01</option>
      <option value="EM02">EM02</option>
      <option value="EM03">EM03</option>
      <option value="EM04">EM04</option>
      <option value="EM05">EM05</option>
      <option value="EM06">EM06</option>
      <option value="EM07">EM07</option>
      <option value="EM08">EM08</option>
      <option value="EM09">EM09</option>
      <option value="EM10">EM10</option>
      <option value="EM11">EM11</option>
      <option value="EM12">EM12</option>
      <option value="EM13">EM13</option>
      <option value="EM14">EM14</option>
      <option value="EM15">EM15</option>
      <option value="EM16">EM16</option>
      <option value="EM17">EM17</option>
      <option value="EM18">EM18</option>
      <option value="EM19">EM19</option>
      <option value="EM20">EM20</option>
      <option value="SC01">SC01</option>
      <option value="SC02">SC02</option>
      <option value="SC03">SC03</option>
      <option value="SC04">SC04</option>
      <option value="SC05">SC05</option>
      <option value="SC06">SC06</option>
      <option value="SC07">SC07</option>
      <option value="SC08">SC08</option>
      <option value="SC09">SC09</option>
      <option value="SC10">SC10</option>
      <option value="SC11">SC11</option>
      <option value="SC12">SC12</option>
      <option value="SC13">SC13</option>
      <option value="SC14">SC14</option>
      <option value="SC15">SC15</option>
      <option value="SC16">SC16</option>
      <option value="SC17">SC17</option>
      <option value="SC18">SC18</option>
      <option value="SC19">SC19</option>
      <option value="SC20">SC20</option>
    </select>
  </div>

  <div style="display:flex; gap:10px; margin-top:10px;">
    <button class="btn-appliquer-filtre" id="btn_appliquer_filtre_cours">Appliquer</button>
    <button id="btn_reset_filtre_cours" style="padding:8px 15px; background:#CDCDCD; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">Réinitialiser</button>
  </div>
</div>
            </div>
            <div class="btn">
             <div id="Créer_Cours" onclick="document.getElementById('vue_dashboard_cours').style.display='none'; document.getElementById('formulaire_ajout_cours').style.display='block';" style="cursor: pointer;">
                <img src="plus.png" height="15" alt="PLUS"> Créer un Cours
              </div>
            </div>
          </div>
        </div>
        
        <div class="haut21">
          <?php
          // 1. Requête SQL pour récupérer les cours ET le nom du professeur
          $sql_cours = "SELECT C.*, E.nom AS prof_nom, E.prenom AS prof_prenom 
                        FROM COURS C 
                        LEFT JOIN ENSEIGNANT E ON C.id_enseignant = E.id_enseignant";
          $resultat_cours = mysqli_query($conn, $sql_cours);

          if (!$resultat_cours) {
              echo "<div style='color: red; padding: 20px;'>Erreur SQL : " . mysqli_error($conn) . "</div>";
          } else {
              if (mysqli_num_rows($resultat_cours) == 0) {
                  echo "<div style='padding: 30px; color: #666; width: 100%; text-align: center;'>Aucun cours n'a été créé pour le moment.</div>";
              }

              // 2. La boucle d'affichage
              while ($cours = mysqli_fetch_assoc($resultat_cours)) {
    $id_cours = $cours['id_cours'];
    $titre = htmlspecialchars($cours['nom_matiere'] ?? 'Titre inconnu');
    $code = htmlspecialchars($cours['code_cours'] ?? 'N/A');
    $dept = htmlspecialchars($cours['departement'] ?? 'N/A');
    $heure = htmlspecialchars($cours['heure'] ?? '0');
    $date_cours = htmlspecialchars($cours['date_cours'] ?? '0');
    $salle = htmlspecialchars($cours['Salle'] ?? '0');
    
    if (!empty($cours['prof_nom'])) {
        $prof_assigne = htmlspecialchars($cours['prof_prenom'] . ' ' . $cours['prof_nom']);
    } else {
        $prof_assigne = "<span style='color: #cc0000; font-style: italic;'>Non assigné</span>";
    }

    $niveau = htmlspecialchars($cours['niveau'] ?? 'N/A');
    $semestre = htmlspecialchars($cours['semestre'] ?? 'N/A');
?>

<div class="bloc-cours-complet"
     data-niveau="<?php echo htmlspecialchars($cours['niveau'] ?? ''); ?>"
     data-horaire="<?php echo htmlspecialchars($cours['heure'] ?? ''); ?>"
     data-date="<?php echo htmlspecialchars($cours['date_cours'] ?? ''); ?>"
     data-salle="<?php echo strtolower(htmlspecialchars($cours['Salle'] ?? '')); ?>"
     style="display: flex; gap: 20px; width: 100%; margin-bottom: 20px; flex-wrap: wrap;">

    <div class="profil" style="margin: 0; flex: 1;">
        <div class="caracteristiques">
            <strong><?php echo $titre; ?></strong><br>
            CODE : <?php echo $code; ?><br>
            Département : <?php echo $dept; ?><br>
        </div>
        <div class="barriere"></div>
        <div class="statut">
            <div class="ctn1">
                <strong>Enseignant assigné</strong><br> <?php echo $prof_assigne; ?><br><br>
                <strong>Niveau</strong><br> <?php echo $niveau; ?><br><br>
            </div>
            <div class="ctn2">
                <strong>Date</strong><br> <?php echo $date_cours; ?><br><br>
                <strong>Horaire</strong><br> <?php echo $heure; ?><br><br>
            </div>
            <div class="ctn3">
                <strong>Salle</strong><br> <?php echo $salle; ?><br><br>
                <strong>Semestre</strong><br> <?php echo $semestre; ?>
            </div>
        </div>
    </div>

    <div class="action_rapide" style="margin-left: 20px; width: 250px;">
        <strong>Actions</strong>
        <a href="modifier_cours.php?id=<?php echo $id_cours; ?>" class="rapide" style="background-color: #e6f2ff; color: #0056b3; text-decoration: none; display: block; text-align: center;">Modifier les informations</a>
        <a href="supprimer_cours.php?id=<?php echo $id_cours; ?>" class="rapide" style="background-color: #ffcccc; color: #cc0000; text-decoration: none; display: block; text-align: center;">Supprimer le cours</a>
    </div>

</div>

<?php
}
          }
          ?>
        </div>
      </div> 
	  </div>
	  
      <div id="formulaire_ajout_cours" style="display: none;">
        <div class="haut1">
          <div class="gauche"><strong>Créer un Cours</strong></div>
          <div class="droite">
            <div class="btn">
              <div id="Retour_dashboard_cours" onclick="document.getElementById('formulaire_ajout_cours').style.display='none'; document.getElementById('vue_dashboard_cours').style.display='block';" style="background: #CDCDCD; padding: 8px 15px; border-radius: 7px; font-weight: bold; cursor: pointer;">Retour aux cours</div>
            </div>
          </div>
        </div>

        <form method="post" action="ajouter_cours.php">
          <div class="form-container">
            
            <div class="struct">
              <h3>Informations générales</h3>
              <div class="input-group">
                <label for="titre_cours">Titre du cours</label>
                <input type="text" id="titre_cours" name="titre_cours" placeholder="Ex: Base de données" required>
              </div>
              <div class="input-group">
                <label for="code_cours">Code du cours</label>
                <input type="text" id="code_cours" name="code_cours" placeholder="Ex: INFO-202" required>
              </div>
              
              <div class="input-group">
                <label for="dep_cours">Catégorie (Département)</label>
                <select id="dep_cours" name="dep_cours" required>
                  <option value="">-- Sélectionner une filière --</option>
                  <option value="Informatique">Informatique</option>
                  <option value="Mathématique">Mathématique</option>
                  <option value="Physique">Physique</option>
                  <option value="Electronique">Electronique</option>
                  <option value="Anglais">Anglais</option>
                  <option value="Espagnol">Espagnol</option>
                </select>
              </div>
            </div>

            <div class="struct">
              <h3>Organisation</h3>
              
              <div class="input-group">
                <label for="capacite_max">Capacité maximale d'élèves</label>
                <input type="number" id="capacite_max" name="capacite_max" min="1" value="30" required style="border: 2px solid #0056b3; border-radius: 5px; padding: 8px;">
              </div>
              
              
              <div class="input-group">
                <label for="prof_cours">Associer un enseignant</label>
                <select id="prof_cours" name="prof_cours" style="background-color: #f9f9f9; border-left: 3px solid #0056b3;">
                  <option value="">-- Choisissez d'abord une catégorie --</option>
                </select>
              </div>

              <div class="input-group">
                <label for="semestre_cours">Niveau Actuel</label>
                <select id="semestre_cours" name="semestre_cours" required>
                  <option value="">-- Sélectionner --</option>
                  <option value="ING1">ING1</option>
                  <option value="ING2">ING2</option>
                  <option value="ING3">ING3</option>
                  <option value="ING4_Systeme">ING4 - Système Embarqué</option>
                  <option value="ING4_Cyber">ING4 - CyberSécurité</option>
                  <option value="ING4_Finance">ING4 - Finance</option>
                  <option value="ING4_Energie">ING4 - Energie</option>
                  <option value="ING5_Systeme">ING5 - Système Embarqué</option>
                  <option value="ING5_Cyber">ING5 - CyberSécurité</option>
                  <option value="ING5_Finance">ING5 - Finance</option>
                  <option value="ING5_Energie">ING5 - Energie</option>
                </select>
              </div>
			  <div class="input-group">
                <label for="date_cours">Date</label>
                <input type="date" id="date_cours" name="date_cours" required>
              </div>
              <div class="input-group">
                <label for="horaire_cours">Horaire</label>
                <select id="horaire_cours" name="horaire_cours" required>
				<option value="">-- Selectionnez --</option>
                  <option value="8h00">8:00 - 10:00</option>
                  <option value="10h00">10:00 - 12:00</option>
                  <option value="12h30">12:30 - 14:30</option>
                  <option value="14h30">14:30 - 16:30</option>
                  <option value="17h00">17:00 - 19:00</option>
                </select>
              </div>
			  <div class="input-group">
                <label for="salle_cours">Salle </label>
                <select id="salle_cours" name="salle_cours" required>
                  <option value="">-- Sélectionner une classe --</option>
                  <option value="EM01">EM01</option>
                  <option value="EM02">EM02</option>
                  <option value="EM03">EM03</option>
                  <option value="EM04">EM04</option>
                  <option value="EM05">EM05</option>
                  <option value="EM06">EM06</option>
				  <option value="EM07">EM07</option>
                  <option value="EM08">EM08</option>
                  <option value="EM09">EM09</option>
                  <option value="EM10">EM10</option>
                  <option value="EM11">EM11</option>
                  <option value="EM12">EM12</option>
				  <option value="EM13">EM13</option>
                  <option value="EM14">EM14</option>
                  <option value="EM15">EM15</option>
                  <option value="EM16">EM16</option>
                  <option value="EM17">EM17</option>
                  <option value="EM18">EM18</option>
				  <option value="EM19">EM19</option>
                  <option value="EM20">EM20</option>
                  <option value="SC01">SC01</option>
                  <option value="SC02">SC02</option>
                  <option value="SC03">SC03</option>
                  <option value="SC04">SC04</option>
                  <option value="SC05">SC05</option>
                  <option value="SC06">SC06</option>
				  <option value="SC07">SC07</option>
                  <option value="SC08">SC08</option>
                  <option value="SC09">SC09</option>
                  <option value="SC10">SC10</option>
                  <option value="SC11">SC11</option>
                  <option value="SC12">SC12</option>
				  <option value="SC13">SC13</option>
                  <option value="SC14">SC14</option>
                  <option value="SC15">SC15</option>
                  <option value="SC16">SC16</option>
                  <option value="SC17">SC17</option>
                  <option value="SC18">SC18</option>
				  <option value="SC19">SC19</option>
                  <option value="SC20">SC20</option>
                </select>
              </div>
			  
              <button type="submit" name="button_ajouter_cours" class="btn-submit">Créer le cours</button>
            </div>
          </div>
        </form>

        <?php
        // --- PRÉPARATION DES DONNÉES EN PHP POUR LE JAVASCRIPT ---
        // On récupère tous les profs et on les range par département dans un tableau PHP
        $sql_profs_filtre = "SELECT id_enseignant, nom, prenom, departement FROM ENSEIGNANT WHERE departement IS NOT NULL";
        $res_profs_filtre = mysqli_query($conn, $sql_profs_filtre);
        $dictionnaire_profs = [];
        
        if ($res_profs_filtre) {
            while ($p = mysqli_fetch_assoc($res_profs_filtre)) {
                $dept = $p['departement'];
                if (!isset($dictionnaire_profs[$dept])) {
                    $dictionnaire_profs[$dept] = [];
                }
                $dictionnaire_profs[$dept][] = [
                    'id' => $p['id_enseignant'],
                    'nom' => htmlspecialchars($p['nom'] . ' ' . $p['prenom'])
                ];
            }
        }
        ?>

        <script>
          document.addEventListener('DOMContentLoaded', function() {
              // json_encode transforme le tableau PHP en un objet compréhensible par JavaScript
              const profsParDept = <?php echo json_encode($dictionnaire_profs); ?>;
              
              const selectCategorie = document.getElementById('dep_cours');
              const selectProf = document.getElementById('prof_cours');

              if(selectCategorie && selectProf) {
                  // On écoute le moment où l'utilisateur change la catégorie
                  selectCategorie.addEventListener('change', function() {
                      const categorieChoisie = this.value;
                      
                      // On efface la liste actuelle des profs
                      selectProf.innerHTML = '<option value="">-- Aucun enseignant sélectionné --</option>';
                      
                      // Si la catégorie choisie existe dans notre dictionnaire et qu'elle contient des profs
                      if(categorieChoisie && profsParDept[categorieChoisie]) {
                          const profs = profsParDept[categorieChoisie];
                          
                          // On ajoute chaque prof correspondant dans le menu déroulant
                          profs.forEach(function(prof) {
                              const option = document.createElement('option');
                              option.value = prof.id;
                              option.textContent = prof.nom;
                              selectProf.appendChild(option);
                          });
                      } else if (categorieChoisie) {
                          // S'il n'y a pas de prof pour cette matière spécifique
                          selectProf.innerHTML = '<option value="">-- Aucun prof disponible pour cette matière --</option>';
                      } else {
                          // Si l'utilisateur remet le select sur "Sélectionner une filière"
                          selectProf.innerHTML = '<option value="">-- Choisissez d\'abord une catégorie --</option>';
                      }
                  });
              }
          });
        </script>
      </div>
	  </div>

  
  <div class="inscriptions-admin" style="display: none;">
    <div class="contenu1">
      
      <div id="vue_dashboard_inscriptions">
        <div class="haut1">
          <div class="gauche"><strong>Gestion des Inscriptions</strong></div>
          </div>
        
        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
          
          <div class="haut21" style="flex: 1; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); min-width: 300px; max-height: 600px; overflow-y: auto;">
            <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #0056b3; padding-bottom: 10px;">Catalogue des Cours</h3>
            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
              <?php
              $sql_tous_cours = "SELECT id_cours, nom_matiere, code_cours, niveau FROM COURS ORDER BY nom_matiere ASC";
              $res_cours = mysqli_query($conn, $sql_tous_cours);
              $liste_cours = [];
              
              if ($res_cours && mysqli_num_rows($res_cours) > 0) {
                  while ($c = mysqli_fetch_assoc($res_cours)) {
                      $liste_cours[] = $c; 
                      
                      echo '<div class="tuile-cours-selection" data-idc="' . $c['id_cours'] . '" style="padding: 12px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #0056b3; cursor: pointer; transition: 0.2s;">';
                      echo '<strong style="color: #333; display: block; font-size: 15px;">' . htmlspecialchars($c['nom_matiere']) . '</strong>';
                      echo '<span style="font-size: 12px; color: #666;">Code: ' . htmlspecialchars($c['code_cours']) . ' | Niveau: ' . htmlspecialchars($c['niveau']) . '</span>';
                      echo '</div>';
                  }
              } else {
                  echo '<p style="color: #666; font-style: italic;">Aucun cours n\'a encore été créé.</p>';
              }
              ?>
            </div>
          </div>

          <div class="haut21" style="flex: 2; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); min-width: 500px; max-height: 600px; overflow-y: auto;">
            
            <div id="msg_aucun_cours" style="text-align: center; padding: 80px 20px; color: #666;">
              <span style="font-size: 40px; display: block; margin-bottom: 15px;">👈</span>
              <strong style="font-size: 18px;">Aucun cours sélectionné</strong><br>
              <span style="font-size: 14px; margin-top: 5px; display: block;">Cliquez sur un cours dans la liste à gauche pour afficher les élèves inscrits.</span>
            </div>

            <?php
            $sql_toutes_inscriptions = "SELECT I.id_cours, I.id_etudiant, E.nom, E.prenom, E.numero_etudiant, I.date_inscription 
                                        FROM INSCRIPTION I
                                        JOIN ETUDIANT E ON I.id_etudiant = E.id_etudiant
                                        ORDER BY E.nom ASC, E.prenom ASC";
            $res_insc = mysqli_query($conn, $sql_toutes_inscriptions);
            
            $inscriptions_par_cours = [];
            if ($res_insc) {
                while ($insc = mysqli_fetch_assoc($res_insc)) {
                    $inscriptions_par_cours[$insc['id_cours']][] = $insc;
                }
            }

            foreach ($liste_cours as $c) {
    $idc = $c['id_cours'];
    $niveau_cours = $c['niveau'];

    // ── Calcul capacité (DOIT être avant le bouton) ──
    $stmt_cap2 = mysqli_prepare($conn, "
        SELECT c.capacite_max, COUNT(i.id_etudiant) AS nb_inscrits
        FROM COURS c
        LEFT JOIN INSCRIPTION i ON i.id_cours = c.id_cours
        WHERE c.id_cours = ?
        GROUP BY c.id_cours
    ");
    mysqli_stmt_bind_param($stmt_cap2, "i", $idc);
    mysqli_stmt_execute($stmt_cap2);
    $cap_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cap2));
    $cours_plein  = $cap_data && ($cap_data['nb_inscrits'] >= $cap_data['capacite_max']);
    $places_dispo = $cap_data ? max(0, $cap_data['capacite_max'] - $cap_data['nb_inscrits']) : '?';

    echo '<div id="details_cours_' . $idc . '" class="details-cours-inscriptions" style="display: none;">';

    // ── EN-TÊTE AVEC BOUTON ──
    echo '<div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 10px;">';
    echo '<h3 style="margin: 0; color: #0056b3;">Inscrits en : ' . htmlspecialchars($c['nom_matiere']) . '</h3>';

    if ($cours_plein) {
        echo '<div style="background:#e0e0e0; color:#999; padding:6px 12px; border-radius:5px; font-size:13px; font-weight:bold; cursor:not-allowed;">🔒 Cours complet</div>';
    } else {
        echo '<div class="btn-toggle-inscription" data-idc="'.$idc.'" style="background:#28a745; color:white; padding:6px 12px; border-radius:5px; cursor:pointer; font-size:13px; font-weight:bold;">+ Nouvelle inscription ('.$places_dispo.' place'.($places_dispo > 1 ? 's' : '').')</div>';
    }
    echo '</div>';

    // ── FORMULAIRE CACHÉ ──
    echo '<div id="form_rapide_insc_' . $idc . '" style="display: none; background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 15px; border: 1px solid #ddd;">';
    echo '<form method="post" action="ajouter_inscription.php" style="display: flex; gap: 10px; align-items: flex-end;">';
    echo '<input type="hidden" name="id_cours" value="' . $idc . '">';
    echo '<div style="flex: 1;">';
    echo '<label style="font-size: 13px; font-weight: bold; color: #333; display: block; margin-bottom: 5px;">Sélectionner un étudiant (Niveau ' . htmlspecialchars($niveau_cours) . ') :</label>';
    echo '<select name="id_etudiant" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">';
    echo '<option value="">-- Choisir un étudiant --</option>';

    // ── Étudiants non inscrits ──
    $sql_unreg = "SELECT id_etudiant, nom, prenom, numero_etudiant FROM ETUDIANT WHERE niveau = ? AND id_etudiant NOT IN (SELECT id_etudiant FROM INSCRIPTION WHERE id_cours = ?) ORDER BY nom ASC";
    $stmt_unreg = mysqli_prepare($conn, $sql_unreg);
    mysqli_stmt_bind_param($stmt_unreg, "si", $niveau_cours, $idc);
    mysqli_stmt_execute($stmt_unreg);
    $res_unreg = mysqli_stmt_get_result($stmt_unreg);

    if (mysqli_num_rows($res_unreg) > 0) {
        while ($unreg = mysqli_fetch_assoc($res_unreg)) {
            echo '<option value="'.$unreg['id_etudiant'].'">'.htmlspecialchars($unreg['nom'].' '.$unreg['prenom'].' ('.$unreg['numero_etudiant'].')').'</option>';
        }
    } else {
        echo '<option value="" disabled>Tous les étudiants de ce niveau sont déjà inscrits.</option>';
    }

    echo '</select>';
    echo '</div>';
    echo '<button type="submit" name="button_inscrire" style="background: #0056b3; color: white; border: none; padding: 9px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">Valider</button>';
    echo '</form>';
    echo '</div>';

    // ── TABLEAU DES INSCRITS ──
    if (isset($inscriptions_par_cours[$idc]) && count($inscriptions_par_cours[$idc]) > 0) {
        echo '<table style="width: 100%; border-collapse: collapse; margin-top: 15px; text-align: left;">';
        echo '<thead><tr style="background: #f8f9fa;">';
        echo '<th style="padding: 12px; border-bottom: 2px solid #ddd;">N° Étudiant</th>';
        echo '<th style="padding: 12px; border-bottom: 2px solid #ddd;">Nom Complet</th>';
        echo '<th style="padding: 12px; border-bottom: 2px solid #ddd;">Date Inscription</th>';
        echo '<th style="padding: 12px; border-bottom: 2px solid #ddd; text-align: center;">Action</th>';
        echo '</tr></thead><tbody>';

        foreach ($inscriptions_par_cours[$idc] as $etu) {
            echo '<tr style="border-bottom: 1px solid #eee;">';
            echo '<td style="padding: 12px; color: #666;">' . htmlspecialchars($etu['numero_etudiant'] ?? 'N/A') . '</td>';
            echo '<td style="padding: 12px;"><strong>' . htmlspecialchars($etu['nom'] . ' ' . $etu['prenom']) . '</strong></td>';
            echo '<td style="padding: 12px; color: #666; font-size: 13px;">' . htmlspecialchars($etu['date_inscription']) . '</td>';
            echo '<td style="padding: 12px; text-align: center;">';
            echo '<a href="supprimer_inscription.php?id_etu=' . $etu['id_etudiant'] . '&id_cours=' . $idc . '" style="background: #ffcccc; color: #cc0000; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 12px; font-weight: bold;" onclick="return confirm(\'Voulez-vous vraiment désinscrire cet étudiant ?\');">Désinscrire</a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div style="padding: 40px; text-align: center; color: #999; font-style: italic;">Aucun étudiant n\'est encore inscrit à ce cours.</div>';
    }

    echo '</div>'; // fin details_cours_
}
            ?>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tuilesCours = document.querySelectorAll('.tuile-cours-selection');
        const detailsCours = document.querySelectorAll('.details-cours-inscriptions');
        const msgAucun = document.getElementById('msg_aucun_cours');

        // Gérer le clic sur les cours à gauche
        tuilesCours.forEach(tuile => {
            tuile.addEventListener('click', function() {
                tuilesCours.forEach(t => {
                    t.style.background = '#f8f9fa';
                    t.style.borderLeft = '4px solid #0056b3';
                });
                
                this.style.background = '#e6f2ff';
                this.style.borderLeft = '4px solid #28a745';

                if(msgAucun) msgAucun.style.display = 'none';
                detailsCours.forEach(d => d.style.display = 'none');

                const idc = this.getAttribute('data-idc');
                const targetDiv = document.getElementById('details_cours_' + idc);
                if(targetDiv) targetDiv.style.display = 'block';
            });
        });

        // Gérer le clic sur le bouton "+ Nouvelle inscription"
        document.querySelectorAll('.btn-toggle-inscription').forEach(btn => {
            btn.addEventListener('click', function() {
                const idc = this.getAttribute('data-idc');
                const form = document.getElementById('form_rapide_insc_' + idc);
                if(form.style.display === 'none' || form.style.display === '') {
                    form.style.display = 'block';
                } else {
                    form.style.display = 'none';
                }
            });
        });
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tuilesCours = document.querySelectorAll('.tuile-cours-selection');
        const detailsCours = document.querySelectorAll('.details-cours-inscriptions');
        const msgAucun = document.getElementById('msg_aucun_cours');

        tuilesCours.forEach(tuile => {
            tuile.addEventListener('click', function() {
                // 1. Enlever la mise en évidence des autres cours
                tuilesCours.forEach(t => {
                    t.style.background = '#f8f9fa';
                    t.style.borderLeft = '4px solid #0056b3';
                });
                
                // 2. Mettre en évidence le cours cliqué
                this.style.background = '#e6f2ff';
                this.style.borderLeft = '4px solid #28a745';

                // 3. Cacher le message par défaut et tous les tableaux
                if(msgAucun) msgAucun.style.display = 'none';
                detailsCours.forEach(d => d.style.display = 'none');

                // 4. Afficher le tableau correspondant au cours cliqué
                const idc = this.getAttribute('data-idc');
                const targetDiv = document.getElementById('details_cours_' + idc);
                if(targetDiv) targetDiv.style.display = 'block';
            });
        });
    });
  </script>
<div class="notation_prof" style="display: none;">
  <div class="contenu1">
    <div id="vue_saisie_notes">

      <?php
      $id_cours_choisi = isset($_GET['id_cours_prof']) ? intval($_GET['id_cours_prof']) : 0;
      $type_choisi = isset($_GET['type_eval']) ? $_GET['type_eval'] : '';
      if ($type_choisi && !isset($TYPES_EVAL[$type_choisi])) $type_choisi = '';
      ?>

      <!-- ══ EN-TÊTE ══ -->
      <div class="haut1" style="flex-wrap:wrap; gap:10px;">
        <div class="gauche">
          <strong>Gestion des Notes</strong>
          <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px; align-items:center;">

            <!-- Sélecteur de cours -->
            <select id="select_matiere_prof"
              onchange="window.location.href='index.php?role_simule=Professeur&id=<?php echo $id_actuel; ?>&onglet=notes&id_cours_prof='+this.value;"
              style="padding:8px 12px; border-radius:8px; border:1px solid #ccc; font-weight:bold; color:#333; min-width:260px;">
              <option value="">-- Sélectionnez un cours --</option>
              <?php
              if ($role_actuel == 'Professeur' && $id_actuel) {
                  $req_c = mysqli_prepare($conn, "SELECT id_cours, nom_matiere, code_cours FROM cours WHERE id_enseignant = ? ORDER BY nom_matiere ASC");
                  mysqli_stmt_bind_param($req_c, "i", $id_actuel);
                  mysqli_stmt_execute($req_c);
                  $res_c = mysqli_stmt_get_result($req_c);
                  while ($c = mysqli_fetch_assoc($res_c)) {
                      $sel = ($c['id_cours'] == $id_cours_choisi) ? 'selected' : '';
                      echo '<option value="'.$c['id_cours'].'" '.$sel.'>'.htmlspecialchars($c['nom_matiere'].' ('.$c['code_cours'].')').'</option>';
                  }
              }
              ?>
            </select>

            <!-- Sélecteur de type d'évaluation -->
            <?php if ($id_cours_choisi > 0): ?>
            <select id="select_type_eval"
              onchange="window.location.href='index.php?role_simule=Professeur&id=<?php echo $id_actuel; ?>&onglet=notes&id_cours_prof=<?php echo $id_cours_choisi; ?>&type_eval='+this.value;"
              style="padding:8px 12px; border-radius:8px; border:1px solid #ccc; font-weight:bold; color:#333;">
              <option value="">-- Type d'évaluation --</option>
              <?php foreach ($TYPES_EVAL as $cle => $info):
                  $sel = ($cle === $type_choisi) ? 'selected' : ''; ?>
                <option value="<?php echo $cle; ?>" <?php echo $sel; ?>>
                  <?php echo htmlspecialchars($info['label'].' (coef '.$info['coef'].')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php endif; ?>

          </div>
        </div>

        <!-- Bouton enregistrer -->
        <?php if ($id_cours_choisi > 0 && $type_choisi): ?>
        <div class="droite">
          <button type="submit" form="form_notes_prof"
            style="background:#28a745; color:white; padding:11px 20px; border:none; border-radius:8px; cursor:pointer; font-weight:bold; font-size:14px; display:flex; align-items:center; gap:8px; box-shadow:0 2px 8px rgba(40,167,69,0.3);">
            💾 Enregistrer les notes
          </button>
        </div>
        <?php endif; ?>
      </div>

      <?php if ($id_cours_choisi > 0): ?>

        <?php
        // ── Statistiques du cours ──
        $nb_inscrits  = dash_val($conn, "SELECT COUNT(*) FROM inscription WHERE id_cours = ?", "i", $id_cours_choisi);
        $infos_cours  = null;
        $s_ic = mysqli_prepare($conn, "SELECT nom_matiere, code_cours FROM cours WHERE id_cours = ?");
        mysqli_stmt_bind_param($s_ic, "i", $id_cours_choisi);
        mysqli_stmt_execute($s_ic);
        $r_ic = mysqli_stmt_get_result($s_ic);
        if ($row_ic = mysqli_fetch_assoc($r_ic)) $infos_cours = $row_ic;

        // Récapitulatif de toutes les évaluations déjà saisies pour ce cours
        $bilan_evals = [];
        $s_be = mysqli_prepare($conn, "
            SELECT n.evaluation, COUNT(n.note) as nb_notes,
                   ROUND(AVG(n.note),2) as moy,
                   MIN(n.note) as mini,
                   MAX(n.note) as maxi
            FROM note n
            WHERE n.id_cours = ? AND n.note IS NOT NULL
            GROUP BY n.evaluation
        ");
        mysqli_stmt_bind_param($s_be, "i", $id_cours_choisi);
        mysqli_stmt_execute($s_be);
        $r_be = mysqli_stmt_get_result($s_be);
        while ($row_be = mysqli_fetch_assoc($r_be)) $bilan_evals[$row_be['evaluation']] = $row_be;
        ?>

        <!-- ══ BANDEAU STATISTIQUES ══ -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-top:20px;">
          <div style="background:white; border-radius:12px; padding:16px 20px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:5px solid #0056b3; display:flex; flex-direction:column; gap:4px;">
            <span style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:.5px;">Cours</span>
            <span style="font-size:15px; font-weight:bold; color:#222;"><?php echo htmlspecialchars($infos_cours['nom_matiere'] ?? '—'); ?></span>
            <span style="font-size:12px; color:#666;"><?php echo htmlspecialchars($infos_cours['code_cours'] ?? ''); ?></span>
          </div>
          <div style="background:white; border-radius:12px; padding:16px 20px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:5px solid #6f42c1;">
            <span style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:.5px;">Étudiants</span>
            <div style="font-size:30px; font-weight:bold; color:#6f42c1; margin-top:4px;"><?php echo $nb_inscrits; ?></div>
            <span style="font-size:12px; color:#666;">inscrits au cours</span>
          </div>
          <div style="background:white; border-radius:12px; padding:16px 20px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:5px solid #fd7e14;">
            <span style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:.5px;">Évaluations</span>
            <div style="font-size:30px; font-weight:bold; color:#fd7e14; margin-top:4px;"><?php echo count($bilan_evals); ?></div>
            <span style="font-size:12px; color:#666;">types saisis</span>
          </div>
          <?php if ($type_choisi && isset($bilan_evals[$type_choisi])): $b = $bilan_evals[$type_choisi]; ?>
          <div style="background:white; border-radius:12px; padding:16px 20px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:5px solid #28a745;">
            <span style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:.5px;">Moy. <?php echo htmlspecialchars($type_choisi); ?></span>
            <div style="font-size:30px; font-weight:bold; color:<?php echo ($b['moy']>=10)?'#28a745':'#cc0000'; ?>; margin-top:4px;"><?php echo $b['moy']; ?><span style="font-size:14px; color:#aaa;">/20</span></div>
            <span style="font-size:12px; color:#666;"><?php echo $b['nb_notes']; ?> notes · min <?php echo $b['mini']; ?> · max <?php echo $b['maxi']; ?></span>
          </div>
          <?php endif; ?>
        </div>

        <!-- ══ RÉCAP DE TOUTES LES ÉVALUATIONS ══ -->
        <?php if (count($bilan_evals) > 0): ?>
        <div style="margin-top:20px; background:white; border-radius:12px; padding:18px 20px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
          <h4 style="margin:0 0 14px; color:#333; font-size:14px; text-transform:uppercase; letter-spacing:.5px;">📊 Récapitulatif des évaluations saisies</h4>
          <div style="display:flex; flex-wrap:wrap; gap:10px;">
            <?php foreach ($TYPES_EVAL as $cle => $info):
                if (!isset($bilan_evals[$cle])) continue;
                $b = $bilan_evals[$cle];
                $isActif = ($cle === $type_choisi);
                $url = "index.php?role_simule=Professeur&id={$id_actuel}&onglet=notes&id_cours_prof={$id_cours_choisi}&type_eval=".urlencode($cle);
            ?>
            <a href="<?php echo $url; ?>" style="
                text-decoration:none;
                display:flex; flex-direction:column; gap:3px;
                padding:12px 16px; border-radius:10px;
                border:2px solid <?php echo $isActif ? '#0056b3' : '#e0e0e0'; ?>;
                background:<?php echo $isActif ? '#e8f0fb' : 'white'; ?>;
                min-width:140px; cursor:pointer; transition:all .2s;">
              <span style="font-size:13px; font-weight:bold; color:<?php echo $isActif ? '#0056b3' : '#333'; ?>">
                <?php echo htmlspecialchars($info['label']); ?>
              </span>
              <span style="font-size:11px; color:#666;">coef <?php echo $info['coef']; ?> · <?php echo $b['nb_notes']; ?>/<?php echo $nb_inscrits; ?> notes</span>
              <span style="font-size:13px; font-weight:bold; color:<?php echo ($b['moy']>=10)?'#28a745':'#cc0000'; ?>">
                Moy : <?php echo $b['moy']; ?>/20
              </span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- ══ TABLEAU DE SAISIE (si type choisi) ══ -->
        <?php if ($type_choisi): ?>
        <div style="margin-top:20px; background:white; border-radius:12px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
<?php
// Vérifier si l'évaluation actuelle est verrouillée
$est_verrouille = false;
$s_verr = mysqli_prepare($conn, "SELECT 1 FROM verrouillage_notes WHERE id_cours = ? AND evaluation = ?");
mysqli_stmt_bind_param($s_verr, "is", $id_cours_choisi, $type_choisi);
mysqli_stmt_execute($s_verr);
if (mysqli_fetch_row(mysqli_stmt_get_result($s_verr))) {
    $est_verrouille = true;
}
?>
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; padding-bottom:14px; border-bottom:2px solid #f0f0f0;">
            <div>
              <h3 style="margin:0; color:#0056b3; font-size:16px;">
                <?php echo htmlspecialchars($TYPES_EVAL[$type_choisi]['label']); ?>
                <span style="background:#e8f0fb; color:#0056b3; font-size:12px; padding:3px 8px; border-radius:12px; margin-left:8px; font-weight:normal;">
                  Coefficient <?php echo $TYPES_EVAL[$type_choisi]['coef']; ?>
                </span>
              </h3>
              <p style="margin:4px 0 0; font-size:13px; color:#666;">
                Saisissez ou modifiez les notes ci-dessous. Les cases vides suppriment la note enregistrée.
              </p>
            </div>
            <!-- Progression -->
            <?php
            $nb_notes_saisies = isset($bilan_evals[$type_choisi]) ? $bilan_evals[$type_choisi]['nb_notes'] : 0;
            $pct = $nb_inscrits > 0 ? round($nb_notes_saisies / $nb_inscrits * 100) : 0;
            ?>
            <div style="text-align:center; min-width:90px;">
              <div style="font-size:22px; font-weight:bold; color:#0056b3;"><?php echo $nb_notes_saisies; ?>/<?php echo $nb_inscrits; ?></div>
              <div style="font-size:11px; color:#888; margin-bottom:5px;">notes saisies</div>
              <div style="background:#e9ecef; border-radius:10px; height:6px; overflow:hidden;">
                <div style="background:#0056b3; height:100%; width:<?php echo $pct; ?>%; border-radius:10px; transition:width .4s;"></div>
              </div>
            </div>
          </div>
 <form method="POST" action="ajouter_notes.php" id="form_notes_prof">
            <input type="hidden" name="id_cours"        value="<?php echo $id_cours_choisi; ?>">
            <input type="hidden" name="type_eval"       value="<?php echo htmlspecialchars($type_choisi); ?>">
            <input type="hidden" name="coefficient"     value="<?php echo $TYPES_EVAL[$type_choisi]['coef']; ?>">
            <input type="hidden" name="id_prof_retour"  value="<?php echo $id_actuel; ?>">

            <table style="width:100%; border-collapse:collapse; text-align:left;">
              <thead>
                <tr style="background:#f0f4fa; color:#0056b3; font-size:13px; text-transform:uppercase; letter-spacing:.3px;">
                  <th style="padding:12px 14px; border-radius:8px 0 0 0; width:130px;">N° Étudiant</th>
                  <th style="padding:12px 14px;">Étudiant</th>
                  <th style="padding:12px 14px; text-align:center; width:120px;">Note /20</th>
                  <th style="padding:12px 14px; border-radius:0 8px 0 0;">Commentaire</th>
                </tr>
              </thead>
              <tbody>
<?php
$sql_etu = "SELECT e.id_etudiant, e.nom, e.prenom, e.numero_etudiant
            FROM inscription i
            JOIN etudiant e ON i.id_etudiant = e.id_etudiant
            WHERE i.id_cours = ?
            ORDER BY e.nom ASC, e.prenom ASC";
$stmt_etu = mysqli_prepare($conn, $sql_etu);
mysqli_stmt_bind_param($stmt_etu, "i", $id_cours_choisi);
mysqli_stmt_execute($stmt_etu);
$res_etu = mysqli_stmt_get_result($stmt_etu);

// Récupérer TOUTES les notes existantes pour ce cours/type en une seule requête
$sql_notes_ex = "SELECT id_note, id_etudiant, note, commentaire
                 FROM note
                 WHERE id_cours = ? AND evaluation = ?
                 ORDER BY id_note ASC";
$stmt_nex = mysqli_prepare($conn, $sql_notes_ex);
mysqli_stmt_bind_param($stmt_nex, "is", $id_cours_choisi, $type_choisi);
mysqli_stmt_execute($stmt_nex);
$res_nex = mysqli_stmt_get_result($stmt_nex);

// Regrouper les notes par étudiant : $notes_par_etu[id_etudiant] = [ [id_note, note, commentaire], ... ]
$notes_par_etu = [];
while ($row_nex = mysqli_fetch_assoc($res_nex)) {
    $notes_par_etu[$row_nex['id_etudiant']][] = $row_nex;
}

$ligne = 0;
while ($etu = mysqli_fetch_assoc($res_etu)):
    $id_etu    = $etu['id_etudiant'];
    $initiales = strtoupper(mb_substr($etu['prenom'],0,1) . mb_substr($etu['nom'],0,1));
    $bg        = ($ligne % 2 === 0) ? '#ffffff' : '#fafbfd';
    $ligne++;

    $notes_existantes = $notes_par_etu[$id_etu] ?? [];
?>
<tr style="background:<?php echo $bg; ?>; border-bottom:2px solid #e8ecf0;">
  <td style="padding:12px 14px; color:#888; font-size:13px; vertical-align:top;">
    <?php echo htmlspecialchars($etu['numero_etudiant']); ?>
  </td>
  <td style="padding:12px 14px; vertical-align:top;">
    <div style="display:flex; align-items:center; gap:10px;">
      <span style="width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#0056b3,#2196f3); color:white; display:inline-flex; align-items:center; justify-content:center; font-size:13px; font-weight:bold; flex-shrink:0;">
        <?php echo $initiales; ?>
      </span>
      <strong style="color:#222; font-size:14px;"><?php echo htmlspecialchars($etu['prenom'].' '.$etu['nom']); ?></strong>
    </div>
  </td>

  <td colspan="2" style="padding:10px 14px; vertical-align:top;">

    <!-- ── Notes existantes (modifiables) ── -->
    <?php foreach ($notes_existantes as $idx => $ne):
        $nf = (float)$ne['note'];
        $col = $nf >= 10 ? '#28a745' : '#cc0000';
        $num = $idx + 1;
    ?>
    <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px; background:#f8f9fa; border-radius:8px; padding:8px 12px; border-left:3px solid <?php echo $col; ?>;">
      <span style="font-size:12px; color:#888; white-space:nowrap; min-width:55px;">Note <?php echo $num; ?> :</span>
      <input type="hidden" name="ids_notes[<?php echo $id_etu; ?>_<?php echo $idx; ?>]" value="<?php echo $ne['id_note']; ?>">
      
      <input type="number"
        name="notes[<?php echo $id_etu; ?>_<?php echo $idx; ?>]"
        value="<?php echo htmlspecialchars($ne['note']); ?>"
        min="0" max="20" step="0.5"
        class="champ-note"
        <?php echo $est_verrouille ? 'readonly style="background:#e9ecef; cursor:not-allowed;' : 'style="'; ?> width:72px; padding:7px 5px; border:2px solid <?php echo $col; ?>; border-radius:7px; text-align:center; font-weight:bold; font-size:14px;">
      
      <input type="text"
        name="commentaires[<?php echo $id_etu; ?>_<?php echo $idx; ?>]"
        value="<?php echo htmlspecialchars($ne['commentaire'] ?? ''); ?>"
        placeholder="Appréciation…"
        <?php echo $est_verrouille ? 'readonly style="background:#e9ecef; cursor:not-allowed;' : 'style="'; ?> flex:1; padding:7px 10px; border:1px solid #ddd; border-radius:7px; font-size:13px;">
      
      <?php if (!$est_verrouille): ?>
      <a href="supprimer_note.php?id_note=<?php echo $ne['id_note']; ?>&id_prof=<?php echo $id_actuel; ?>&id_cours=<?php echo $id_cours_choisi; ?>&type_eval=<?php echo urlencode($type_choisi); ?>"
         onclick="return confirm('Supprimer cette note ?')"
         style="color:#cc0000; font-size:18px; text-decoration:none; font-weight:bold; padding:0 4px;" title="Supprimer">✕</a>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php if (!$est_verrouille): ?>
    <div style="display:flex; align-items:center; gap:8px; background:#f0f5ff; border-radius:8px; padding:8px 12px; border:1px dashed #0056b3;">
      <span style="font-size:12px; color:#0056b3; white-space:nowrap; min-width:55px;">+ Nouvelle :</span>
      <input type="number"
        name="notes[<?php echo $id_etu; ?>_new]"
        min="0" max="20" step="0.5"
        class="champ-note"
        placeholder="—"
        style="width:72px; padding:7px 5px; border:2px solid #c0d4f0; border-radius:7px; text-align:center; font-weight:bold; font-size:14px;">
      <input type="text"
        name="commentaires[<?php echo $id_etu; ?>_new]"
        placeholder="Appréciation…"
        style="flex:1; padding:7px 10px; border:1px solid #c0d4f0; border-radius:7px; font-size:13px;">
    </div>
    <?php endif; ?>

  </td>
</tr>
<?php endwhile; ?>
</tbody>
            </table>

            <!-- Bouton bas de tableau -->
            <!-- Bouton bas de tableau -->
            <div style="display:flex; justify-content:flex-end; align-items: center; gap: 15px; margin-top:16px; padding-top:14px; border-top:1px solid #f0f0f0;">
              
              <?php if ($est_verrouille): ?>
                <div style="color: #dc3545; font-weight: bold; font-size: 16px; background: #ffe6e6; padding: 10px 20px; border-radius: 8px;">
                  🔒 Cette évaluation est verrouillée. Aucune modification n'est possible.
                </div>
              <?php else: ?>
                <!-- Bouton Enregistrer existant -->
                <button type="submit" name="btn_action" value="enregistrer"
                  style="background:#28a745; color:white; padding:12px 28px; border:none; border-radius:8px; cursor:pointer; font-weight:bold; font-size:15px; display:flex; align-items:center; gap:8px; box-shadow:0 3px 10px rgba(40,167,69,0.3);">
                  💾 Enregistrer
                </button>

                <button type="submit" name="btn_action" value="verrouiller"
                  onclick="return confirm('⚠️ ATTENTION : Voulez-vous vraiment enregistrer ET verrouiller cette évaluation ? Toute modification ou ajout de note sera définitivement impossible après cela.');" 
                  style="background:#dc3545; color:white; padding:12px 28px; border:none; border-radius:8px; cursor:pointer; font-weight:bold; font-size:15px; display:flex; align-items:center; gap:8px; box-shadow:0 3px 10px rgba(220,53,69,0.3);">
                  🔒 Enregistrer et Verrouiller
                </button>
              <?php endif; ?>

            </div>
          </form>
        </div>

        <?php else: ?>
        <!-- Invite à choisir un type d'évaluation -->
        <div style="margin-top:20px; background:white; border-radius:12px; padding:40px 20px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
          <div style="font-size:44px; margin-bottom:12px;">📋</div>
          <strong style="font-size:17px; color:#333;">Choisissez un type d'évaluation</strong><br>
          <p style="color:#666; margin-top:8px; font-size:14px;">Sélectionnez le type d'évaluation dans le menu ci-dessus pour saisir ou consulter les notes.</p>
          <!-- Raccourcis rapides -->
          <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:10px; margin-top:20px;">
            <?php foreach ($TYPES_EVAL as $cle => $info):
                $url = "index.php?role_simule=Professeur&id={$id_actuel}&onglet=notes&id_cours_prof={$id_cours_choisi}&type_eval=".urlencode($cle);
                $hasDeja = isset($bilan_evals[$cle]);
            ?>
            <a href="<?php echo $url; ?>" style="
                text-decoration:none; padding:12px 20px; border-radius:10px;
                border:2px solid <?php echo $hasDeja ? '#28a745' : '#0056b3'; ?>;
                background:<?php echo $hasDeja ? '#e6f7ec' : '#e8f0fb'; ?>;
                color:<?php echo $hasDeja ? '#28a745' : '#0056b3'; ?>;
                font-weight:bold; font-size:14px; display:flex; flex-direction:column; align-items:center; gap:4px; min-width:130px;">
              <?php echo htmlspecialchars($info['label']); ?>
              <span style="font-size:11px; font-weight:normal; opacity:.8;">
                coef <?php echo $info['coef']; ?>
                <?php if ($hasDeja): ?> · ✅ <?php echo $bilan_evals[$cle]['nb_notes']; ?> notes<?php endif; ?>
              </span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

      <?php else: ?>
      <!-- Pas de cours sélectionné -->
      <div style="margin-top:30px; background:white; border-radius:12px; padding:60px 20px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <div style="font-size:50px; margin-bottom:15px;">📝</div>
        <strong style="font-size:18px; color:#333;">Sélectionnez un cours</strong><br>
        <p style="color:#666; margin-top:8px;">Choisissez l'un de vos cours dans le menu ci-dessus pour commencer à gérer les notes.</p>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>
<div class="presences-prof" style="display: none;">
    <div class="contenu1">
      <div id="vue_saisie_presences">
        <div class="haut1">
          <div class="gauche">
            <strong>Faire l'appel</strong>
            <br><span style="font-size: 14px; font-weight: normal; color: #555;">Cours : Algorithmique Avancée (INFO-301) - Date : Aujourd'hui</span>
          </div>
          <div class="droite">
            <div class="btn">
              <div id="Enregistrer_presences" style="background-color: #007bff; color: white; padding: 10px 15px; border-radius: 5px; cursor: pointer;">
                <img src="plus.png" height="15" alt="Save" style="filter: brightness(0) invert(1);"> Valider les présences
              </div>
            </div>
          </div>
        </div>

        <div class="grille-presence-prof" style="margin-top: 20px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
          <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
              <tr style="border-bottom: 2px solid #eee; color: #333;">
                <th style="padding: 12px 10px;">ID Étudiant</th>
                <th style="padding: 12px 10px;">Nom Complet</th>
                <th style="padding: 12px 10px; text-align: center;">Présent (Oui)</th>
                <th style="padding: 12px 10px; text-align: center;">Absent (Non)</th>
              </tr>
            </thead>
            <tbody>
              <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 12px 10px; color: #666;">E-2023-14</td>
                <td style="padding: 12px 10px;"><strong>Emma Martin</strong></td>
                <td style="padding: 12px 10px; text-align: center;">
                  <input type="radio" name="presence_E202314" value="oui" checked style="transform: scale(1.5); cursor: pointer;">
                </td>
                <td style="padding: 12px 10px; text-align: center;">
                  <input type="radio" name="presence_E202314" value="non" style="transform: scale(1.5); cursor: pointer;">
                </td>
              </tr>
              <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 12px 10px; color: #666;">E-2023-15</td>
                <td style="padding: 12px 10px;"><strong>Lucas Bernard</strong></td>
                <td style="padding: 12px 10px; text-align: center;">
                  <input type="radio" name="presence_E202315" value="oui" style="transform: scale(1.5); cursor: pointer;">
                </td>
                <td style="padding: 12px 10px; text-align: center;">
                  <input type="radio" name="presence_E202315" value="non" checked style="transform: scale(1.5); cursor: pointer;">
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="liste-etudiants-prof" style="display: none;">
  <div class="contenu1">
    
    <?php
    // On récupère l'ID du cours s'il a été sélectionné via le menu déroulant
    $id_cours_liste = isset($_GET['id_cours_liste']) ? intval($_GET['id_cours_liste']) : 0;
    ?>

    <div class="haut1">
      <div class="gauche">
        <strong>Mes Étudiants Inscrits</strong>
        <br>
        <select onchange="window.location.href='index.php?role_simule=Professeur&id=<?php echo $id_actuel; ?>&onglet=liste_etu&id_cours_liste='+this.value;" style="margin-top: 5px; padding: 5px; border-radius: 5px; border: 1px solid #ccc; font-weight: bold; color: #333;">
          <option value="">-- Sélectionnez un de vos cours --</option>
          <?php
          if ($role_actuel == 'Professeur' && $id_actuel) {
              // Requête pour lister les cours assignés à CE professeur
              $req_mes_cours = mysqli_prepare($conn, "SELECT id_cours, nom_matiere, code_cours FROM cours WHERE id_enseignant = ?");
              mysqli_stmt_bind_param($req_mes_cours, "i", $id_actuel);
              mysqli_stmt_execute($req_mes_cours);
              $res_mes_cours = mysqli_stmt_get_result($req_mes_cours);
              
              while ($mc = mysqli_fetch_assoc($res_mes_cours)) {
                  $selected = ($mc['id_cours'] == $id_cours_liste) ? 'selected' : '';
                  echo '<option value="'.$mc['id_cours'].'" '.$selected.'>'.htmlspecialchars($mc['nom_matiere'].' ('.$mc['code_cours'].')').'</option>';
              }
          }
          ?>
        </select>
      </div>
    </div>

    <div class="grille-liste-etu" style="margin-top: 20px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
      <?php if ($id_cours_liste > 0): ?>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
          <thead>
            <tr style="border-bottom: 2px solid #eee; background: #0056b3; color: white;">
              <th style="padding: 12px; border-radius: 5px 0 0 0;">N° Étudiant</th>
              <th style="padding: 12px;">Nom</th>
              <th style="padding: 12px;">Prénom</th>
              <th style="padding: 12px; border-radius: 0 5px 0 0;">Email</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // La requête magique : On joint INSCRIPTION et ETUDIANT en filtrant par le cours
            $sql_inscrits = "SELECT e.numero_etudiant, e.nom, e.prenom, e.email_etu 
                             FROM inscription i
                             JOIN etudiant e ON i.id_etudiant = e.id_etudiant
                             WHERE i.id_cours = ?
                             ORDER BY e.nom ASC, e.prenom ASC";
                             
            $stmt_inscrits = mysqli_prepare($conn, $sql_inscrits);
            
            if ($stmt_inscrits) {
                mysqli_stmt_bind_param($stmt_inscrits, "i", $id_cours_liste);
                mysqli_stmt_execute($stmt_inscrits);
                $res_inscrits = mysqli_stmt_get_result($stmt_inscrits);

                if (mysqli_num_rows($res_inscrits) > 0) {
                    // On boucle pour afficher chaque étudiant trouvé
                    while ($etu = mysqli_fetch_assoc($res_inscrits)) {
                        echo '<tr style="border-bottom: 1px solid #eee;">';
                        echo '  <td style="padding: 12px; color: #666;">' . htmlspecialchars($etu['numero_etudiant']) . '</td>';
                        echo '  <td style="padding: 12px; font-weight: bold;">' . htmlspecialchars($etu['nom']) . '</td>';
                        echo '  <td style="padding: 12px;">' . htmlspecialchars($etu['prenom']) . '</td>';
                        echo '  <td style="padding: 12px; color: #0056b3;">' . htmlspecialchars($etu['email_etu'] ?? 'Non renseigné') . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo "<tr><td colspan='4' style='padding: 20px; text-align: center; color: #666;'>Aucun étudiant n'est inscrit à ce cours pour le moment.</td></tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='color:red;'>Erreur SQL : " . mysqli_error($conn) . "</td></tr>";
            }
            ?>
          </tbody>
        </table>
      <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #666; font-style: italic;">
          👆 Veuillez sélectionner un de vos cours dans le menu déroulant ci-dessus pour afficher la liste de vos étudiants.
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php if (isset($_GET['onglet']) && $_GET['onglet'] == 'notes') { ?>
  <script>
    window.addEventListener('load', function() {
        setTimeout(function() {
            document.querySelectorAll('.page, .emploi, .notation, .messagerie, .presences, .parametres, .enseignants, .cours-admin, .inscriptions-admin, .notation_prof, .presences-prof, .vue-tb-admin, .vue-tb-prof, .vue-tb-etudiant, .page-cours-etudiant').forEach(el => { if(el) el.style.display = 'none'; });
            let np = document.querySelector('.notation_prof');
            if(np) np.style.display = 'block';
        }, 150); 
    });
  </script>
<?php } ?>

<?php if (isset($_GET['onglet']) && $_GET['onglet'] == 'liste_etu') { ?>
  <script>
    window.addEventListener('load', function() {
        setTimeout(function() {
            document.querySelectorAll('.page, .emploi, .notation, .messagerie, .presences, .parametres, .enseignants, .cours-admin, .inscriptions-admin, .notation_prof, .presences-prof, .vue-tb-admin, .vue-tb-prof, .vue-tb-etudiant, .page-cours-etudiant').forEach(el => { if(el) el.style.display = 'none'; });
            let listeEtu = document.querySelector('.liste-etudiants-prof');
            if(listeEtu) listeEtu.style.display = 'block';
        }, 150); 
    });
  </script>
<?php } ?>
  <div id="modal_edt_prof" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999; justify-content:center; align-items:center;">
  <div style="background:white; border-radius:12px; padding:30px; width:700px; max-width:90%; max-height:80vh; overflow-y:auto; position:relative;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:2px solid #eee; padding-bottom:15px;">
      <h3 id="titre_modal_edt" style="margin:0; color:#0056b3;"></h3>
      <button onclick="document.getElementById('modal_edt_prof').style.display='none';" style="background:#CDCDCD; border:none; padding:8px 15px; border-radius:5px; cursor:pointer; font-weight:bold; font-size:16px;">✕</button>
    </div>
    <div id="contenu_modal_edt" style="display:flex; flex-direction:column; gap:10px;">
      <p style="color:#999; text-align:center;">Chargement...</p>
    </div>
  </div>
</div>
</div> <!-- fin .milieu -->
<footer class="app-footer">Mentions/liens</footer>
</body>
</html>
