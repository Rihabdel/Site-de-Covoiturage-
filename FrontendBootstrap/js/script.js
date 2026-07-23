import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import '../scss/_custom.scss';
import '../Router/Router.js';
export const API_URL = 'https://ecoride-api.onrender.com/api'; 
export const roleCookieName = 'role'; 

// ------------------------------------
// LOGIQUE DU TOKEN (COOKIES)
// ------------------------------------
export function setApiToken(token) {
    setCookie("accesstoken", token, 7); 
}

export function getApiToken() {
    const token = getCookie("accesstoken");
    if (!token || token === "undefined" || token === "null" || token.trim() === "") {
        return null;
    }
    return token;
}

export function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        let date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

export function getCookie(name) {
    let nameEQ = name + "=";
    let ca = document.cookie.split(';');
    for(let i=0; i < ca.length; i++) {
        let c = ca[i].trim();
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function eraseCookie(name) {   
    document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

// ------------------------------------
// LOGIQUE DU RÔLE (LOCAL STORAGE)
// ------------------------------------
export function getRole() {
    let role = localStorage.getItem("roles"); // Récupère le tableau en chaîne: '["ROLE_USER"]'
    if (!role) return null;
    
    // Nettoie les crochets [ ] et les guillemets " pour n'avoir que ROLE_USER
    role = role.replace(/[\[\]"]/g, '').trim(); 
    
    return role;
}

// ------------------------------------
// SÉCURITÉ & ROUTEUR
// ------------------------------------
export function isConnected() {
    return getApiToken() !== null;
}

export function waitForAuth() {
    return new Promise((resolve) => {
        const token = getApiToken();
        const role = getRole();
        resolve({ token, role, isConnected: token !== null });
    });
}

export function signout() {
    eraseCookie("accesstoken");
    localStorage.removeItem("roles");
    alert("Vous êtes déconnecté.");
    globalThis.location.href = "/login";
}
// Affichage des éléments selon le rôle
export function showAndHideElementsForRoles() {
    const userConnected = isConnected(); 
    const role = getRole();

    console.log("showAndHideElementsForRoles - Connecté:", userConnected, "Rôle:", role);

    document.querySelectorAll('[data-show]').forEach(element => {
        const values = element.dataset.show
            .split(',')
            .map(v => v.trim());

        const shouldShow =
            (values.includes("disconnected") && !userConnected) ||
            (values.includes("connected") && userConnected) ||
            (userConnected && role && values.includes(role));

        element.style.display = shouldShow ? "" : "none";
        
    
    });
}

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
showAndHideElementsForRoles();
