#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Vérification des privilèges root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Ce script doit être exécuté en tant que root${NC}"
    exit 1
fi

echo -e "${YELLOW}Configuration des clés JWT...${NC}"

# Définition des chemins
JWT_DIR="/var/www/html/prospection-backend/config/jwt"
PASSPHRASE_FILE="/tmp/jwt_passphrase.txt"
ENV_FILE="/var/www/html/prospection-backend/.env"

# Création du répertoire JWT s'il n'existe pas
mkdir -p ${JWT_DIR}

# Génération d'un passphrase aléatoire sécurisé
echo -e "${YELLOW}Génération du passphrase...${NC}"
openssl rand -base64 32 > ${PASSPHRASE_FILE}
PASSPHRASE=$(cat ${PASSPHRASE_FILE})

# Génération de la clé privée
echo -e "${YELLOW}Génération de la clé privée...${NC}"
openssl genpkey -out ${JWT_DIR}/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass file:${PASSPHRASE_FILE}

# Génération de la clé publique
echo -e "${YELLOW}Génération de la clé publique...${NC}"
openssl pkey -in ${JWT_DIR}/private.pem -out ${JWT_DIR}/public.pem -pubout -passin file:${PASSPHRASE_FILE}

# Configuration des permissions
echo -e "${YELLOW}Configuration des permissions...${NC}"
chown -R apache:apache ${JWT_DIR}
chmod 600 ${JWT_DIR}/private.pem
chmod 644 ${JWT_DIR}/public.pem

# Mise à jour du fichier .env
echo -e "${YELLOW}Mise à jour du fichier .env...${NC}"
# Sauvegarde du fichier .env
cp ${ENV_FILE} ${ENV_FILE}.bak

# Mise à jour des paramètres JWT dans .env
sed -i "s|JWT_SECRET_KEY=.*|JWT_SECRET_KEY=${JWT_DIR}/private.pem|" ${ENV_FILE}
sed -i "s|JWT_PUBLIC_KEY=.*|JWT_PUBLIC_KEY=${JWT_DIR}/public.pem|" ${ENV_FILE}
sed -i "s|JWT_PASSPHRASE=.*|JWT_PASSPHRASE=${PASSPHRASE}|" ${ENV_FILE}

# Nettoyage
echo -e "${YELLOW}Nettoyage des fichiers temporaires...${NC}"
rm ${PASSPHRASE_FILE}

# Vider le cache Symfony
echo -e "${YELLOW}Vidage du cache Symfony...${NC}"
cd /var/www/html/prospection-backend
php bin/console cache:clear

# Redémarrage du service httpd
echo -e "${YELLOW}Redémarrage du service httpd...${NC}"
systemctl restart httpd

echo -e "${GREEN}Configuration JWT terminée avec succès !${NC}"
echo -e "${YELLOW}Un backup du fichier .env a été créé : ${ENV_FILE}.bak${NC}"
echo -e "${YELLOW}Assurez-vous de sauvegarder le passphrase JWT de manière sécurisée.${NC}"
echo -e "${YELLOW}Passphrase JWT : ${PASSPHRASE}${NC}" 