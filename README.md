# Site-de-Covoiturage-
# EcoRide

**EcoRide** est une application web de covoiturage développée dans le cadre de mon parcours de formation en développement web.

## Présentation

L'objectif du projet est de permettre aux utilisateurs de proposer et de rechercher des trajets de covoiturage.

L'application comprend notamment :

* la gestion des utilisateurs ;
* la création et la recherche de trajets ;
* la gestion des réservations ;
* l'authentification des utilisateurs ;
* la gestion des données en base de données.

##  Technologies utilisées

### Backend

* PHP
* Symfony
* API REST
* PostgreSQL

### Frontend

* HTML5
* CSS3
* JavaScript
* Bootstrap
* Sass / SCSS

### Outils

* Docker
* Git
* GitHub

##  Démo

**Application en ligne :**
https://site-de-covoiturage.onrender.com/

 Installation

### Prérequis

* PHP
* Composer
* Symfony CLI
* PostgreSQL
* Docker / Docker Compose

### Installation

1 .Clone the current repository (SSH):

```bash
$ git clone 'https://github.com/Rihabdel/Site-de-Covoiturage-'
````

2 . Move in and create few `.env.{environment}.local` files, according to your environments with your default configuration.

```bash
$ cp .env .env.local  
```

3 . Initialisation avec Docker :
Le projet utilise Docker pour isoler les services MySQL (données relationnelles) et MongoDB.

```bash
$ docker-compose up -d
```

4. Dépendances et Base de données

```bash
$ composer install        # Install all PHP packages
$ php bin/console d:d:c   # Create your DATABASE related to your .env.local configuration
$ php bin/console d:m:m   # Run migrations to setup your DATABASE according to your entities
```

### Lancer le projet

```bash
symfony server:start
```

## 👩‍💻 Auteur

**Rihab CHIBANIr**

Développeuse Web Full Stack Junior
