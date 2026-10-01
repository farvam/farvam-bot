#!/usr/bin/env bash
# Installs / updates the "panel-market" container (Farvam marketing site) and wires it into Caddy.
# Run on the server as root:  bash /opt/panel-market/server-install.sh
# Never touches the product container "farvam" or its data. Safe to run again for updates:
# everything the owner saves lives in the panel-market-* Docker volumes (SQLite DB + media).
set -euo pipefail

APP_DIR=/opt/panel-market
CADDYFILE=/etc/caddy/Caddyfile
SNIPPET=/etc/caddy/panel-market.caddy
OLD_SNIPPET=/etc/caddy/farvam-site.caddy     # from the first version of this installer
DOMAIN=farvamcertification.ir
PORT=8430

say()  { printf '\n\033[1;33m==> %s\033[0m\n' "$*"; }
ok()   { printf '\033[1;32m✔ %s\033[0m\n' "$*"; }
fail() { printf '\033[1;31m✘ %s\033[0m\n' "$*"; exit 1; }
exists() { docker ps -a --format '{{.Names}}' | grep -qx "$1"; }

[ "$(id -u)" = 0 ] || fail "Run as root."
cd "$APP_DIR"
chmod +x backup.sh restore.sh

say "1/6 Backup of the current panel-market data (if it is already installed)"
if exists panel-market; then bash ./backup.sh || fail "Backup failed; nothing was changed."; else ok "First install; nothing to back up"; fi

say "2/6 Building the image (panel-market)"
docker compose build
docker image prune -f >/dev/null 2>&1 || true

# Older installer created container "farvam-site": move its data into the new volumes once.
if exists farvam-site; then
  say "Moving data from the old container farvam-site to panel-market"
  docker stop farvam-site >/dev/null
  # texts, settings, leads, password: JSON files -> SQLite database
  docker volume create --label com.docker.compose.project=panel-market --label com.docker.compose.volume=db panel-market-db >/dev/null
  if docker volume inspect farvam-site_farvam_data >/dev/null 2>&1; then
    docker run --rm -u www-data -v farvam-site_farvam_data:/old:ro -v panel-market-db:/var/lib/panel-market \
      --entrypoint php panel-market:latest /opt/panel-market/import-json.php /old
  fi
  # images, videos/files, fonts: copied as they are
  for v in images files fonts; do
    old="farvam-site_farvam_$v"; new="panel-market-$v"
    if docker volume inspect "$old" >/dev/null 2>&1 && ! docker volume inspect "$new" >/dev/null 2>&1; then
      docker volume create --label com.docker.compose.project=panel-market --label com.docker.compose.volume="$v" "$new" >/dev/null
      docker run --rm -u 0 -v "$old":/from:ro -v "$new":/to --entrypoint sh panel-market:latest -c 'cp -a /from/. /to/'
      ok "$old -> $new"
    fi
  done
  docker rm farvam-site >/dev/null
  ok "Old container removed (its volumes are kept as a safety copy)"
fi

say "3/6 Starting the container"
docker compose up -d --remove-orphans
for i in $(seq 1 30); do
  code=$(curl -s -o /dev/null -w '%{http_code}' "http://127.0.0.1:$PORT/farvam/" || true)
  [ "$code" = 200 ] && break; sleep 2
done
[ "$code" = 200 ] || { docker logs --tail 40 panel-market; fail "Container did not answer (HTTP $code)."; }
db=$(docker exec -u www-data panel-market php -r 'require "/var/www/html/farvam/lib.php"; echo db() ? "ok" : "off";')
[ "$db" = ok ] || fail "SQLite database is not available inside the container."
ok "panel-market answers on 127.0.0.1:$PORT, database OK"

say "4/6 Connecting Caddy (backup first, automatic rollback on error)"
cp "$APP_DIR/panel-market.caddy" "$SNIPPET"
if grep -q "import $SNIPPET" "$CADDYFILE" && ! grep -q "import $OLD_SNIPPET" "$CADDYFILE"; then
  ok "Caddyfile already imports the panel-market snippet"
else
  BACKUP="$CADDYFILE.bak-panel-market-$(date +%Y%m%d-%H%M%S)"
  cp "$CADDYFILE" "$BACKUP"
  rc=0
  python3 - "$CADDYFILE" "$DOMAIN" "$SNIPPET" "$OLD_SNIPPET" <<'PY' || rc=$?
import re, sys
path, domain, snippet, old = sys.argv[1:5]
lines = [l for l in open(path, encoding="utf-8").read().split("\n") if l.strip() != f"import {old}"]
done = any(l.strip() == f"import {snippet}" for l in lines)
out = []
for ln in lines:
    out.append(ln)
    s = ln.strip()
    if not done and s.endswith("{") and not s.startswith("#"):
        hosts = [h.strip() for h in s[:-1].replace(",", " ").split()]
        if any(re.sub(r"^https?://", "", h).split(":")[0] == domain for h in hosts):
            out.append(re.match(r"\s*", ln).group(0) + "\t" + f"import {snippet}")
            done = True
if not done:
    sys.exit(3)
open(path, "w", encoding="utf-8").write("\n".join(out))
PY
  [ $rc = 0 ] || { cp "$BACKUP" "$CADDYFILE"; fail "No site block for $DOMAIN found in $CADDYFILE. Nothing changed. Send this file's contents to Claude."; }
  if ! caddy validate --config "$CADDYFILE" --adapter caddyfile >/tmp/caddy-validate.log 2>&1; then
    cp "$BACKUP" "$CADDYFILE"; tail -15 /tmp/caddy-validate.log
    fail "Caddy rejected the new config; the original Caddyfile was restored."
  fi
  ok "Caddyfile updated (backup: $BACKUP)"
fi

say "5/6 Reloading Caddy"
systemctl reload caddy || { [ -n "${BACKUP:-}" ] && cp "$BACKUP" "$CADDYFILE" && systemctl reload caddy; fail "Caddy reload failed; original config restored."; }
rm -f "$OLD_SNIPPET"
ok "Caddy reloaded"

say "6/6 Daily backup (03:30) and public checks"
printf '30 3 * * * root /bin/bash %s/backup.sh > /var/log/panel-market-backup.log 2>&1\n' "$APP_DIR" > /etc/cron.d/panel-market-backup
chmod 644 /etc/cron.d/panel-market-backup
ok "Daily backup -> $APP_DIR/backups (newest 7 kept)"
sleep 2
for path in /farvam/ /farvam/for/bonak /farvam/blog/ /farvam/present.php /farvam/sitemap.xml /farvam/admin/; do
  code=$(curl -s -o /dev/null -w '%{http_code}' --resolve "$DOMAIN:443:127.0.0.1" "https://$DOMAIN$path" || true)
  printf '  %-26s %s\n' "$path" "$code"
done
blocked=$(curl -s -o /dev/null -w '%{http_code}' --resolve "$DOMAIN:443:127.0.0.1" "https://$DOMAIN/farvam/data/content.json" || true)
printf '  %-26s %s (must be 403)\n' "/farvam/data/content.json" "$blocked"
panel=$(curl -s -o /dev/null -w '%{http_code}' --resolve "$DOMAIN:443:127.0.0.1" "https://$DOMAIN/" || true)
printf '  %-26s %s (your existing panel, unchanged)\n' "/" "$panel"
docker ps --format '  {{.Names}}\t{{.Status}}' | grep -E '^  (farvam|panel-market)\b' || true

echo
ok "Done.  Site:  https://$DOMAIN/farvam/     Admin:  https://$DOMAIN/farvam/admin/"
echo "   Logs: docker logs -f panel-market    Backup now: bash $APP_DIR/backup.sh"
echo "   Restore: bash $APP_DIR/restore.sh $APP_DIR/backups/<file>.tar.gz"
