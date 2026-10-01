#!/usr/bin/env bash
# Installs / updates the Farvam marketing site container and wires it into Caddy.
# Run on the server as root:  bash /opt/farvam-site/server-install.sh
# Safe to run again for updates: owner data (data/, images/, files/, fonts) lives in Docker volumes.
set -euo pipefail

APP_DIR=/opt/farvam-site
CADDYFILE=/etc/caddy/Caddyfile
SNIPPET=/etc/caddy/farvam-site.caddy
DOMAIN=farvamcertification.ir
PORT=8430

say()  { printf '\n\033[1;33m==> %s\033[0m\n' "$*"; }
ok()   { printf '\033[1;32m✔ %s\033[0m\n' "$*"; }
fail() { printf '\033[1;31m✘ %s\033[0m\n' "$*"; exit 1; }

[ "$(id -u)" = 0 ] || fail "Run as root."
cd "$APP_DIR"

say "1/5 Building and starting the container (farvam-site)"
docker compose up -d --build
docker image prune -f >/dev/null 2>&1 || true

say "2/5 Waiting for the site inside the container"
for i in $(seq 1 30); do
  code=$(curl -s -o /dev/null -w '%{http_code}' "http://127.0.0.1:$PORT/farvam/" || true)
  [ "$code" = 200 ] && break; sleep 2
done
[ "$code" = 200 ] || { docker logs --tail 40 farvam-site; fail "Container did not answer (HTTP $code)."; }
ok "Container answers on 127.0.0.1:$PORT (HTTP 200)"

say "3/5 Connecting Caddy (backup first, automatic rollback on error)"
cp "$APP_DIR/farvam-site.caddy" "$SNIPPET"
if grep -q "import $SNIPPET" "$CADDYFILE"; then
  ok "Caddyfile already imports the Farvam snippet"
else
  BACKUP="$CADDYFILE.bak-farvam-$(date +%Y%m%d-%H%M%S)"
  cp "$CADDYFILE" "$BACKUP"
  # insert the import as the first line inside the site block of $DOMAIN
  rc=0
  python3 - "$CADDYFILE" "$DOMAIN" "$SNIPPET" <<'PY' || rc=$?
import re, sys
path, domain, snippet = sys.argv[1:4]
lines = open(path, encoding="utf-8").read().split("\n")
out, done = [], False
for ln in lines:
    out.append(ln)
    s = ln.strip()
    if not done and s.endswith("{") and not s.startswith("#"):
        hosts = [h.strip().rstrip(",") for h in s[:-1].replace(",", " ").split()]
        if any(re.sub(r"^https?://", "", h).split(":")[0] == domain for h in hosts):
            indent = re.match(r"\s*", ln).group(0) + "\t"
            out.append(f"{indent}import {snippet}")
            done = True
if not done:
    sys.exit(3)
open(path, "w", encoding="utf-8").write("\n".join(out))
PY
  [ $rc = 0 ] || { cp "$BACKUP" "$CADDYFILE"; fail "No site block for $DOMAIN found in $CADDYFILE. Nothing changed. Send this file's contents to Claude."; }
  if ! caddy validate --config "$CADDYFILE" --adapter caddyfile >/tmp/caddy-validate.log 2>&1; then
    cp "$BACKUP" "$CADDYFILE"; cat /tmp/caddy-validate.log | tail -15
    fail "Caddy rejected the new config; the original Caddyfile was restored."
  fi
  ok "Caddyfile updated (backup: $BACKUP)"
fi

say "4/5 Reloading Caddy"
systemctl reload caddy || { [ -n "${BACKUP:-}" ] && cp "$BACKUP" "$CADDYFILE" && systemctl reload caddy; fail "Caddy reload failed; original config restored."; }
ok "Caddy reloaded"

say "5/5 Checking the public site"
sleep 2
for path in /farvam/ /farvam/for/bonak /farvam/blog/ /farvam/present.php /farvam/sitemap.xml /farvam/admin/; do
  code=$(curl -s -o /dev/null -w '%{http_code}' --resolve "$DOMAIN:443:127.0.0.1" "https://$DOMAIN$path" || true)
  printf '  %-26s %s\n' "$path" "$code"
done
blocked=$(curl -s -o /dev/null -w '%{http_code}' --resolve "$DOMAIN:443:127.0.0.1" "https://$DOMAIN/farvam/data/content.json" || true)
printf '  %-26s %s (must be 403)\n' "/farvam/data/content.json" "$blocked"
panel=$(curl -s -o /dev/null -w '%{http_code}' --resolve "$DOMAIN:443:127.0.0.1" "https://$DOMAIN/" || true)
printf '  %-26s %s (your existing panel, unchanged)\n' "/" "$panel"

echo
ok "Done.  Site:  https://$DOMAIN/farvam/     Admin:  https://$DOMAIN/farvam/admin/"
echo "   Logs: docker logs -f farvam-site     Stop: cd $APP_DIR && docker compose down"
