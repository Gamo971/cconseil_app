#!/bin/sh
set -e
# Montage Docker (surtout bind mount) : garantir l’écriture pour PHP-FPM (www-data)
if [ -d /var/www/html/storage ]; then
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
    chmod -R ug+rwX /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
fi
exec "$@"
