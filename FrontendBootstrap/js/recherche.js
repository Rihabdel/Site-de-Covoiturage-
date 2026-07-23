import { getTrips , getTripById, participerCovoiturage  } from './api.js';
import { isConnected } from '../script.js';
import  { searchOpenStreetMap } from './openstreetmap.js';

export default function initRecherche() {
console.log("Initialisation de la recherche de covoiturages...");
    initBoutonsRecherche();

}


export async function initBoutonsRecherche() {
    const container = document.getElementById("results-covoiturages");
    if (!container) {
        console.error("L'élément avec l'ID 'results-covoiturages' n'a pas été trouvé.");
        return;
    }
    const  searchBtn = document.getElementById("searchBtn");
        if (searchBtn) {
            initSearchForm();
        }
        const departInput = document.getElementById("departInput");
        const departResults = document.getElementById("depart-results");
        const arriveeInput = document.getElementById("arriveeInput");
        const arriveeResults = document.getElementById("arrivee-results");

        if (!departInput || !departResults || !arriveeInput || !arriveeResults) {
    console.error("Un ou plusieurs éléments de recherche sont introuvables.");
    return;
}

        departInput.addEventListener("input", async () => {

            const results = await searchOpenStreetMap(
                departInput.value
            );

            showSuggestions(
                results,
                departResults,
                departInput,
                "depart"
            );

        });

        arriveeInput.addEventListener("input", async () => {

            const results = await searchOpenStreetMap(
                arriveeInput.value
            );
            showSuggestions(
                results,
                arriveeResults,
                arriveeInput,
                "arrivee"
            );
        });

        container.addEventListener("click", async (event) => {
        const detailButton = event.target.closest('.detailBtn');
        if (detailButton) {
                    const tripId = detailButton.dataset.id;
                    console.log("Afficher les détails du trajet avec l'ID :", tripId);
                try {
                const modalEl = document.getElementById('tripDetailsModal');
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modalInstance.show();
                document.getElementById('tripDetailsContainer').innerHTML =
                '<p>Chargement...</p>';
                    await displayTripDetail(tripId);
                } catch (error) {
                    console.error("Erreur lors de la récupération des détails du trajet :", error);
                    document.getElementById('tripDetailsContainer').innerHTML =
                    '<p>Erreur lors de la récupération des détails du trajet.</p>';
                }
        }
        });
    
        const participerContainer= document.getElementById("tripDetailsContainer");
        participerContainer.addEventListener("click", async (event) => {
            
            const btnParticiper = event.target.closest("#participerBtn");
            if (!btnParticiper) return;
            console.log("Bouton Participer cliqué !");
            if (!isConnected()) {
                bootstrap.Modal
                    .getOrCreateInstance(document.getElementById("loginRequiredModal"))
                    .show();
                return;
            }
            
            document.getElementById("creditsUtilises").textContent =
            btnParticiper.dataset.prix;
            document.getElementById("confirmParticipationBtn").dataset.id = btnParticiper.dataset.id;
            bootstrap.Modal
            .getOrCreateInstance(document.getElementById("participationModal"))
            .show();
        });

        const confirmParticipationBtn = document.getElementById("confirmParticipationBtn");
        confirmParticipationBtn.addEventListener("click", async () => {
            const tripId = confirmParticipationBtn.dataset.id; 
            console.log("Confirmer la participation pour le trajet avec l'ID :", tripId);
            confirmParticipationBtn.disabled = true;

            try {
                await participerCovoiturage(tripId);
                bootstrap.Modal
                            .getInstance(document.getElementById("participationModal"))
                            .hide();
                        alert("Participation confirmée !");
                    } finally {
                        confirmParticipationBtn.disabled = false;
                    }
                
        
        });

        const loginBtn = document.getElementById("goLoginBtn");
        loginBtn.addEventListener("click", () => {

            bootstrap.Modal
                .getInstance(document.getElementById("loginRequiredModal"))
                .hide();

            window.location.hash = "#/login";
        });

}
    async function displayTrips(trips) {

    const container =document.getElementById("results-covoiturages");
        if (!trips || trips.length === 0) {

            container.innerHTML = `

            <div class="alert alert-info">

                Aucun covoiturage disponible.

            </div>

            `;

            return;
        }
        container.innerHTML = trips.map(trip => `
            <div class="ride-card" data-id="${trip.id}">
                <div class="card-header">
                    <img src="${trip.chauffeur?.photo ?? 'https://via.placeholder.com/150'}" alt="Photo de profil" class="driver-photo">
                    <div class="driver-info">
                        <div class="driver-name">${trip.chauffeur?.pseudo ?? "Inconnu"}</div>
                        <div class="driver-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            ${trip.chauffeur?.note ?? "Pas encore noté"}
                        </div>
                    </div>
                </div>
                    <div class="card-body">
                        <div class="ride-details">
                            <div class="detail-item">
                                <span class="detail-label">Départ</span>
                                <span class="detail-value">${new Date(trip.dateDepart).toLocaleString('fr-FR')}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Arrivée</span>
                                <span class="detail-value">${new Date(trip.dateArrivee).toLocaleString('fr-FR')}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Places</span>
                                <span class="detail-value">${trip.placeDisponible} disponibles</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Durée</span>
                                <span class="detail-value">${trip.duree}</span>
                            </div>
                            <div class="eco-badge">
                                <i class="fas fa-leaf"></i> ${trip.voyageEcologique ? "Trajet écologique" : "Trajet Non écologique"}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="price">${trip.prix} € par personne</div>
                        <button class="btn btn-primary detailBtn" data-id="${trip.id}">Voir détail</button>
                    </div>
            </div>`).join("");
}
export function buildFilters() {

  const prixMax = document.getElementById("prixMax").value;
  const dureeMax = document.getElementById("dureeMax").value;
  const ecologique = document.getElementById("flexCheckDefault").checked;
  const note = document.getElementById("noteInput").value;
const filters = {};

    //  prix
    if (prixMax && prixMax > 0) {
        filters.prixMax = prixMax;
    }

  //  duree
  if (dureeMax && dureeMax > 0) {
    filters.dureeMax = dureeMax;
  }

  // 🎯 ecologique
    if (ecologique !== "all") {
    filters.ecologique = ecologique === "true";
  }

 if (note && note !== "all" && note !== "Note minimum") {
    filters.note = note;
}
 if (depart) {
    filters.depart = depart;
  }
  if (arrivee) {
    filters.arrivee = arrivee;
  }
  return filters;
}
async function applyFilters() {
  try {
    const filters = buildFilters();
    console.log("Filtres appliqués :", filters);
    const filteredTrips = await getTrips(filters);
    console.log("Covoiturages filtrés :", filteredTrips);
    displayTrips(filteredTrips);
  } catch (error) {
    console.error("Erreur lors de l'application des filtres :", error);
   
  }
}
        // Ajouter des écouteurs d'événements aux éléments de filtre
const prix = document.getElementById("prixMax");
const duree = document.getElementById("dureeMax");
const ecologique = document.getElementById("flexCheckDefault");
const note = document.getElementById("noteInput");

const resetFiltersBtn = document.getElementById("resetFilters");
if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener("click", () => {
        prix.value = 50;
        duree.value = "";
        ecologique.value = "";
        note.value = "";
        applyFilters();
    });
}
if (prix) prix.addEventListener("change", applyFilters);
if (duree) duree.addEventListener("change", applyFilters);
if (ecologique) ecologique.addEventListener("change", applyFilters);
if (note) note.addEventListener("change", applyFilters);


// ====================== FIN FILTRES ======================
export async function displayTripDetail(id) {
    try {
        
        const trip = await getTripById(id);
        console.log("Détails du trajet :", trip);
        const tripDetailsContainer = document.getElementById('tripDetailsContainer');
        if (!tripDetailsContainer) {
            console.error("L'élément avec l'ID 'tripDetailsContainer' n'a pas été trouvé.");
            return;
        }
        tripDetailsContainer.innerHTML = `

<div class="container-fluid card p-0">
    <div class="card-body">
    <div class="row justify-content-between align-items-center">
            <div class="col-md-8">
                <div class="text-center mb-3">
                <i class="bi bi-person-circle display-1 text-secondary"></i>
                </div>
                    width="100" height="100" alt="Photo chauffeur">
                <h4 class="mt-3 mb-1">${trip.chauffeur}</h4>
                <p>
                    <i class="bi bi-person-fill me-2"></i>
                    ${trip.age} ans
                </p>
                <span class="badge bg-warning text-dark">
                    ⭐ ${trip.note ?? "Non noté"}
                </span>
            </div>
            <div class="col-md-4 text-end">
                    <h3 class="text-success mb-0 ">
                        ${trip.prix} crédits
                    </h3>
            </div>
        </div>
        <hr>
        
        <div class="row g-3">
            <div class="col-md-7">
            <h5 class="text-success mb-3">
                Détails du trajet
            </h5>
                <h3 class="mb-3">
                    <i class="bi bi-geo-alt-fill text-success me-2"></i>
                    ${trip.adresseDepart} → ${trip.adresseArrivee}
                </h3>
                <h5 class="mb-3">
                    <i class="bi bi-calendar-event me-2"></i>
                    ${trip.dateDepart}
                </h5>
                <h5 class="mb-3">
                    <i class="bi bi-clock me-2"></i>
                    ${trip.heureDepart} → ${trip.heureArrivee}
                </h5>
                <p>
                    <i class="bi bi-hourglass-split me-2"></i>
                    <strong>Durée</strong>
                    ${trip.duree}
                </p>
                <p>
                    <i class="bi bi-people-fill me-2"></i>
                    <strong>Places disponibles</strong>
                    ${trip.placeDisponible}
                </p>
            </div>
            <div class="col-md-5">
                <h5 class="text-success mb-3">
                    Informations sur le véhicule
                </h5>
                <p>
                    <i class="bi bi-car-front-fill me-2"></i>
                    <strong>Véhicule</strong><br>${trip.vehicule}
                </p>
                <p>
                    <i class="bi bi-credit-card-2-front me-2"></i>
                    <strong>Immatriculation</strong><br>
                    ${trip.immatriculation}
                </p>
                <p>
                    <i class="bi bi-fuel-pump-fill me-2"></i>
                    <strong>Énergie</strong><br>
                    ${trip.energie}
                </p>
            </div>
            <div class="col-12">
                <i class="fas fa-user me-2"></i>
                    <strong>Passagers :</strong> ${trip.passagers?.pseudo ?? "Aucun passager pour le moment"}
            </div>
                </div> 
        </div>  
        <hr>
        <div class="d-flex justify-content-end">
            <button
                class="btn btn-success btn-lg"
                id="participerBtn"
                data-id="${trip.id}"
                data-prix="${trip.prix}">
                <i class="bi bi-check-circle-fill me-2"></i>
                Participer
            </button>
        </div>
    </div>

</div>
`;
    } catch (error) {
        console.error("Erreur lors de la récupération des détails du trajet :", error);
        const tripDetailsContainer = document.getElementById('tripDetailsContainer');
        if (tripDetailsContainer) {
            tripDetailsContainer.innerHTML = '<p>Erreur lors de la récupération des détails du trajet.</p>';
        }
    }
}
//formulaire de recherche
async function initSearchForm() {
    const  searchBtn = document.getElementById('searchBtn');
    const form = document.getElementById('searchForm');
    if (!searchBtn) return;
   
    searchBtn.addEventListener('click', async (e) => {
        e.preventDefault();

        console.log("Formulaire intercepté !");

        const formData = new FormData(form);

        const data = {
            date: formData.get('dateDepart'),
            depart: formData.get('adresseDepart'),
            arrivee: formData.get('adresseArrivee'),
        };

        console.log("Paramètres envoyés :", data);

        try {
            const trips = await getTrips(data);

            console.log("Covoiturages récupérés :", trips);

            displayTrips(trips);

        } catch (error) {
            console.error(
                "Erreur lors de la récupération des covoiturages :",
                error
            );
        }
    });
}

export function showSuggestions(results, container, input, type) {
    if (!container) {
        console.error("Container de suggestions introuvable.");
        return;
    }
    container.innerHTML = "";

    results.forEach(place => {

        const item = document.createElement("button");

        item.type = "button";
        item.className = "list-group-item list-group-item-action";
        item.textContent = place.display_name;

        item.addEventListener("click", () => {

            input.value = place.display_name;

            container.innerHTML = "";

            if (type === "arrivee") {

                document.getElementById("latitudeArrivee").value = place.lat;
                document.getElementById("longitudeArrivee").value = place.lon;

                document.getElementById("villeArrivee").value =
                    place.address.city ||
                    place.address.town ||
                    place.address.village ||
                    "";

            }
            if (type === "depart") {

                document.getElementById("latitudeDepart").value = place.lat;
                document.getElementById("longitudeDepart").value = place.lon;
                document.getElementById("villeDepart").value =
                    place.address.city ||
                    place.address.town ||
                    place.address.village ||
                    "";
                    
            }
        }
        );
        container.appendChild(item);

    });
}