import { isConnected, getUserInfo } from './script.js';
import { getMyTrips, cancelTrip , updateTripStatus, getTripById , addTrip, getVehicules, updateUserInfo} from './api.js';
import  { searchOpenStreetMap } from './openstreetmap.js';
import { showSuggestions} from './recherche.js';
export default async function initProfil() {
    console.log("Initialisation page trajets");

    if (!isConnected()) {
        alert("Vous devez être connecté pour accéder à cette page.");
        window.location.href = "/login";
        return;
    }
    try {
        const userInfo = await getUserInfo();
        console.log("User info:", userInfo);
        loadPlannedTrips();
        loadInProgressTrips();
        loadCompletedTrips();
        
    
    }
    catch (error) {
        console.error("Erreur lors de la récupération des informations de l'utilisateur:", error);
    }
        initPlannedTripsButton();
        initInProgressTripsButtons();
        initCompletedTripsButtons();
        initCreateTripButtons();
}
async function loadPlannedTrips() {
    try {
        await displayPlannedTrips();
    } catch (error) {
        console.error("Erreur lors de la récupération des informations de l'utilisateur:", error);
    }
}
export async function displayPlannedTrips() {

    const trips = await getMyTrips();
    console.log("Trajets prévus :", trips);

    const plannedTrip = document.getElementById('planned-trip');
    if (!plannedTrip) {
        console.error("L'élément avec l'ID 'planned-trip' n'a pas été trouvé.");
        return;
    }
    
    const visiblePlannedTrips = trips.filter(trip => trip.statut === 'planned' && trip.dateDepart >= new Date().toISOString() && trip.placeDisponible > 0);
    if (visiblePlannedTrips.length === 0) {
    plannedTrip.innerHTML = `
        <div class="alert alert-info text-center">
            Aucun trajet planifié.
        </div>
    `;
    return;
    }
    plannedTrip.innerHTML = visiblePlannedTrips.map(trip => {
            const date = new Date(trip.dateDepart);
            const dateFormatee = date.toLocaleDateString('fr-FR');
        const heureDepart = new Date(trip.dateDepart).toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        });
        const heureArrivee = new Date(trip.dateArrivee).toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        });

        return `
        <div class="row align-items-center planned-trip mb-3">
            
            <div class="col-md-8 ">
                <h5 class="card-title">${trip.adresseDepart} → ${trip.adresseArrivee}</h5>
                    <p class="card-text">${dateFormatee} à ${heureDepart}</p>
                
                <div class="mb-1">
                    <i class="fas fa-road me-2"></i>
                    <strong>Distance :</strong> ${trip.distance ?? 'pas disponible'} km
                </div>
                <div class="mb-1">
                    <i class="fas fa-users me-2"></i>
                    <strong>Nombre de place disponibles :</strong> ${trip.placeDisponible ?? 'pas disponible'} places
                </div>
                <div class="mb-1">
                    <i class="fas fa-euro-sign me-2"></i>
                    <strong>Prix par personne :</strong> ${trip.prix ?? 'pas disponible'} €
                </div>
                <div class="mb-1">
                    <i class="far fa-calendar me-2"></i>
                    <strong>Horaire :</strong> ${heureDepart} → ${heureArrivee}
                </div>
                <div class="mb-1">
                </div>
                <div class="mb-1">
                    <i class="fas fa-leaf me-2"></i>
                    <strong>Voyage écologique :</strong> ${trip.voyageEcologique ? 'Oui' : 'Non'}
                </div>
                <div class="mb-1">
                <i class="fas fa-user me-2"></i>
                    <strong>Passagers :</strong> ${trip.passagers.length > 0 ? trip.passagers.map(passager => passager.pseudo).join(', ') : ' Aucun passager'   }
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <p><span class="trip-status status-planned" id="trip-status">${trip.statut}</span></p>
                <p class="card-text"><strong><i class="fas fa-user me-2"></i> Conducteur :</strong> ${trip.chauffeur.pseudo} </p>
                    <div class="mt-2">
                        <button class="btn btn-primary btn-sm start-trip" data-id="${trip.id}">
                            Démarrer
                        </button>
                        <button class="btn btn-danger cancel-trip btn-sm" data-id="${trip.id}">
                            Annuler
                        </button>
                </div>
            </div>
            
        </div>
    `}).join('');
}
function initPlannedTripsButton() {
    
    const plannedTrip = document.getElementById('planned-trip');
    if (!plannedTrip) {
        console.error("L'élément avec l'ID 'planned-trip' n'a pas été trouvé.");
        return;
    }
    
    plannedTrip.addEventListener('click', async (event) => {
        if (event.target.classList.contains('cancel-trip')) {
            const tripId = event.target.getAttribute('data-id');
            try {
                await cancelTrip(tripId);
                alert("Trajet annulé avec succès.");
                const userInfo = await getUserInfo();
                displayPlannedTrips(userInfo.trajetsProposes);
            }
            catch (error) {
                console.error("Erreur lors de l'annulation du trajet:", error);
                alert("Une erreur est survenue lors de l'annulation du trajet.");
            }
        }
        if (event.target.classList.contains('start-trip')) {
            const tripId = event.target.getAttribute('data-id');
            const button = event.target;
             try {
                console.log("Démarrage du trajet avec l'ID :", tripId);
            const result = await updateTripStatus(tripId, {statut: "in_progress"});

            alert(result.message);

            // changer le texte du bouton
            button.textContent = "En cours";
            button.disabled = true;

        } catch (e) {
            alert(e.message);
        }
        }
    });
}
// onglet "En cours" pour afficher les trajets en cours
async function loadInProgressTrips() {
    try {
        await displayInProgressTrips();
    } catch (error) {
        console.error("Erreur lors de la récupération des informations de l'utilisateur:", error);
    }
}

export async function displayInProgressTrips() {
    const trips = await getMyTrips();
    console.log("Trajets en cours :", trips);
    const inProgressTrip = document.getElementById('inProgress-trip');
    if (!inProgressTrip) {
        console.error("L'élément avec l'ID 'inProgress-trip' n'a pas été trouvé.");
        return;
    }
    const visibleInProgressTrips = trips.filter(trip => trip.statut === 'in_progress');
    if (visibleInProgressTrips.length === 0) {
        inProgressTrip.innerHTML = `
            <div class="alert alert-info text-center">
                Aucun trajet en cours.
            </div>
        `;
        return;
    }
    inProgressTrip.innerHTML = visibleInProgressTrips.map(trip => `
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5>Trajet :</h5>
                <p class="mb-1"><i class="far fa-calendar me-2"></i> Date : ${trip.dateDepart}</p>
                <p class="mb-1"><i class="fas fa-car me-2"></i> Véhicule : ${trip.vehicule?.marque ?? ''} ${trip.vehicule?.modele ?? ''}</p>
                <p class="mb-0"><i class="fas fa-users me-2"></i> Passagers à bord : ${trip.passagers ?? 'Aucun passager'}</p>  
            </div>
            <div class="col-md-4 text-md-end">
                <span class="trip-status status-in-progress disabled" id="trip-status-${trip.id}">En cours</span>
                <div class="mt-2">
                    <button class="btn btn-success btn-sm" id="end-trip" data-id="${trip.id}">Arrivée à destination</button>
                </div>
            </div>
        </div>
    `).join('');
}
function initInProgressTripsButtons() {
    const inProgressTrip = document.getElementById('inProgress-trip');
    if (!inProgressTrip) {
        console.error("L'élément avec l'ID 'inProgress-trip' n'a pas été trouvé.");
        return;
    }
    
    inProgressTrip.addEventListener('click', async (event) => {
        if (event.target.id === 'end-trip') {
            const tripId = event.target.dataset.id;
            const button = event.target;
            try {
            const result = await updateTripStatus(tripId, {statut: "completed"});

            alert(result.message);
            button.textContent = "Terminé";
            button.disabled = true;
            button.classList.remove("btn-success");
            button.classList.add("btn-secondary");
            await displayInProgressTrips();


        } catch (e) {
            alert(e.message);
        }
        }
    });
}
// onglet historique pour afficher les trajets terminés
async function loadCompletedTrips() {
    try {
          await  displayCompletedTrips();
        }
    catch (error) {
        console.error("Erreur lors de la récupération des informations de l'utilisateur:", error);
    }
}
//historique des trajets terminés
export async function displayCompletedTrips() {
    const historyTrip = document.getElementById('history-trip');
    const trips = await getMyTrips();
    if (!historyTrip) {
        console.error("L'élément avec l'ID 'history-trip' n'a pas été trouvé.");
        return;
    }
    const visibleHistoryTrips = trips.filter(trip => trip.statut === 'completed');
    if (visibleHistoryTrips.length === 0) {
        historyTrip.innerHTML = `
            <div class="alert alert-info text-center">
                Aucun trajet terminé.
            </div>
        `;
        return;
    }
    historyTrip.innerHTML = visibleHistoryTrips.map(trip => {
        const formattedDate = new Date(trip.dateDepart).toLocaleDateString('fr-FR');    
        const heureArrivee = new Date(trip.dateArrivee).toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        });
        return `
            <div class="row align-items-center">
                <h4 class="card-title">Trajet ${trip.id}</h4>
                <div class="col-md-8">
                    <h5 class="card-title">${trip.adresseDepart} → ${trip.adresseArrivee}</h5>
                    <p class="card-text">${formattedDate} - ${trip.duree ?? 'Non spécifiée'}</p>
                    <p class="card-text"><strong>Arrivée à ${heureArrivee}</strong></p>
                    
                    <p class="card-text"><strong>Note :</strong> ${trip.note ?? 'Non noté'}</p>
                    
                </div>    
                <div class="col-md-4 text-md-end">
                    <span class="trip-status status-completed">Terminé</span>
                    
                    <div class="mt-2">
                        <button class="btn btn-outline-secondary btn-sm details-trip" data-bs-toggle="modal" data-bs-target="#tripDetailsModal" data-id="${trip.id}">Détails</button>
                        <button class="btn btn-success validate-trip btn-sm" data-id="${trip.id}">Valider le trajet</button>
                        </div>
                </div>
                    </div>
                </div>
            </div>    
    `}).join('');
}
function initCompletedTripsButtons() {
    const historyTrip = document.getElementById('history-trip');
    if (!historyTrip) {
        console.error("L'élément avec l'ID 'history-trip' n'a pas été trouvé.");
        return;
    }
    
    historyTrip.addEventListener('click', async (event) => {
     
            const detailButton = event.target.closest('.details-trip');
            if (detailButton) {
                const tripId = detailButton.dataset.id;
                console.log("Afficher les détails du trajet avec l'ID :", tripId);
                if (!tripId) return;
   
            try {
            const modalEl = document.getElementById('tripDetailsModal');
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modalInstance.show();
            document.getElementById('tripDetailsContainer').innerHTML =
            '<p>Chargement...</p>';
                const tripDetails = await displayTripDetails(tripId);
                console.log("Détails du trajet :", tripDetails);
            } catch (error) {
                console.error("Erreur lors de la récupération des détails du trajet :", error);
                document.getElementById('tripDetailsContainer').innerHTML =
                '<p>Erreur lors de la récupération des détails du trajet.</p>';
            }
        }
    });
}
//AFFICHER DETAILS DU TRAJET DANS UNE MODALE
export async function displayTripDetails(id) {
    try {
        
        const trip = await getTripById(id);
        console.log(trip);
        console.log("Détails du trajet :", trip);

        const tripDetailsContainer = document.getElementById('tripDetailsContainer');
        if (!tripDetailsContainer) {
            console.error("L'élément avec l'ID 'tripDetailsContainer' n'a pas été trouvé.");
            return;
        }
        tripDetailsContainer.innerHTML = `
    <p><strong>Chauffeur :</strong> ${trip.chauffeur}</p>
    <p><strong>Adresse de départ :</strong> ${trip.adresseDepart}</p>
    <p><strong>Adresse d'arrivée :</strong> ${trip.adresseArrivee}</p>
        <p><strong>Horaire :</strong> ${trip.heureDepart} → ${trip.heureArrivee}</p>
    <p><strong>Date de départ :</strong> ${trip.dateDepart}</p>

    <p><strong>Véhicule :</strong> ${trip.vehicule}</p>

    <p><strong>Immatriculation :</strong> ${trip.immatriculation}</p>

    <p><strong>Énergie :</strong> ${trip.energie}</p>

    <p><strong>Places disponibles :</strong> ${trip.nombrePlace}</p>

    <p><strong>Prix :</strong> ${trip.prix} €</p>

    <p><strong>Durée :</strong> ${trip.duree}</p>

    <p><strong>Âge du chauffeur :</strong> ${trip.age} ans</p>

    <p><strong>Note :</strong> ${trip.note ?? 'Non noté'}</p>
    <p><strong>Passagers :</strong> ${trip.passagers}</p>
`;
    } catch (error) {
        console.error("Erreur lors de la récupération des détails du trajet :", error);
        const tripDetailsContainer = document.getElementById('tripDetailsContainer');
        if (tripDetailsContainer) {
            tripDetailsContainer.innerHTML = '<p>Erreur lors de la récupération des détails du trajet.</p>';
        }
    }
}
//creer un trajet

// Fonction pour remplir la liste déroulante des véhicules
async function fillVehiculeSelect(vehiculeId) {
    try {
        const vehicules = await getVehicules();
        const vehiculeSelect = document.getElementById('vehiculeSelect');
        if (!vehiculeSelect) {
            console.error('Élément de sélection du véhicule introuvable.');
            return;
        }
        vehiculeSelect.innerHTML = '';
        vehicules.forEach(vehicule => {
            const option = document.createElement('option');
            option.value = vehicule.id;
            option.textContent = vehicule.marque + ' ' + vehicule.modele;
            vehiculeSelect.appendChild(option);
        });
    } catch (error) {
        console.error("Erreur lors de la récupération des véhicules :", error);
    }
}

async function initCreateTripButtons() {
    const user = await getUserInfo();

    if (!user) {
        console.error("Impossible de récupérer les informations de l'utilisateur.");
        return;
    }
    console.log("Rôles de l'utilisateur :", user.roles);

    const becomeDriverBtn = document.getElementById('becomeDriverBtn');
    const becomePassengerBtn = document.getElementById('becomePassengerBtn');

    if (becomePassengerBtn && user.roles.includes("ROLE_CONDUCTEUR")) {
        becomePassengerBtn.addEventListener('click', async () => {
            try {
                const result = await updateUserInfo({ isConducteur: false });
                console.log("Utilisateur mis à jour (Passager) :", result);
                // On réaffiche pour masquer le formulaire
                await displayCreateTrip();
            } catch (error) {
                console.error("Erreur lors de la mise à jour de l'utilisateur :", error);
                alert("Une erreur est survenue. Veuillez réessayer.");
            }
        });
    }

    if (becomeDriverBtn) {
        becomeDriverBtn.addEventListener('click', async () => {
            try {
                const result = await updateUserInfo({ isConducteur: true });
                console.log("Utilisateur mis à jour (Conducteur) :", result);
                // IMPORTANT : On recharge l'affichage pour afficher le formulaire
                await displayCreateTrip();
            } catch (error) {
                console.error("Erreur lors de la mise à jour de l'utilisateur :", error);
                alert("Une erreur est survenue. Veuillez réessayer.");
            }
        });
    }

    // --- Autocomplétion OpenStreetMap ---
    const departInput = document.getElementById("departInput");
    const departResults = document.getElementById("depart-results");
    const arriveeInput = document.getElementById("arriveeInput");
    const arriveeResults = document.getElementById("arrivee-results");

    if (departInput && departResults) {
        departInput.addEventListener("input", async () => {
            const results = await searchOpenStreetMap(departInput.value);
            showSuggestions(results, departResults, departInput, "depart");
        });
    }

    if (arriveeInput && arriveeResults) {
        arriveeInput.addEventListener("input", async () => {
            const results = await searchOpenStreetMap(arriveeInput.value);
            showSuggestions(results, arriveeResults, arriveeInput, "arrivee");
        });
    }

    // Initialisation du formulaire de soumission (gestion du clic de création)
    initcreateTripForm();
}

async function displayCreateTrip() {
    const userInfo = await getUserInfo();
    const tripFormContainer = document.getElementById("createTripFormContainer");
    const becomeDriverBtn = document.getElementById("becomeDriverBtn");
    const becomePassengerBtn = document.getElementById("becomePassengerBtn");
    if (!tripFormContainer || !becomeDriverBtn || !becomePassengerBtn) {
        console.error("Éléments de formulaire manquants.");
        return;
    }
    if (userInfo.roles.includes("ROLE_CONDUCTEUR")) {
        tripFormContainer.style.display = "block";
        becomeDriverBtn.style.display = "none";
        becomePassengerBtn.style.display = "inline-block";
    }
    else {
        tripFormContainer.style.display = "none";
        becomeDriverBtn.style.display = "inline-block";
        becomePassengerBtn.style.display = "none";
    }
    await fillVehiculeSelect();
}
async function initRoleSelection() {
    const tripFormContainer = document.getElementById("createTripFormContainer");
    const conducteurCheck = document.getElementById("editIsConducteurMain");
    const passagerCheck = document.getElementById("editIsPassagerMain");

    if (!tripFormContainer || !conducteurCheck || !passagerCheck) {
        console.error("Éléments de sélection de rôles manquants.");
        return;
    }

    const isConducteur = conducteurCheck.checked;
    tripFormContainer.style.display = isConducteur ? "block" : "none";
   
    const data = {
        isConducteur: conducteurCheck.checked,
        isPassager: passagerCheck.checked
    };

    try {
        const result = await updateUserInfo(data);
        console.log("Rôles mis à jour :", result);
        alert("Rôles mis à jour avec succès.");
        await displayCreateTrip(); // Rafraîchit l'interface des trajets en fonction du rôle
    } catch (error) {
        console.error("Erreur lors de la mise à jour des rôles :", error);
        alert("Une erreur est survenue.");
    }
}

// Fonction pour soumettre le formulaire de création de trajet
async function initcreateTripForm() {
    const createTripBtn = document.getElementById('createTripBtn');
    const tripForm = document.getElementById('tripForm');

    if (!tripForm || !createTripBtn) {
        return; // Éléments pas encore présents ou hors contexte
    }
    
    // Pour éviter de rajouter plusieurs fois l'écouteur si la fonction est rappelée
    createTripBtn.replaceWith(createTripBtn.cloneNode(true));
    const newCreateTripBtn = document.getElementById('createTripBtn');

    newCreateTripBtn.addEventListener('click', async (event) => {
        event.preventDefault();
        const VehiculeSelect = document.getElementById('vehiculeSelect');
        const formData = new FormData(tripForm);
        const data = {
            adresseDepart: formData.get('adresseDepart'),
            adresseArrivee: formData.get('adresseArrivee'),
            dateDepart: formData.get('dateDepart'),
            prix: formData.get('prix'),
            placeDisponible: Number(formData.get('placeDisponible')),
            vehicule: VehiculeSelect ? VehiculeSelect.value : null,
        };

        console.log("Données du formulaire de création de trajet :", data);
        
        try {
            const result = await addTrip(data);
            console.log("Résultat de la création du trajet :", result);
            alert(result.message || "Trajet créé avec succès !");
            tripForm.reset();
            
            const userInfo = await getUserInfo();
            if (userInfo && typeof displayPlannedTrips === 'function') {
                displayPlannedTrips(userInfo.trajetsProposes);
            }
        } catch (error) {
            console.error("Erreur lors de la création du trajet:", error);
            alert("Une erreur est survenue lors de la création du trajet.");
        }
    });
}

