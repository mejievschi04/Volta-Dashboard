#!/bin/bash
# Actualizează aplicația pe VPS după git push de pe PC.
# Rulează pe server: bash update-vps.sh
# Cale tipică: cd /var/www/volta-dashboard && bash update-vps.sh

set -e

APP_DIR="${APP_DIR:-$(pwd)}"
cd "$APP_DIR"

if [ ! -f artisan ]; then
  echo "Eroare: rulează din rădăcina proiectului Laravel (ex: /var/www/volta-dashboard)"
  exit 1
fi

echo "==> Director: $APP_DIR"
echo "==> git pull..."
git pull origin main

echo "==> Aplicare migrări..."
php artisan migrate --force

echo "==> Optimizare Laravel pentru producție..."
php artisan optimize:clear
php artisan optimize

if command -v systemctl >/dev/null 2>&1; then
  for svc in php8.3-fpm php8.2-fpm php-fpm; do
    if systemctl is-active --quiet "$svc" 2>/dev/null; then
      echo "==> Reload $svc (OPcache)..."
      sudo systemctl reload "$svc" || true
      break
    fi
  done
fi

echo ""
echo "Gata. Verifică /rapoarte în browser cu Ctrl+Shift+R."
echo "În View Source caută: data-rapoarte-ui=\"luni-perioada-v3\""
echo "(dacă lipsește, fă git push de pe PC cu ultimele modificări, apoi rulează din nou acest script)"
