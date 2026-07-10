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
const form = document.getElementById("contactForm");
if (form) {
    console.log("Formulaire contact trouvé, ajout de l'événement submit");
    form.addEventListener("submit", newContactMsg);
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