#!/bin/sh
# Al montar carpetas del equipo anfitrión, los permisos vienen de ahí.
# Esto asegura que Apache pueda escribir la base de datos y las subidas.
set -e

for dir in /var/www/html/data /var/www/html/images/subidas; do
    mkdir -p "$dir"
    chown -R www-data:www-data "$dir" 2>/dev/null || true
done

exec "$@"
