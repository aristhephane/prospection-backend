#!/bin/bash

# Script pour configurer le sous-domaine API

# Vérifier que l'utilisateur est root
if [ "$(id -u)" != "0" ]; then
   echo "Ce script doit être exécuté en tant que root" 1>&2
   exit 1
fi

# Copier les fichiers de configuration dans les emplacements appropriés
cp api-vhost.conf /etc/httpd/conf.d/api-prospection.conf
cp vhost.conf /etc/httpd/conf.d/prospection.conf

# Redémarrer Apache
systemctl restart httpd

# Vérifier la syntaxe de configuration Apache
apachectl -t

# Afficher un message de confirmation
echo "Configuration VirtualHost terminée."
echo "N'oubliez pas de créer l'entrée DNS suivante :"
echo "  Type: A"
echo "  Nom: api.upjv-prospection-vps.amourfoot.fr"
echo "  Valeur: [même adresse IP que upjv-prospection-vps.amourfoot.fr]"
echo ""
echo "Testez ensuite avec: curl -I http://api.upjv-prospection-vps.amourfoot.fr/auth-status" 