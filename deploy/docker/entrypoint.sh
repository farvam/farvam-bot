#!/bin/sh
# Add shipped images/files/fonts that are missing from the persistent volumes (new ones in an
# update). Never overwrites anything the owner uploaded or replaced.
set -e
W=/var/www/html/farvam
for d in images files assets/fonts; do
  cp -an /opt/panel-market/defaults/"$d"/. "$W/$d/" 2>/dev/null || true
done
# Code installed from the admin «به‌روزرسانی» button is kept in the database volume. Re-apply it
# unless this image is newer (a full install with deploy-panel-market.bat replaces it).
APP=/var/lib/panel-market/update/current
if [ -f "$APP/.applied" ]; then
  if [ "$(cat "$APP/.applied")" -gt "$(cat /opt/panel-market/built-at)" ]; then
    (cd "$APP" && tar cf - --exclude=./.applied .) | (cd "$W" && tar xf - --no-same-owner)
    chown -R www-data:www-data "$W"
    echo "panel-market: re-applied code from the admin update ($APP)"
  else
    rm -rf /var/lib/panel-market/update/current /var/lib/panel-market/update/prev
  fi
fi
chown www-data:www-data /var/lib/panel-market "$W/images" "$W/files" "$W/assets/fonts"
exec docker-php-entrypoint "$@"
