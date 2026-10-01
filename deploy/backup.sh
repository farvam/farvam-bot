#!/usr/bin/env bash
# Backup of everything panel-market stores (database + images + videos/files + fonts).
# Output: /opt/panel-market/backups/panel-market-<date>.tar.gz (keeps the newest $KEEP).
# Runs daily from /etc/cron.d/panel-market-backup and before every update.
set -euo pipefail
DIR=/opt/panel-market/backups
KEEP=${KEEP:-7}
mkdir -p "$DIR"; chmod 700 "$DIR"
if ! docker ps --format '{{.Names}}' | grep -qx panel-market; then echo "panel-market is not running; nothing to back up."; exit 0; fi
TS=$(date +%Y%m%d-%H%M%S)
OUT="$DIR/panel-market-$TS.tar.gz"
docker exec -u www-data panel-market php /opt/panel-market/snapshot.php /var/lib/panel-market/snapshot.sqlite >/dev/null
docker exec panel-market tar czf - -C /var/lib/panel-market snapshot.sqlite \
  -C /var/www/html/farvam images files assets/fonts > "$OUT.part"
docker exec panel-market rm -f /var/lib/panel-market/snapshot.sqlite
mv "$OUT.part" "$OUT"; chmod 600 "$OUT"
ls -1t "$DIR"/panel-market-*.tar.gz | tail -n +$((KEEP + 1)) | xargs -r rm -f
echo "backup: $OUT ($(du -h "$OUT" | cut -f1))"
