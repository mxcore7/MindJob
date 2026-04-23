# 🚀 Présentation du Projet : MindJob

## 1. Introduction & Objectif du Projet
**MindJob** est une application web intelligente conçue pour optimiser la recherche d'emploi et le recrutement. Son but est d'offrir une plateforme complète permettant aux utilisateurs de gérer leur profil (compétences, expériences), d'être recommandés pour des offres d'emploi ciblées, et de retrouver au même endroit des offres réelles importées via des API externes.

L'application repose sur une architecture moderne de type **SPA (Single Page Application)**, séparant totalement le système de données (Backend) de l'interface visuelle (Frontend) afin de garantir maintenabilité, scalabilité (facilité de montée en charge), et évolutivité.

---

## 2. Architecture Technique (La Stack)

Le projet est divisé en deux piliers technologiques :
- **Backend (Moteur de la logique & API)** : Développé avec le framework **Laravel 11** (PHP). 
- **Frontend (Interface Utilisateur)** : Développé en **Vue 3** compilé avec **Vite**, et stylisé de façon pure avec **Tailwind CSS**.

### Base de données :
- **PostgreSQL** : Choisi pour sa robustesse relationnelle et surtout ses excellentes capacités de traitement natif du format JSON (crucial ici car les compétences et préférences utilisateurs/jobs utilisent des colonnes JSON, favorisant la flexibilité d'évolution).

---

## 3. L'Écosystème Backend (Laravel) - Dossier `d:\MindJod\backend\`

Le backend suit strictement le design pattern **MVC (Model-View-Controller)** adapté aux API, servant exclusivement de fournisseur de données (via l'envoi de format JSON). 

### 🗂️ Les dossiers et fichiers vitaux pour comprendre la structure :

*   **`routes/api.php`** 
    *   *Rôle :* C'est le standardiste de l'application. Dès que le frontend a besoin d'informations, la requête atterrit ici. Ce fichier lie une URL (ex: `/api/jobs`) à une fonction précise du code, et filtre ce qui nécessite une authentification.
*   **`app/Http/Controllers/`** 
    *   *Rôle :* Les chefs d'orchestre. Par exemple, `JobController.php` va recevoir une requête venant du routeur, demander à la base de données de trouver les offres correspondantes, et les formater en JSON pour les rendre au frontend.
*   **`app/Models/`** 
    *   *Rôle :* L'ORM (Object Relational Mapping) Eloquent de Laravel. Par exemple, `Job.php` désigne la table `jobs`. Cela permet aux développeurs de manipuler les lignes de la base de données sous forme d'objets, rendant le code beaucoup plus lisible.
*   **`app/Services/` & `app/Console/Commands/`** 
    *   *Rôle :* Les services encapsulent les algorithmes complexes (le moteur de recommandation, ou la logique d'appel aux API d'Indeed). Les commandes (ex: `php artisan jobs:fetch`) permettent d'automatiser (via CRON) des tâches récurrentes comme la synchronisation quotidienne avec France Travail.
*   **`database/migrations/`** 
    *   *Rôle :* Le cahier des charges de la base de données. Ce sont des fichiers PHP qui décrivent exactement la structure des tables (ex: colonnes id, title, skills_required). C'est parfait pour le travail en équipe : tout le monde peut recréer la BDD depuis zéro en tappant `php artisan migrate`.
*   **`.env`** 
    *   *Rôle :* C'est le fichier des secrets (mots de passe BDD, clés API). Il est strictement ignoré par Git (non versionné) pour des raisons de sécurité.

### 🔐 Authentification & Sécurité :
- Le module utilisé est **Laravel Sanctum**. Lors du login, le backend génère un ***Token***. Le Frontend va conserver ce token et devra le fournir pour prouver l'identité de l'utilisateur à chaque action privée. Les mots de passe en BDD sont bien-sûr cryptés via `bcrypt`.

---

## 4. L'Écosystème Frontend (Vue 3) - Dossier `d:\MindJod\frontend\`

Cette partie est autonome. C'est le client qui viendra se connecter à l'API de Laravel. Son rôle est de proposer une expérience fluide et réactive, sans rafraîchir la page à chaque clic d'un bouton.

### 🗂️ Les dossiers et ressources du cycle de vie Vue.js :

*   **`src/main.js`** 
    *   *Rôle :* Le fichier de lancement principal. Il instancie globalement l'application Vue, insère le gestionnaire de variables globales (Pinia) et le système de pagination côté client (Vue Router).
*   **`src/router/index.js`** 
    *   *Rôle :* Le "routeur client". Il écoute l'URL dans le navigateur (ex: `/dashboard` ou `/jobs`) et injecte immédiatement à l'écran le bon composant, sans nécessiter d'attendre une réponse de rendu du serveur.
*   **`src/views/`** 
    *   *Rôle :* Les composants qui correspondent à des "pages pleines" (ex: `JobsPage.vue`, `UserProfile.vue`, `Dashboard.vue`).
*   **`src/components/`** 
    *   *Rôle :* Les éléments réutilisables de l'interface. Au lieu de coder 100 fois l'apparence d'une carte de Job, on crée un `JobCard.vue` qui acceptera les données d'un job de manière dynamique. Cela garantit un code modulaire et l'absence totale de répétition (DRY : Don't Repeat Yourself).
*   **`src/stores/` (Géré par Pinia)** 
    *   *Rôle :* La mémoire centrale du front. Si un utilisateur se connecte, nous devons afficher son nom sur la Navbar, mais aussi récupérer son ID pour ses statistiques dans le Dashboard... Le *store Pinia* permet de rendre cette donnée commune et accessible partout sans refaire d'appel à l'API Laravel.
*   **`src/api/` (Axios)** 
    *   *Rôle :* Centralise les configurations des requêtes HTTP réseau. C'est ici que l'on s'assure par exemple que le fameux *Token Sanctum* est bien inséré en "Header" de la requête invisiblement avant l'envoi au backend.

---

## 5. Zoom : L'API de Recherche d'Emplois (Jobs API)
L'une des forces du projet est sa capacité à récupérer et afficher des offres d'emploi, à la fois depuis sa propre base de données et depuis des sources externes (comme France Travail).

### ⚙️ Le moteur de l'API (Backend)
*   **`app/Services/FranceTravailService.php`** :
    *   C'est le "Service métier" qui communique avec l'extérieur. Il gère l'authentification sécurisée (OAuth2 via les clés `.env`) avec France Travail, met en cache le *Token* pendant 20 minutes pour optimiser les requêtes, et propose des fonctions de recherche (`searchOffres`), de détail (`getOffre`), et d'importation de masse (`fetchAndStore`).
    *   La fonction `fetchAndStore()` est particulièrement puissante : elle importe les offres de l'API, extrait intelligemment les compétences des listes texte, nettoie la syntaxe HTML des descriptions, évite les doublons, et les sauvegarde directement dans la table locale `jobs`.
*   **`app/Http/Controllers/OffreController.php`** & **`JobController.php`** :
    *   Le `JobController` gère la route `/api/jobs` : il sert à l'application les données "internes" (les offres préalablement importées, traitées et stockées), permettant un affichage très rapide.
    *   Le `OffreController` gère la route `/api/offres` : il permet de faire de la "Recherche instantanée (Live Search)" en interrogeant directement l'API externe sans bloquer sur des sauvegardes intermédiaires.

### 💻 Le Rendu et la Consommation (Frontend)
*   **Les "Stores" Pinia (`src/stores/jobs.js`)** :
    *   C'est le composant central de gestion d'état côté Vue. Il exporte des **Actions** asynchrones (ex: `fetchJobs()`) qui contactent l'API Laravel (`/api/jobs`) en passant simultanément tous les paramètres de filtre (recherche, lieu, type de contrat). Il stocke dans le dictionnaire global les résultats, supervise la pagination (`totalPages`, `currentPage`), les statistiques du dashboard, et gère automatiquement les erreurs de connexion éventuelles (`try...catch`).
*   **L'Interface Utilisateur (`src/views/JobsPage.vue`)** :
    *   La page exploite directement les données du store (`jobsStore.jobs`). Elle dispose d'une barre de recherche couplée à des listes déroulantes avancées (Type de contrat, Source). 
    *   Pour éviter de surcharger l'API à chaque lettre tapée, la fonction `handleSearch` utilise un **"Debounce"** (un timeout intelligent de 500ms qui attend que l'utilisateur ait fini de taper avant d'envoyer la requête réseau).
    *   **Le Rendu final** : Tant qu'il charge, il montre un repère visuel (`spinner animate-spin`). Une fois les offres récupérées, chaque objet est transmis individuellement au sous-composant `JobCard.vue` grâce à la directive itératrice `v-for`. Il existe aussi un état visuel très soigné (`Empty State`) dans le cas où aucun résultat ne remonte.

---

## 6. Fonctionnalités Avancées et Logique Métier

**🧠 1. Le Système de Recommandation & Matching :**
- C'est le cœur de "l'intelligence" du projet. 
- La logique met en parallèle le champ `skills` du profil utilisateur, et le champ `skills_required` de la base d'offre d'emploi. L'algorithme calcule un index de pertinence (%) et propulse les suggestions en haut de la liste du `Dashboard` avec affichage d'une statistique "Taux de matching".

**🔌 2. L'agrégation et l'intégration externe :**
- L'application récupère périodiquement via l'API, RapidAPI, ou d'autres organismes, les dernières offres réelles pour nourrir le site. Tout passe par l'implémentation de la logique métier sécurisée côté backend (`app/Services/`). 

---

## 7. Vision de Déploiement
*   **Versionage** : L'ensemble du code est tracké par Git (via des commits réguliers pour historiser le développement).
*   **Déploiement Backend** : Destiné à tourner sur un environnement tel qu'un VPS (Machine Linux) ou isolé sous Docker.
*   **Déploiement Frontend** : Suite à sa compilation (`npm run build`), le frontend devient un simple ensemble optimisé de fichiers HTML/JS/CSS statiques, prêt à être hébergé sur des Content Delivery Network ultra-rapides du type Netlify ou Vercel. 
