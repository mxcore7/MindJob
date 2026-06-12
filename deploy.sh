#!/bin/bash

# ==============================================================================
# Script de déploiement pour le projet MindJob
# ==============================================================================

# --- Configuration ---
# Remplacez "root" par votre nom d'utilisateur si nécessaire
SERVER_USER="root"
SERVER_IP="162.35.160.164"
# Remplacez par le chemin absolu de votre projet sur le VPS
PROJECT_PATH="/var/www/mindjob" 
# Branche git à déployer
GIT_BRANCH="main"

echo "🚀 Début du déploiement vers ${SERVER_USER}@${SERVER_IP}..."

# Connexion SSH et exécution des commandes sur le VPS
# L'option -t force l'allocation d'un terminal (évite les bugs de fermeture sur Git Bash)
ssh -t ${SERVER_USER}@${SERVER_IP} "
    set -e # Arrête le script en cas d'erreur

    echo '🛠️  Vérification et installation des dépendances système (Ubuntu/Debian)...'
    export DEBIAN_FRONTEND=noninteractive
    
    # Mise à jour silencieuse
    apt-get update -yqq || true
    apt-get install -yqq curl git unzip wget software-properties-common || true

    # Installation de PHP 8.4 (requis par Symfony 8+)
    if ! php -v 2>/dev/null | grep -q "PHP 8.4"; then
        echo '⚙️  Installation de PHP 8.4 et ses extensions...'
        apt-get install -yqq software-properties-common
        add-apt-repository ppa:ondrej/php -y || true
        apt-get update -yqq
        apt-get install -yqq php8.4 php8.4-cli php8.4-fpm php8.4-pgsql php8.4-sqlite3 php8.4-xml php8.4-curl php8.4-mbstring php8.4-zip php8.4-redis || true
        update-alternatives --set php /usr/bin/php8.4 || true
    fi

    # Installation de Composer
    if ! command -v composer > /dev/null; then
        echo '📦 Installation de Composer...'
        curl -sS https://getcomposer.org/installer | php
        mv composer.phar /usr/local/bin/composer
    fi

    # Installation de Node.js et NPM
    if ! command -v npm > /dev/null; then
        echo '🟢 Installation de Node.js...'
        curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
        apt-get install -yqq nodejs || true
    fi

    # Vérifier si le dossier existe, sinon cloner le projet
    if [ ! -d "${PROJECT_PATH}" ]; then
        echo '📦 Premier déploiement : Clonage du projet depuis GitHub...'
        mkdir -p /var/www
        git clone https://github.com/mxcore7/MindJob "${PROJECT_PATH}"
        cd "${PROJECT_PATH}"
        git checkout ${GIT_BRANCH}
    else
        echo '📂 Navigation vers le dossier du projet...'
        cd "${PROJECT_PATH}"
        echo '📥 Récupération des dernières mises à jour depuis Git...'
        git fetch origin
        git reset --hard origin/${GIT_BRANCH}
    fi

    # ==========================================
    # 1. Déploiement Backend (Laravel)
    # ==========================================
    echo '⚙️  Mise à jour du Backend (Laravel)...'
    cd backend

    # Configuration du fichier .env
    if [ ! -f .env ]; then
        echo '📝 Création du fichier .env à partir de .env.example...'
        cp .env.example .env
        php artisan key:generate
    fi

    # Installation des dépendances
    echo '📦 Installation des dépendances Composer...'
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

    # Création de la base SQLite si Laravel 11 l'utilise par défaut et qu'elle n'existe pas
    if grep -q "DB_CONNECTION=sqlite" .env && [ ! -f database/database.sqlite ]; then
        touch database/database.sqlite
    fi

    # Exécution des migrations de base de données
    echo '🗄️  Exécution des migrations...'
    php artisan migrate --force

    # Nettoyage et mise en cache pour la production
    echo '🧹 Optimisation de Laravel...'
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # ==========================================
    # 2. Déploiement Frontend
    # ==========================================
    echo '⚙️  Mise à jour du Frontend...'
    cd ../frontend

    # Installation des dépendances Node.js
    echo '📦 Installation des paquets npm...'
    npm install

    # Build de l'application pour la production
    echo '🏗️  Build du projet Frontend...'
    npm run build

    # ==========================================
    # 3. Finalisation
    # ==========================================
    echo '🔄 Ajustement des permissions (optionnel)...'
    # chown -R www-data:www-data ../backend/storage ../backend/bootstrap/cache

    echo '✅ Déploiement terminé avec succès sur le VPS !'
"

echo "🎉 Le script local a terminé son exécution."
