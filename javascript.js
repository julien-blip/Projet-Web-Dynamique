
    document.addEventListener("DOMContentLoaded", function () {
      const btnAjouter = document.getElementById('Ajouter_etudiant');
      const btnRetour = document.getElementById('Retour_dashboard');
      
      const vueDashboard = document.getElementById('vue_dashboard');
      const vueFormulaire = document.getElementById('formulaire_ajout');
      
      // Boutons Navigation
      const page_btn = document.getElementById('btn_maison'); 
      const page_btn_admin = document.getElementById('btn_etudiant_admin');
      const emploi_btn = document.getElementById('btn_emploi'); 
      const notes_btn = document.getElementById('btn_notes'); 
      const msg_btn = document.getElementById('btn_messages');
      const presences_btn = document.getElementById('btn_presences');
	  const parametre_btn = document.getElementById('btn_parametre');
      
      // Vues
      const page_page = document.querySelector('.page');
      const emploi_page = document.querySelector('.emploi'); 
      const notes_page = document.querySelector('.notation'); 
      const msg_page = document.querySelector('.messagerie'); 
      const presences_page = document.querySelector('.presences');
	  const parametre_page = document.querySelector('.parametres');
      
      const vueNavAdmin = document.getElementById('navigation_admin');
      const vueNavEtudiant = document.getElementById('navigation_etudiant');
	  const vueNavProf = document.getElementById('navigation_prof');
	  
	  const btnEnseignantsAdmin = document.getElementById('btn_enseignants_admin');
	  // --- Déclaration pour la vue Professeur ---
const btnNotesProf = document.getElementById('btn_notes_prof'); // À lier au bouton du menu prof
const notationProfPage = document.querySelector('.notation-prof');



// --- Événement pour afficher la saisie des notes ---
if(btnNotesProf) {
    btnNotesProf.addEventListener('click', function() {
        cacherVues();
        notationProfPage.style.display = 'flex'; 
    });
} // --- Déclaration pour les présences Professeur ---
const btnPresencesProf = document.getElementById('btn_presences_prof'); // À lier au menu
const presencesProfPage = document.querySelector('.presences-prof');




// --- Événement pour afficher la saisie des présences ---
if(btnPresencesProf) {
    btnPresencesProf.addEventListener('click', function() {
        cacherVues();
        presencesProfPage.style.display = 'flex'; 
    });
} 

const enseignantsPage = document.querySelector('.enseignants');

const btnCoursAdmin = document.getElementById('btn_cours_admin');
const coursAdminPage = document.querySelector('.cours-admin');

const btnAjouterCours = document.getElementById('Ajouter_cours');
const btnRetourCours = document.getElementById('Retour_dashboard_cours');
const vueDashboardCours = document.getElementById('vue_dashboard_cours');
const vueFormulaireCours = document.getElementById('formulaire_ajout_cours');
      
      // Fonction pour cacher toutes les vues centrales
      function cacherVues() {
    if(page_page) page_page.style.display = 'none';
    if(emploi_page) emploi_page.style.display = 'none';
    if(notes_page) notes_page.style.display = 'none';
    if(msg_page) msg_page.style.display = 'none';
    if(presences_page) presences_page.style.display = 'none'; 
    if(parametre_page) parametre_page.style.display = 'none';
    if(enseignantsPage) enseignantsPage.style.display = 'none'; // <-- Nouvelle ligne
	if(notationProfPage) notationProfPage.style.display = 'none';
	if(presencesProfPage) presencesProfPage.style.display = 'none';
}


if(btnCoursAdmin) {
    btnCoursAdmin.addEventListener('click', function() {
        cacherVues();
        coursAdminPage.style.display = 'flex'; 
    });
}

// --- Événements Formulaire Cours ---
if(btnAjouterCours) {
    btnAjouterCours.addEventListener('click', function() {
        vueDashboardCours.style.display = 'none';
        vueFormulaireCours.style.display = 'block';
    });
}
if(btnRetourCours) {
    btnRetourCours.addEventListener('click', function() {
        vueFormulaireCours.style.display = 'none';
        vueDashboardCours.style.display = 'block';
    });
}

      // Bascule de pages
      if(page_btn) {
          page_btn.addEventListener('click', function() {
              cacherVues();
              page_page.style.display = 'flex';
          });
      }
      if(page_btn_admin) {
          page_btn_admin.addEventListener('click', function() {
              cacherVues();
              page_page.style.display = 'flex';
          });
      }
      if(emploi_btn) {
          emploi_btn.addEventListener('click', function() {
              cacherVues();
              emploi_page.style.display = 'flex'; 
          });
      }
	  if(btnEnseignantsAdmin) {
    btnEnseignantsAdmin.addEventListener('click', function() {
        cacherVues();
        enseignantsPage.style.display = 'flex'; 
    });
}
      if(notes_btn) {
          notes_btn.addEventListener('click', function() {
              cacherVues();
              notes_page.style.display = 'flex'; 
          });
      }
      if(msg_btn) {
          msg_btn.addEventListener('click', function() {
              cacherVues();
              msg_page.style.display = 'flex'; 
          });
      }
      if(presences_btn) { 
          presences_btn.addEventListener('click', function() {
              cacherVues();
              presences_page.style.display = 'flex'; 
          });
      }
	  if(parametre_btn) { 
          parametre_btn.addEventListener('click', function() {
              cacherVues();
              parametre_page.style.display = 'flex'; 
          });
      }

      // Actions formulaires
      if(btnAjouter) {
          btnAjouter.addEventListener('click', function() {
            vueDashboard.style.display = 'none';
            vueFormulaire.style.display = 'block';
          });
      }
      if(btnRetour) {
          btnRetour.addEventListener('click', function() {
            vueFormulaire.style.display = 'none';
            vueDashboard.style.display = 'block';
          });
      }

      // --- LOGIQUE DU MENU DES RÔLES ---
      const boutonsFleche = document.querySelectorAll('img[name="fleche"]');
      
      boutonsFleche.forEach(fleche => {
        fleche.addEventListener('click', function(e) {
          e.stopPropagation(); 
          
          const menuParent = this.closest('.navigateur');
          if (menuParent) {
             const leMenu = menuParent.querySelector('.menu_role');
             
             document.querySelectorAll('.menu_role').forEach(m => {
                if(m !== leMenu) m.style.display = 'none';
             });

             if(leMenu) {
                 leMenu.style.display = (leMenu.style.display === 'block') ? 'none' : 'block';
             }
          }
        });
      });

      document.addEventListener('click', function() {
        document.querySelectorAll('.menu_role').forEach(m => m.style.display = 'none');
      });

      // Actions lors du clic sur un rôle
      const optionsRole = document.querySelectorAll('.option_role');
      
      optionsRole.forEach(function(option) {
        option.addEventListener('click', function() {
          const roleChoisi = this.innerText.trim(); 
          
          document.querySelectorAll('.menu_role').forEach(m => m.style.display = 'none');
          
          // Réinitialiser la vue au changement de rôle
          cacherVues();
          if(page_page) page_page.style.display = 'flex';

          if(roleChoisi === 'Etudiant') {
            vueNavAdmin.style.display = 'none';
            vueNavEtudiant.style.display = 'flex';
			vueNavProf.style.display = 'none';
          } 
          else if(roleChoisi === 'Administrateur') {
            vueNavAdmin.style.display = 'flex';
            vueNavEtudiant.style.display = 'none';
			vueNavProf.style.display = 'none';
          }
		  else if(roleChoisi === 'Professeur') {
            vueNavAdmin.style.display = 'none';
            vueNavEtudiant.style.display = 'none';
			vueNavProf.style.display = 'flex';
          }
        });
      });

      // --- LOGIQUE DE RECHERCHE ---
      const barreRecherche = document.querySelector('.recherche input');
      
      if (barreRecherche) {
        barreRecherche.addEventListener('input', function(e) {
          const texteRecherche = e.target.value.toLowerCase();
          const profils = document.querySelectorAll('.profil');
          
          profils.forEach(profil => {
            const nomEtudiant = profil.querySelector('.caracteristiques strong').innerText.toLowerCase();
            
            if(nomEtudiant.includes(texteRecherche)) {
              profil.style.display = 'flex';
            } else {
              profil.style.display = 'none';
            }
          });
        });
      }
 });
