#!/bin/sh
# Add shipped images/files/fonts that are missing from the persistent volumes (new ones in an
# update). Never overwrites anything the owner uploaded or replaced.
set -e
W=/var/www/html/farvam
for d in images files assets/fonts; do
  cp -an /opt/panel-market/defaults/"$d"/. "$W/$d/" 2>/dev/null || true
done
chown www-data:www-data /var/lib/panel-market "$W/images" "$W/files" "$W/assets/fonts"
exec docker-php-entrypoint "$@"
