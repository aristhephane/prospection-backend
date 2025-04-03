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

echo -e "${YELLOW}Correction des permissions des clés JWT...${NC}"

# Définition des chemins
JWT_DIR="/var/www/html/prospection-backend/config/jwt"
PASSPHRASE="d6128fe74303eefb28994e3efe35e78b970e451baa28e61edc6594d4cc33a137"

# Vérification de l'existence des clés
if [ ! -f "${JWT_DIR}/private.pem" ] || [ ! -f "${JWT_DIR}/public.pem" ]; then
    echo -e "${RED}Les clés JWT n'existent pas. Génération de nouvelles clés...${NC}"
    
    # Création du répertoire JWT s'il n'existe pas
    mkdir -p ${JWT_DIR}
    
    # Créer un fichier temporaire pour le passphrase
    PASSPHRASE_FILE="/tmp/jwt_passphrase.txt"
    echo "${PASSPHRASE}" > ${PASSPHRASE_FILE}
    
    # Génération de la clé privée
    echo -e "${YELLOW}Génération de la clé privée...${NC}"
    openssl genpkey -out ${JWT_DIR}/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass file:${PASSPHRASE_FILE}
    
    # Génération de la clé publique
    echo -e "${YELLOW}Génération de la clé publique...${NC}"
    openssl pkey -in ${JWT_DIR}/private.pem -out ${JWT_DIR}/public.pem -pubout -passin file:${PASSPHRASE_FILE}
    
    # Nettoyage
    rm ${PASSPHRASE_FILE}
else
    echo -e "${YELLOW}Les clés JWT existent déjà.${NC}"
fi

# Configuration des permissions
echo -e "${YELLOW}Configuration des permissions...${NC}"
chown -R apache:apache ${JWT_DIR}
chmod 640 ${JWT_DIR}/private.pem
chmod 644 ${JWT_DIR}/public.pem

# Vidage du cache Symfony
echo -e "${YELLOW}Vidage du cache Symfony...${NC}"
cd /var/www/html/prospection-backend
php bin/console cache:clear

echo -e "${GREEN}Correction des permissions terminée avec succès !${NC}"
echo -e "${YELLOW}Assurez-vous que le passphrase JWT dans .env et .env.local est: ${PASSPHRASE}${NC}" 