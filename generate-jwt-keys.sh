#!/bin/bash

# Ce script génère de nouvelles clés JWT pour le backend

# Définir le répertoire de configuration JWT
JWT_DIR="/var/www/html/prospection-backend/config/jwt"

# Créer le répertoire s'il n'existe pas
mkdir -p $JWT_DIR

# Définir une phrase secrète simple
PASSPHRASE="password123"

# Générer la clé privée
openssl genpkey -out $JWT_DIR/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:$PASSPHRASE

# Générer la clé publique à partir de la clé privée
openssl pkey -in $JWT_DIR/private.pem -out $JWT_DIR/public.pem -pubout -passin pass:$PASSPHRASE

# Définir les permissions appropriées
chmod 644 $JWT_DIR/public.pem
chmod 600 $JWT_DIR/private.pem

echo "Clés JWT générées avec succès!"
echo "Phrase secrète: $PASSPHRASE"
echo "N'oubliez pas de mettre à jour la variable JWT_PASSPHRASE dans le fichier .env"
