import { showAndHideElementsForRoles, signout, isConnected } from '../script.js';
import { getUserInfo, updateUserInfo , deleteUserAccount } from '../api.js';

//INITIALISATION DE LA PAGE PROFIL
export default async function initProfil() {
    console.log("Initialisation page profil");

    if (!isConnected()) {
        alert("Vous devez être connecté pour accéder à cette page.");
        window.location.href = "/login";
        return;
    }

    try {
        const user = await getUserInfo();
       loadProfile(user);
        
        const vehicules = await getVehicules(user.id);
        if (vehicules && vehicules.length > 0) {
            currentVehicule = vehicules[0]; // Stockage du premier véhicule
            displayVehicule(currentVehicule);
        } else {
            displayVehicule(null);
        }

        initProfileListeners(user);
        initButtonsVehicule();
        initFormVehicule();
        initform();
        showAndHideElementsForRoles();
        
        console.log("Page profil initialisée avec succès");
    } catch (error) {
        console.error("Erreur lors de l'initialisation du profil :", error);
    }
}
// Fonction pour charger les informations du profil
async function loadProfile(user) {
    try {
        const user = await getUserInfo();
        displayUserData(user);
        
    } catch (error) {
        console.error("Erreur lors du chargement des informations utilisateur :", error);
    }
}
// affichage des données utilisateur dans le profil
function displayUserData(user) {
    const userHeader = document.getElementById('user-header');
    if (userHeader) {
        userHeader.innerHTML = `
            <div class="col-md-6"> 
                <h2>Bonjour, ${user.pseudo || ''}</h2>
                <p>Bienvenue dans votre espace personnel EcoRide</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="credit-badge" id="creditBadge">
                    <i class="fas fa-coins me-2"></i> ${user.credits || 0} crédits disponibles
                </div>
            </div>
        `;
    }

    const profileInfo = document.getElementById('profileInfo');
    if (profileInfo) {
        profileInfo.innerHTML = `  
            <p><strong>Nom :</strong> ${user.nom || ''}</p>
            <p><strong>Prénom :</strong> ${user.prenom || ''}</p>
            <p><strong>Numéro de téléphone :</strong> ${user.telephone || ''}</p>
            <p><strong>Date de naissance :</strong> ${user.dateNaissance || ''}</p>
            <p><strong>Email :</strong> ${user.email || ''}</p>
           `;
    }

    const statusUser = document.getElementById('statusUser');
    if (statusUser) {
        statusUser.innerHTML = `
            <div class="mb-3">
                <div class="mb-3">
    ${user.isConducteur ? `<span class="role-badge badge-driver"><i class="fas fa-car me-1"></i> Conducteur</span>` : ''}
    
    ${user.isPassager ? `<span class="role-badge badge-passenger"><i class="fas fa-user me-1"></i> Passager</span>` : ''}
</div>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="editIsPassager" name="isPassager">
                <label class="form-check-label" for="editIsPassager">Passager</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="editIsConducteur" name="isConducteur">
                 <label class="form-check-label" for="editIsConducteur">Conducteur</label>
            </div>
        `;
    }
    const passagerCheck = document.getElementById('editIsPassagerMain');
    const conducteurCheck = document.getElementById('editIsConducteurMain');

    if (passagerCheck) {
        passagerCheck.checked = user.isPassager || false;
    }
    if (conducteurCheck) {
        conducteurCheck.checked = user.isConducteur || false;
    }

}
// Initialisation des écouteurs d'événements pour les boutons et les formulaires
function initProfileListeners(user) {
    // Ouvrir le modal pour la modification du mot de passe
    const changePasswordBtn = document.getElementById('changePasswordBtn');
    if (changePasswordBtn) {
        changePasswordBtn.addEventListener('click', () => {
            const modalEl = document.getElementById('changePasswordModal');
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    }
   
   const editProfileBtn = document.getElementById('editProfileBtn');
if (editProfileBtn) {
    // 1. Ajout de async ici
    editProfileBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        
        const modalEl = document.getElementById('editProfileModal');
        if (!modalEl) {
            console.error("Modal pour la modification du profil non trouvé !");
            return;
        }
        try {
          
            const user = await getUserInfo();
            fillEditProfileModal(user);
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.show();
        } catch (error) {
            console.error("Erreur lors de la récupération des données utilisateur :", error);
        }
    });
}
    // Bouton de suppression de compte
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', () => deleteAccount());
    }
    //checkbox pour le rôle de passager
    const editIsPassagerCheckbox = document.getElementById('editIsPassager');
    if (editIsPassagerCheckbox) {
        editIsPassagerCheckbox.checked = user.isPassager || false;
        editIsPassagerCheckbox.addEventListener('change', async () => {
            const updatedData = { isPassager: editIsPassagerCheckbox.checked };
            try {
                await updateUserInfo(updatedData);
                alert("Rôle de passager mis à jour avec succès !");
            } catch (error) {
                console.error("Erreur lors de la mise à jour du rôle de passager :", error);
                alert("Une erreur est survenue lors de la mise à jour du rôle de passager.");
            }
        });
    }
    //checkbox pour le rôle de conducteur
    const editIsConducteurCheckbox = document.getElementById('editIsConducteur');
    if (editIsConducteurCheckbox) {
        editIsConducteurCheckbox.checked = user.isConducteur || false;
        editIsConducteurCheckbox.addEventListener('change', async () => {
            const updatedData = { isConducteur: editIsConducteurCheckbox.checked };
            try {
                await updateUserInfo(updatedData);
                alert("Rôle de conducteur mis à jour avec succès !");
            } catch (error) {
                console.error("Erreur lors de la mise à jour du rôle de conducteur :", error);
                alert("Une erreur est survenue lors de la mise à jour du rôle de conducteur.");
            }
        });
    }
}
// Remplir le modal de modification du profil avec les données actuelles de l'utilisateur
function fillEditProfileModal(user) {
    
    const form = document.getElementById('editProfileForm');
    if (!form) return;
    console.log("Données reçues dans le modal :", user);
    form.elements['editPseudo'].value = user.pseudo || '';
    form.elements['editNom'].value = user.nom || '';
    form.elements['editPrenom'].value = user.prenom || '';
    form.elements['editDateDeNaissance'].value = user.dateNaissance || '';
    form.elements['editNumeroTelephone'].value = user.telephone || '';
    isPassager: editProfileForm.elements['isPassager'] ? editProfileForm.elements['isPassager'].checked : false;
                isConducteur: editProfileForm.elements['isConducteur'] ? editProfileForm.elements['isConducteur'].checked : false;
};
// Initialisation du formulaire de modification du profil
function initform() {
    // --- Mise à jour du profil ---
    const editProfileForm = document.getElementById('editProfileForm');
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(editProfileForm);
          const telephoneRaw = formData.get('numeroTelephone');

            const updatedData = {
                pseudo: formData.get('pseudo'),
                nom: formData.get('nom'),
                prenom: formData.get('prenom'),
                dateNaissance: formData.get('dateDeNaissance'),
                telephone: telephoneRaw ? telephoneRaw.replace(/\s+/g, '') : null ,
                
            };
            try {
                await updateUserInfo(updatedData);
                const user = await getUserInfo(); 
                displayUserData(user);
                
                const modalEl = document.getElementById('editProfileModal');
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                alert("Informations mises à jour avec succès !");
            } catch (error) {
                console.error("Erreur lors de la mise à jour des informations :", error);
                alert("Une erreur est survenue lors de la mise à jour des informations.");
            }
        });
    }

    // --- Mise à jour du mot de passe ---
    const passwordForm = document.getElementById('PasswordUpdateForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            
            const formData = new FormData(passwordForm);
            const currentPassword = formData.get('currentPassword');
            const newPassword = formData.get('newPassword');
            const confirmNewPassword = formData.get('confirmNewPassword');

            if (!currentPassword || !newPassword || !confirmNewPassword) {
                alert("Veuillez remplir tous les champs du formulaire.");
                return;
            }

            if (newPassword !== confirmNewPassword) {
                alert("Les nouveaux mots de passe ne correspondent pas.");
                return;
            }

            const updatedData = { currentPassword, newPassword, confirmNewPassword };

            try {
                await updateUserInfo(updatedData); 
                alert("Mot de passe mis à jour avec succès !");
                
                const modalEl = document.getElementById('changePasswordModal');
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                passwordForm.reset();
            } catch (error) {
                console.error("Erreur lors de la mise à jour du mot de passe :", error);
                alert("Une erreur est survenue lors de la mise à jour du mot de passe.");
            }
        });
    }
}
// Fonction pour supprimer le compte utilisateur
async function deleteAccount() {
    if (!confirm("Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.")) {
        return;
    }
    try {
        await deleteUserAccount();
        
        alert("Votre compte a été supprimé. Vous allez être redirigé vers la page d'accueil.");
        signout();
    } catch (error) {
        console.error("Erreur lors de la suppression du compte :", error);
        alert("Impossible de supprimer le compte.");
    }
}
