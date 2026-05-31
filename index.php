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

