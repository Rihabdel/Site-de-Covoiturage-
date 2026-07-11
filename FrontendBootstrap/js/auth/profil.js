import { showAndHideElementsForRoles, signout, isConnected } from '../script.js';
import { getUserInfo, updateUserInfo , deleteUserAccount ,addVehicule ,getVehicules , updateVehicule, deleteVehicule , getVehiculeById} from '../api.js';

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
            
       const vehiculeInfo = document.getElementById('vehiculeInfo');
       if (vehiculeInfo) {

            vehiculeInfo.innerHTML = `<p>Chargement des véhicules...</p>`;
            await loadVehicule();
        }
     

        initProfileListeners(user);
        initVehiculeListener();
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


// --- Section Véhicule ---
async function loadVehicule()   {
    try {
        const user = await getUserInfo();
        const vehicules = await getVehicules();
        displayVehicules(vehicules);
    }
    catch (error) {
        console.error("Erreur lors du chargement des véhicules :", error);
    }
}
async function displayVehicules(vehicules) {
    const vehiculeInfo = document.getElementById("vehiculeInfo");

    if (!vehiculeInfo) return;

    if (!vehicules || vehicules.length === 0) {
        vehiculeInfo.innerHTML = `<p>Aucun véhicule enregistré.</p>`;
        return;
    }

    vehiculeInfo    .innerHTML = vehicules.map(v => `
        <div class="vehicule-card col mb-4 p-2" data-id="${v.id}">
            <div class="card-body ">
                <h5 class="card-header">
                    ${v.marque || "Marque inconnue"} ${v.modele || ""}
                </h5>

                <p class="card-text">
                    <strong>Couleur :</strong> ${v.couleur || "Non renseignée"}<br>
                    <strong>Immatriculation :</strong> ${v.numeroImmatriculation || "Non renseignée"}<br>
                    <strong>Énergie :</strong> ${v.energie || "Non renseignée"}<br>
                    <strong>Date de mise en circulation :</strong> ${v.dateImmatriculation || "Non renseignée"}
                </p>
            </div>
            <div class="card-footer bg-transparent">
                                        <button class="btn btn-outline-primary btn-sm" id="editVehiculeBtn" data-id="${v.id}">
                                            <i class="fas fa-edit"></i> Modifier
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" id="deleteVehiculeBtn" data-id="${v.id}">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
             </div>
        </div>
    `).join("");
}

// Remplir le formulaire d'édition du véhicule avec les données actuelles
function fillVehiculeForm(vehicule) {
    const form = document.getElementById('editVehiculeForm');
    if (!form || !vehicule) return;

    form.elements['editVehiculeMarque'].value = vehicule.marque || '';
    form.elements['editVehiculeModele'].value = vehicule.modele || '';
    form.elements['editVehiculeEnergie'].value = vehicule.energie || '';
    form.elements['editVehiculeCouleur'].value = vehicule.couleur || '';
    form.elements['editVehiculeDateImmatriculation'].value = vehicule.dateImmatriculation || '';
    form.elements['editVehiculeNumeroImmatriculation'].value = vehicule.numeroImmatriculation || '';
}
// Initialisation des écouteurs d'événements pour les véhicules
function initVehiculeListener() {

    const addVehiculeBtn = document.getElementById('addVehiculeBtn');
    if (addVehiculeBtn) {
        addVehiculeBtn.addEventListener('click', () => {
            const modalEl = document.getElementById('addVehiculeModal');
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    }
    const vehiculeInfo = document.getElementById('vehiculeInfo');
    if (vehiculeInfo) {
        vehiculeInfo.addEventListener('click', async (event) => {
            const editBtn = event.target.closest('#editVehiculeBtn');
            if (editBtn) {
                const vehiculeId = editBtn.dataset.id;
                const vehicule = await getVehiculeById(vehiculeId);
                fillVehiculeForm(vehicule);
                        console.log("Données du véhicule récupérées :", vehicule);
                console.log("ID du véhicule à modifier :", vehiculeId);
                const editVehiculeForm = document.getElementById('editVehiculeForm');
                if (editVehiculeForm) {
                    editVehiculeForm.dataset.id = vehiculeId;
                    console.log("ID du véhicule stocké dans le formulaire :", editVehiculeForm.dataset.id);
                }
                
                        
                   
                
                const modalEl = document.getElementById('editVehiculeModal');
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
            const deleteBtn = event.target.closest('#deleteVehiculeBtn');
            if (deleteBtn) {
                const vehiculeId = deleteBtn.dataset.id;
                const modalEl = document.getElementById('deleteVehiculeModal');
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
                const confirmDeleteBtn = document.getElementById('confirmDeleteVehiculeBtn');
                if (confirmDeleteBtn) {
                    confirmDeleteBtn.onclick = async () => {
                        try {
                            await deleteVehicule(vehiculeId);
                            const user = await getUserInfo();
                            const vehicules = await getVehicules(user.id);
                            displayVehicules(vehicules);
                            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                            alert("Véhicule supprimé avec succès.");
                        } catch (error) {
                            console.error("Erreur lors de la suppression du véhicule :", error);
                            alert("Une erreur est survenue lors de la suppression du véhicule.");
                        }
                    };
                }
            
            }
        });
    }
   


}

function initFormVehicule() {
    const addVehiculeForm = document.getElementById('addVehiculeForm');
    if (addVehiculeForm) {
        addVehiculeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(addVehiculeForm);
            const vehiculeData = Object.fromEntries(formData);
            try {
                await addVehicule(vehiculeData);
                const user = await getUserInfo();
                const vehicules = await getVehicules(user.id);
                displayVehicules(vehicules);

                const modalEl = document.getElementById('addVehiculeModal');
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                addVehiculeForm.reset();
                alert("Véhicule ajouté avec succès !");
            } catch (error) {
                console.error("Erreur lors de l'ajout du véhicule :", error);
                alert("Une erreur est survenue lors de l'ajout du véhicule.");
            }
        });
    }

    const editVehiculeForm = document.getElementById('editVehiculeForm');
    if (!editVehiculeForm) return;
    editVehiculeForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(editVehiculeForm);
        const vehiculeData = {
                marque: formData.get('marque'),
                modele: formData.get('modele'),
                energie: formData.get('energie'),
                couleur: formData.get('couleur'),
                dateImmatriculation: formData.get('dateImmatriculation'),
                numeroImmatriculation: formData.get('numeroImmatriculation')
            };
            const vehiculeId = editVehiculeForm.dataset.id;
            if (!vehiculeId) {
                console.error("ID du véhicule manquant pour la mise à jour.");
                alert("Impossible de mettre à jour le véhicule : ID manquant.");
                return;
            }

            try {
                await updateVehicule(vehiculeId, vehiculeData);
                const user = await getUserInfo();
                const vehicules = await getVehicules(user.id);
                displayVehicules(vehicules);
                const modalEl = document.getElementById('editVehiculeModal');
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                alert("Véhicule mis à jour avec succès !");
                ;
            } catch (error) {
                console.error("Erreur lors de la mise à jour du véhicule :", error);
                alert("Une erreur est survenue lors de la mise à jour du véhicule.");
            }
        });
    }


