#!/bin/bash

# Script pour configurer le VirtualHost (configuration unifiée)

# Vérifier que l'utilisateur est root
if [ "$(id -u)" != "0" ]; then
   echo "Ce script doit être exécuté en tant que root" 1>&2
   exit 1
fi

# Nettoyer toutes les anciennes configurations
echo "Nettoyage des configurations VirtualHost..."
rm -f /etc/httpd/conf.d/api-prospection.conf*
rm -f /etc/httpd/conf.d/prospection.conf*
rm -f /etc/httpd/conf.d/api-vhost.conf*

# Copier UNIQUEMENT la configuration unifiée
echo "Installation de la configuration unifiée..."
cp prospection-unified.conf /etc/httpd/conf.d/prospection-unified.conf

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

# Afficher un message de confirmation
echo "Configuration VirtualHost terminée."
echo "N'oubliez pas de vérifier l'entrée DNS suivante :"
echo "  Type: A"
echo "  Nom: api.upjv-prospection-vps.amourfoot.fr"
echo "  Valeur: [même adresse IP que upjv-prospection-vps.amourfoot.fr]"
echo ""
echo "Testez avec: curl -I -H 'Origin: http://upjv-prospection-vps.amourfoot.fr:3000' http://api.upjv-prospection-vps.amourfoot.fr/auth-test"
echo "Diagnostic complet: sudo ./api-test.sh"