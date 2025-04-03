#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}Suppression de la configuration JWT et activation de l'authentification par session...${NC}"

# Vérification que le script est exécuté en tant que root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Ce script doit être exécuté en tant que root${NC}"
    exit 1
fi

# Chemin du projet
PROJECT_DIR="/var/www/html/prospection-backend"
JWT_DIR="${PROJECT_DIR}/config/jwt"

# 1. Supprimer les clés JWT
echo -e "${YELLOW}Suppression des clés JWT...${NC}"
rm -rf ${JWT_DIR}
mkdir -p ${JWT_DIR}
chown -R apache:apache ${JWT_DIR}
chmod 755 ${JWT_DIR}

# 2. Vider le cache Symfony
echo -e "${YELLOW}Vidage du cache Symfony...${NC}"
cd ${PROJECT_DIR}
php bin/console cache:clear

# 3. Redémarrer Apache
echo -e "${YELLOW}Redémarrage du serveur web...${NC}"
systemctl restart httpd

echo -e "${GREEN}Migration vers l'authentification par session terminée avec succès!${NC}"
echo -e "${YELLOW}Les points d'entrée API suivants sont maintenant disponibles:${NC}"
echo -e "${YELLOW}- POST /api/auth/login: Authentification${NC}"
echo -e "${YELLOW}- GET /api/auth/status: Vérification de l'état d'authentification${NC}"
echo -e "${YELLOW}- POST /api/auth/logout: Déconnexion${NC}" 