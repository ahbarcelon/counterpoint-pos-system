#!/bin/sh
set -eu

port="${PORT:-8080}"
case "$port" in
    ''|*[!0-9]*) echo 'PORT must be a numeric TCP port' >&2; exit 1 ;;
esac

sed -i "s/^Listen 80$/Listen ${port}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf

# Railway attaches volumes at runtime, after Docker build-time permissions are set.
mkdir -p /var/www/html/public/uploads/products /var/www/html/public/uploads/avatars
chown www-data:www-data /var/www/html/public/uploads /var/www/html/public/uploads/products /var/www/html/public/uploads/avatars

exec "$@"
