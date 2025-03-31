#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}Début de la configuration du système...${NC}"

# Vérification des privilèges root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Ce script doit être exécuté en tant que root${NC}"
    exit 1
fi

# Installation des dépendances nécessaires
echo -e "${YELLOW}Installation des dépendances...${NC}"
dnf update -y
dnf install -y httpd mod_ssl certbot python3-certbot-apache epel-release

# Démarrage et activation de httpd
echo -e "${YELLOW}Configuration du service HTTPD...${NC}"
systemctl enable httpd
systemctl start httpd

# Configuration du vhost frontend
echo -e "${YELLOW}Configuration du vhost frontend...${NC}"
cat > /etc/httpd/conf.d/frontend.conf << 'EOL'
<VirtualHost *:80>
    ServerName upjv-prospection-vps.amourfoot.fr
    DocumentRoot /var/www/html/prospection-frontend/build

    <Directory /var/www/html/prospection-frontend/build>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
            RewriteRule ^index\.html$ - [L]
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule . /index.html [L]
        </IfModule>
    </Directory>

    ErrorLog logs/frontend-error.log
    CustomLog logs/frontend-access.log combined
    LogLevel warn
</VirtualHost>
EOL

# Configuration du vhost backend
echo -e "${YELLOW}Configuration du vhost backend...${NC}"
cat > /etc/httpd/conf.d/backend.conf << 'EOL'
<VirtualHost *:80>
    ServerName api.upjv-prospection-vps.amourfoot.fr
    DocumentRoot /var/www/html/prospection-backend/public

    <Directory /var/www/html/prospection-backend/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ index.php [QSA,L]
        </IfModule>
    </Directory>

    <IfModule mod_headers.c>
        SetEnvIf Origin "^https?://upjv-prospection-vps\.amourfoot\.fr$" ALLOWED_ORIGIN=$0
        Header always set Access-Control-Allow-Origin "%{ALLOWED_ORIGIN}e" env=ALLOWED_ORIGIN
        Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
        Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"
        Header always set Access-Control-Expose-Headers "Authorization"
        Header always set Access-Control-Allow-Credentials "true"
        Header always set Access-Control-Max-Age "3600"
        
        RewriteEngine On
        RewriteCond %{REQUEST_METHOD} OPTIONS
        RewriteRule ^(.*)$ $1 [R=200,L]
    </IfModule>

    ErrorLog logs/backend-error.log
    CustomLog logs/backend-access.log combined
    LogLevel warn
</VirtualHost>
EOL

# Configuration des modules Apache
echo -e "${YELLOW}Configuration des modules Apache...${NC}"
# Les modules sont déjà activés par défaut dans CentOS

# Nettoyage des anciennes configurations
echo -e "${YELLOW}Nettoyage des anciennes configurations...${NC}"
rm -f /etc/httpd/conf.d/prospection-unified.conf
rm -f /etc/httpd/conf.d/api-vhost.conf
rm -f /etc/httpd/conf.d/vhost.conf

# Configuration des permissions SELinux
echo -e "${YELLOW}Configuration des permissions SELinux...${NC}"
semanage fcontext -a -t httpd_sys_content_t "/var/www/html/prospection-frontend/build(/.*)?"
semanage fcontext -a -t httpd_sys_content_t "/var/www/html/prospection-backend/public(/.*)?"
restorecon -Rv /var/www/html/prospection-frontend/build
restorecon -Rv /var/www/html/prospection-backend/public

# Configuration des permissions
echo -e "${YELLOW}Configuration des permissions...${NC}"
chown -R apache:apache /var/www/html/prospection-frontend/build
chown -R apache:apache /var/www/html/prospection-backend/public
chmod -R 755 /var/www/html/prospection-frontend/build
chmod -R 755 /var/www/html/prospection-backend/public

# Installation des certificats SSL
echo -e "${YELLOW}Installation des certificats SSL...${NC}"
certbot --apache -d upjv-prospection-vps.amourfoot.fr -d api.upjv-prospection-vps.amourfoot.fr --non-interactive --agree-tos --email admin@amourfoot.fr

# Redémarrage d'Apache
echo -e "${YELLOW}Redémarrage d'Apache...${NC}"
systemctl restart httpd

# Vérification de la configuration
echo -e "${YELLOW}Vérification de la configuration Apache...${NC}"
httpd -t

# Configuration du pare-feu
echo -e "${YELLOW}Configuration du pare-feu...${NC}"
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --reload

echo -e "${GREEN}Configuration terminée avec succès !${NC}"
echo -e "${YELLOW}Veuillez vérifier que les domaines suivants sont accessibles :${NC}"
echo -e "Frontend : https://upjv-prospection-vps.amourfoot.fr"
echo -e "Backend : https://api.upjv-prospection-vps.amourfoot.fr" 