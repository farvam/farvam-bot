#!/usr/bin/env bash
# Restore panel-market from a backup made by backup.sh:
#   bash /opt/panel-market/restore.sh /opt/panel-market/backups/panel-market-YYYYMMDD-HHMMSS.tar.gz
set -euo pipefail
FILE=$(readlink -f "${1:?usage: restore.sh <backup.tar.gz>}")
[ -f "$FILE" ] || { echo "not found: $FILE"; exit 1; }
cd /opt/panel-market
bash ./backup.sh || true            # safety copy of the current state first
docker compose stop panel-market
docker run --rm -u 0 \
  -v panel-market-db:/var/lib/panel-market \
  -v panel-market-images:/var/www/html/farvam/images \
  -v panel-market-files:/var/www/html/farvam/files \
  -v panel-market-fonts:/var/www/html/farvam/assets/fonts \
  -v "$FILE":/restore.tar.gz:ro --entrypoint sh panel-market:latest -c '
    set -e; mkdir /tmp/r; tar xzf /restore.tar.gz -C /tmp/r
    rm -f /var/lib/panel-market/panel-market.sqlite-wal /var/lib/panel-market/panel-market.sqlite-shm
    cp /tmp/r/snapshot.sqlite /var/lib/panel-market/panel-market.sqlite
    for d in images files; do cp -a /tmp/r/$d/. /var/www/html/farvam/$d/; done
    cp -a /tmp/r/assets/fonts/. /var/www/html/farvam/assets/fonts/
    chown -R www-data:www-data /var/lib/panel-market /var/www/html/farvam/images /var/www/html/farvam/files /var/www/html/farvam/assets/fonts'
docker compose start panel-market
echo "restored from $FILE"
