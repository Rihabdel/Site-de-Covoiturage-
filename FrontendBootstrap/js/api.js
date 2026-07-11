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
