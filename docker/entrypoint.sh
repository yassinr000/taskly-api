#!/bin/sh
set -e
cd /var/www/html

# Certains hébergeurs imposent le port via la variable PORT.
if [ -n "$PORT" ]; then
  sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
  sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

php artisan package:discover --ansi
php artisan config:cache
php artisan route:cache

# Crée / met à jour les tables au démarrage (désactivable avec RUN_MIGRATIONS=false).
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force
fi

chown -R www-data:www-data storage bootstrap/cache
# Make sure only one Apache MPM is active when the container starts
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
a2enmod mpm_prefork > /dev/null 2>&1 || true
exec "$@"
