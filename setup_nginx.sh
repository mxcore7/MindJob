#!/bin/bash

SERVER_USER="root"
SERVER_IP="162.35.160.164"

echo "⚙️ Configuration de Nginx sur le VPS..."

ssh -t ${SERVER_USER}@${SERVER_IP} "
    set -e
    
    echo '⚙️ Vérification et installation de Nginx...'
    export DEBIAN_FRONTEND=noninteractive
    apt-get update -yqq || true
    apt-get install -yqq nginx || true

    mkdir -p /etc/nginx/sites-available
    mkdir -p /etc/nginx/sites-enabled

    echo '📝 Création du fichier de configuration Nginx...'
    cat << 'EOF' > /etc/nginx/sites-available/mindjob
# 1. Serveur Interne pour le Backend Laravel (Port 8000)
server {
    listen 8000;
    server_name localhost;
    root /var/www/mindjob/backend/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }
}

# 2. Serveur Public Principal pour le Frontend (Port 80)
server {
    listen 80;
    server_name 162.35.160.164; # Remplacez par votre domaine plus tard

    root /var/www/mindjob/frontend/dist;
    index index.html;

    # Routes du Frontend
    location / {
        try_files \$uri \$uri/ /index.html;
    }

    # Redirection des requêtes API vers le Backend Laravel
    location /api {
        proxy_pass http://127.0.0.1:8000/api;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
    }
}
EOF

    echo '🔗 Activation de la configuration...'
    ln -sf /etc/nginx/sites-available/mindjob /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default

    echo '🔧 Ajustement des permissions des dossiers Laravel...'
    chown -R www-data:www-data /var/www/mindjob/backend/storage /var/www/mindjob/backend/bootstrap/cache /var/www/mindjob/backend/database || true
    chmod -R 775 /var/www/mindjob/backend/storage /var/www/mindjob/backend/bootstrap/cache /var/www/mindjob/backend/database || true

    echo '🛑 Arrêt de Apache2 au cas où il bloquerait le port 80...'
    systemctl disable apache2 --now 2>/dev/null || true

    echo '🔍 Vérification de la configuration Nginx...'
    nginx -t

    echo '🔄 Redémarrage de Nginx et PHP-FPM...'
    systemctl restart nginx
    systemctl restart php8.4-fpm

    echo '✅ Nginx configuré ! Votre site est maintenant en ligne !'
"

echo "🌍 Vous pouvez y accéder à l'adresse : http://${SERVER_IP}"
