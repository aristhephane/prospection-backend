#!/bin/bash
cd /var/www/html/prospection-backend

echo "Nettoyage du cache Symfony..."
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug

echo "Correction des permissions..."
sudo chmod -R 777 var/cache var/log
sudo chown -R apache:apache var/cache var/log

echo "Cache vidé et réchauffé avec succès!"
