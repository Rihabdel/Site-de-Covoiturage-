import { getApiToken} from './script.js';
export const API_URL = "http://127.0.0.1:8000/api";

// Récupération des infos utilisateur
export async function getUserInfo() {
    const myHeaders = new Headers();
    myHeaders.append("X-AUTH-TOKEN", getApiToken());
    const requestOptions = {
        method: "GET",
        headers: myHeaders,
    };
    const response = await fetch(`${API_URL}/user`, requestOptions);
    if (!response.ok) throw new Error(`Erreur ${response.status}`);
    return await response.json();
}
// Mise à jour des infos utilisateur
export async function updateUserInfo(updatedData) {
    const response = await fetch(`${API_URL}/user/update`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-AUTH-TOKEN': getApiToken()
        },
        body: JSON.stringify(updatedData) 
    });

    if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || `Erreur ${response.status}`);
    }
    return await response.json();
}

export async function deleteUserAccount() {
    const response = await fetch(`${API_URL}/user/delete`, {
        method: 'DELETE',
        headers: {
            'X-AUTH-TOKEN': getApiToken()
        }
    });
    if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || `Erreur ${response.status}`);
    }
    return await response.json();
}

// Récupération des véhicules d'un utilisateur
export async function getVehicules() {
    const myHeaders = new Headers();
    myHeaders.append("X-AUTH-TOKEN", getApiToken());
    const requestOptions = {
        method: "GET",
        headers: myHeaders,
    };
    return fetch(`${API_URL}/vehicule/user`, requestOptions)
        .then(response => {
            if (!response.ok) throw new Error(`Erreur ${response.status}`);
            return response.json();
        }
    );
}
export async function addVehicule(vehiculeData) {
    const response = await fetch(`${API_URL}/vehicule/add`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-AUTH-TOKEN': getApiToken()
        },
        body: JSON.stringify(vehiculeData)
    });
    if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || `Erreur ${response.status}`);
    }
    return await response.json();
}

export async function updateVehicule(id, vehiculeData) {
    const response = await fetch(`${API_URL}/vehicule/update/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-AUTH-TOKEN': getApiToken()
        },
        body: JSON.stringify(vehiculeData)
    });
    if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || `Erreur ${response.status}`);
    }
    return await response.json();
}

export async function deleteVehicule(vehiculeId) {
    const response = await fetch(`${API_URL}/vehicule/delete/${vehiculeId}`, {
        method: 'DELETE',
        headers: {
            'X-AUTH-TOKEN': getApiToken()
        }
    });
    if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || `Erreur ${response.status}`);
    }
    return await response.json();
}
export async function getVehiculeById(vehiculeId) {
    const myHeaders = new Headers();
    myHeaders.append("X-AUTH-TOKEN", getApiToken());
    const requestOptions = {
        method: "GET",
        headers: myHeaders,
    };
    const response = await fetch(`${API_URL}/vehicule/user/${vehiculeId}`, requestOptions);
    if (!response.ok) throw new Error(`Erreur ${response.status}`);
    return await response.json();
}
// Récupération des trajets d'un utilisateur
export async function getMyTrips() {
    const myHeaders = new Headers();
    myHeaders.append("X-AUTH-TOKEN", getApiToken());

    const response = await fetch(`${API_URL}/covoiturage/me`, {
        method: "GET",
        headers: myHeaders,
    });

    if (!response.ok) {
        throw new Error(`Erreur ${response.status}`);
    }
    const data = await response.json();
    return data.data; 
}
// Récupération d'un trajet par son ID
export async function getTripById(tripId) {
    const myHeaders = new Headers();
    myHeaders.append("X-AUTH-TOKEN", getApiToken());
    const requestOptions = {
        method: "GET",
        headers: myHeaders,
    };
    const response = await fetch(`${API_URL}/covoiturage/detail/${tripId}`, requestOptions);
    if (!response.ok) throw new Error(`Erreur ${response.status}`);
    return await response.json();
}

// Annulation d'un trajet
export async function cancelTrip(tripId) {
    const response = await fetch(`${API_URL}/covoiturage/${tripId}`, {
        method: 'DELETE',
        headers: {
            'X-AUTH-TOKEN': getApiToken()
        }
    });
    if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || `Erreur ${response.status}`);
    }
    return await response.json();
}
//modification du statut d'un trajet    
export async function updateTripStatus(tripId, updatedData) {
    const response = await fetch(`${API_URL}/covoiturage/${tripId}/statut`, {
    method: "PATCH", // ou PUT selon ta route
    headers: {
        "Content-Type": "application/json",
        "X-AUTH-TOKEN": getApiToken()
    },
    body: JSON.stringify({
        statut: updatedData.statut
    })
});
    if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || `Erreur ${response.status}`);
    }
    return await response.json();
}

//afficher les trajets disponibles
export async function getTrips(filters = {}) {

    const params = new URLSearchParams(filters);

    const response = await fetch(`${API_URL}/covoiturage/list?${params.toString()}`);

    if (!response.ok) {
        throw new Error("Erreur API");
    }

   return await response.json();
   
}
export async function participerCovoiturage(id) {

    const response = await fetch(`${API_URL}/covoiturage/participer/${id}`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-AUTH-TOKEN": getApiToken()
        }
    });

    const data = await response.json();

    if (!response.ok) {
        throw new Error(data.message);
    }

    return data;
}

