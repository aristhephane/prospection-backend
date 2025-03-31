#!/bin/bash

# Script pour nettoyer les configurations VirtualHost en double et établir une seule configuration unifiée

# Vérifier que l'utilisateur est root
if [ "$(id -u)" != "0" ]; then
   echo "Ce script doit être exécuté en tant que root" 1>&2
   exit 1
fi

echo "=== Nettoyage des configurations VirtualHost ==="

# Sauvegarde de la configuration unifiée actuelle qui semble fonctionner
if [ -f /etc/httpd/conf.d/prospection-unified.conf ]; then
  echo "Sauvegarde de la configuration unifiée existante..."
  cp /etc/httpd/conf.d/prospection-unified.conf /var/www/html/prospection-backend/prospection-unified.conf
fi

# Supprimer toutes les configurations en rapport avec prospection ou api
echo "Suppression de toutes les anciennes configurations..."
rm -f /etc/httpd/conf.d/api-prospection.conf*
rm -f /etc/httpd/conf.d/prospection.conf*
rm -f /etc/httpd/conf.d/api-vhost.conf*
rm -f /etc/httpd/conf.d/vhost.conf*

# Installer uniquement la configuration unifiée
echo "Installation de la configuration unifiée..."
cp /var/www/html/prospection-backend/prospection-unified.conf /etc/httpd/conf.d/prospection-unified.conf

# Redémarrer Apache
echo "Redémarrage d'Apache..."
systemctl restart httpd

# Vérifier la syntaxe de configuration Apache
if apachectl -t; then
  echo "✓ Configuration Apache valide"
else
  echo "✗ Erreur dans la configuration Apache"
  exit 1
fi

echo "=== Configuration terminée ==="
echo "Un seul fichier de configuration VirtualHost est maintenant utilisé:"
echo "  - /etc/httpd/conf.d/prospection-unified.conf"
echo ""
echo "Pour toute modification future, modifiez uniquement:"
echo "  - /var/www/html/prospection-backend/prospection-unified.conf"
echo "puis exécutez: sudo ./setup-vhost.sh"
