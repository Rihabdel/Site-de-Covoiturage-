import Route from "./Route.js";

export const allRoutes = [
  new Route("/", "Accueil", "./views/home.html", [], "./js/home.js"),
  new Route(
    "/contact",
    "Contact",
    "./views/contact.html",
    [],
    "./js/contact.js",
  ),
  new Route(
    "/recherche",
    "Recherche",
    "./views/recherche.html",
    [],
    "./js/recherche.js",
  ),

  // ADMIN : Vérifie que le fichier est bien à la racine de /pages/
  new Route(
    "/admin",
    "Administration",
    "./public/views/admin.html",
    ["ROLE_ADMIN"],
    "./js/admin.js",
  ),

  // CONNEXION : Dans le sous-dossier auth
  new Route(
    "/login",
    "Connexion",
    "./public/views/auth/connexion.html",
    ["disconnected"],
    "./js/auth/connexion.js",
  ),

  // MON COMPTE : Ton fichier s'appelle office.html et est à la racine de /pages/
  new Route(
    "/monCompte",
    "Mon Compte",
    "./public/views/auth/profil.html",
    ["ROLE_USER"],
    "./js/auth/profil.js",
  ),
  // MODIFICATION DU MOT DE PASSE : Ton fichier s'appelle editPassword.html et est à la racine de /pages/
  new Route(
    "/editPassword",
    "Modification du mot de passe",
    "./public/views/auth/editPassword.html",
    ["connected"],
    "./js/auth/editPassword.js",
  ),
  // INSCRIPTION : Ton fichier s'appelle inscript.html à la racine de /pages/
  new Route(
    "/inscription",
    "Inscription",
    "./public/views/auth/inscription.html",
    ["disconnected"],
    "./js/auth/inscription.js",
  ),

  new Route("/404", "Page introuvable", "./public/views/404.html", []),
  new Route(
    "/trajets",
    "Mes trajets",
    "./public/views/trajets.html",
    ["ROLE_USER", "ROLE_ADMIN", "ROLE_CONDUCTEUR"],
    "./js/trajets.js",
  ),
];

export const websiteName = "Ecoride";
