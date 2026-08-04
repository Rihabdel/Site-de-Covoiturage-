import "./scss/main.scss";
import "./Router/Router.js";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
export const API_URL = "https://ecoride-api.onrender.com/api";

export function sanitizeHtml(text) {
  // Créez un élément HTML temporaire de type "div"
  const tempHtml = document.createElement("div");

  // Affectez le texte reçu en tant que contenu texte de l'élément "tempHtml"
  tempHtml.textContent = text;

  // Cela va "neutraliser" ou "échapper" tout code HTML potentiellement malveillant
  return tempHtml.innerHTML;
}

showAndHideElementsForRoles();
