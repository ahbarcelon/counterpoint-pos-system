#!/bin/sh
set -eu

port="${PORT:-8080}"
case "$port" in
    ''|*[!0-9]*) echo 'PORT must be a numeric TCP port' >&2; exit 1 ;;
esac

sed -i "s/^Listen 80$/Listen ${port}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf

# Apache must load exactly one MPM. Normalize it at runtime because the
# distro package scripts can re-enable event/worker after the image is built.
a2dismod -q mpm_event mpm_worker 2>/dev/null || true
a2enmod -q mpm_prefork

# Railway attaches volumes at runtime, after Docker build-time permissions are set.
mkdir -p /var/www/html/public/uploads/products /var/www/html/public/uploads/avatars
chown www-data:www-data /var/www/html/public/uploads /var/www/html/public/uploads/products /var/www/html/public/uploads/avatars

exec "$@"
