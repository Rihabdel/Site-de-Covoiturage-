
import { setApiToken, showAndHideElementsForRoles } from "../script.js";
import { API_URL } from "../api.js";
const emailInput = document.getElementById("EmailInput");
const passwordInput = document.getElementById("PasswordInput");
const btnConnexion = document.getElementById("BtnLogin");
const formConnexion = document.getElementById("FormConnexion");

btnConnexion.addEventListener("click", (e) => checkCredentials(e)); 

function checkCredentials(e) {
    e.preventDefault();
    let dataForm = new FormData(formConnexion);
    let myHeaders = new Headers();
    myHeaders.append("Content-Type", "application/json");
    const raw = JSON.stringify({
        email: dataForm.get("email"),  
        password: dataForm.get("password")
    });
    const requestOptions = {
    method: "POST",
    headers: myHeaders,
    body: raw,
};
fetch(`${API_URL}/login`, requestOptions)
    .then(response => {
        if (!response.ok) throw new Error(response.status); 
        return response.json();
    })
    .then(result => {
        console.log("Connexion réussie :", result);
        const token = result.token;
        setApiToken(token);
        localStorage.setItem("roles", JSON.stringify(result.roles));    
        console.log("Connexion réussie, token stocké dans le cookie.");
        showAndHideElementsForRoles();
        globalThis.location.replace("/monCompte");
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Identifiants incorrects. Veuillez réessayer.");
    });
}


