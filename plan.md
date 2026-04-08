# 🚀 Job Intelligent - Laravel + Vue.js

## 🎯 Objectif
Créer une application web intelligente permettant :
- de gérer les utilisateurs
- de suivre leurs compétences
- de recommander des offres d’emploi
- d’intégrer des offres réelles (API externes)
- d’afficher des statistiques et recommandations

---

## 🏗️ Stack technique

### Backend
- Laravel 11
- Laravel Sanctum (auth API)
- PostgreSQL
- Laravel Scout (optionnel pour recherche)
- API REST

### Frontend
- Vue 3
- Vite
- Pinia (state management)
- Axios
- Tailwind CSS

---

## 📦 Modules Backend (Laravel)

### 1. Authentification
- Register
- Login
- Logout
- JWT / Sanctum token

### 2. Utilisateurs
- Profil utilisateur
- Mise à jour des compétences
- Expérience
- Préférences

### 3. Jobs
- CRUD des jobs (admin)
- Import depuis API externe
- Recherche et filtrage

### 4. Recommandation
- Matching user ↔ jobs
- Score de pertinence
- Suggestions personnalisées

### 5. Dashboard
- Statistiques utilisateur
- Nombre de jobs
- Taux de matching

---

## 🧩 Base de données

### Table users
- id
- email
- password
- full_name
- skills (JSON)
- experience (TEXT)
- preferences (JSON)

### Table jobs
- id
- title
- company
- description
- skills_required (JSON)
- location
- salary
- source (Indeed, LinkedIn, etc.)

---

## 🌐 Frontend Vue

### Pages
- Login / Register
- Dashboard
- Liste des jobs
- Détail job
- Profil utilisateur

### Composants
- Navbar
- JobCard
- DashboardCards
- Charts (statistiques)

---

## 🔌 Intégration API externes

- Indeed API
- LinkedIn API
- RapidAPI jobs

---

## 📊 Fonctionnalités avancées

- Système de recommandation intelligent
- Score de matching (%)
- Recherche avancée
- Notifications

---

## 🔐 Sécurité
- Hash password (bcrypt)
- Auth via Sanctum
- Validation des inputs

---

## 🚀 Déploiement
- Backend : VPS / Docker
- Frontend : Vercel / Netlify
- DB : PostgreSQL