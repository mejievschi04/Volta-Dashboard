#!/bin/bash

# Script de diagnosticare pentru sincronizare GA4 pe VPS
# Rulează din rădăcina proiectului: bash check-ga-sync-vps.sh

echo "=========================================="
echo "Diagnosticare Sincronizare GA4"
echo "=========================================="
echo ""

ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT" || exit 1
if [ ! -f "artisan" ]; then
    echo "❌ Rulează acest script din directorul rădăcină al proiectului Laravel."
    exit 1
fi

# 1. Verifică fișierul de credențiale
echo "1. Verifică fișierul de credențiale..."
if [ -f "storage/app/google-analytics/service-account-credentials.json" ]; then
    echo "   ✅ Fișierul există"
    ls -lh storage/app/google-analytics/service-account-credentials.json
    
    # Verifică permisiunile
    PERMS=$(stat -c "%a" storage/app/google-analytics/service-account-credentials.json 2>/dev/null || stat -f "%OLp" storage/app/google-analytics/service-account-credentials.json 2>/dev/null)
    if [ "$PERMS" = "600" ]; then
        echo "   ✅ Permisiuni corecte (600)"
    else
        echo "   ⚠️  Permisiuni: $PERMS (ar trebui să fie 600)"
        echo "   Rulează: chmod 600 storage/app/google-analytics/service-account-credentials.json"
    fi
    
    # Verifică JSON-ul
    if command -v python3 &> /dev/null; then
        if python3 -m json.tool storage/app/google-analytics/service-account-credentials.json > /dev/null 2>&1; then
            echo "   ✅ JSON valid"
        else
            echo "   ❌ JSON invalid!"
        fi
    fi
else
    echo "   ❌ Fișierul NU există!"
    echo "   Cale așteptată: storage/app/google-analytics/service-account-credentials.json"
fi

echo ""

# 2. Verifică configurația .env
echo "2. Verifică configurația .env..."
if grep -q "GA_PROPERTY_ID" .env; then
    echo "   ✅ GA_PROPERTY_ID este setat:"
    grep "GA_PROPERTY_ID" .env
else
    echo "   ❌ GA_PROPERTY_ID nu este setat!"
    echo "   Adaugă în .env: GA_PROPERTY_ID=281678807"
fi

echo ""

# 3. Verifică PHP extensions
echo "3. Verifică PHP extensions..."
php -m | grep -E "openssl|curl|json" > /dev/null
if [ $? -eq 0 ]; then
    echo "   ✅ Extensiile necesare sunt instalate:"
    php -m | grep -E "openssl|curl|json"
else
    echo "   ⚠️  Unele extensii lipsesc!"
fi

echo ""

# 4. Verifică log-urile recente
echo "4. Ultimele erori din log-uri (GA Sync):"
tail -n 100 storage/logs/laravel.log | grep -i "ga sync" -A 5 | tail -n 20 || echo "   Nu s-au găsit erori recente"

echo ""

# 5. Verifică conectivitatea
echo "5. Verifică conectivitatea la Google APIs..."
curl -s -o /dev/null -w "   OAuth2 API: %{http_code}\n" https://oauth2.googleapis.com/token
curl -s -o /dev/null -w "   Analytics API: %{http_code}\n" https://analyticsdata.googleapis.com

echo ""

# 6. Test rapid în PHP
echo "6. Test rapid configurație..."
php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo '   Property ID: ' . config('google-analytics.property_id') . PHP_EOL;
echo '   Credentials Path: ' . config('google-analytics.credentials_path') . PHP_EOL;
echo '   File Exists: ' . (file_exists(config('google-analytics.credentials_path')) ? 'Yes' : 'No') . PHP_EOL;
echo '   File Readable: ' . (file_exists(config('google-analytics.credentials_path')) && is_readable(config('google-analytics.credentials_path')) ? 'Yes' : 'No') . PHP_EOL;
"

echo ""
echo "=========================================="
echo "Diagnosticare completă!"
echo "=========================================="
echo ""
echo "📋 Pași următori:"
echo "   1. Dacă fișierul lipsește: pune service-account-credentials.json în storage/app/google-analytics/"
echo "   2. Dacă permisiunile sunt greșite, rulează:"
echo "      chmod 600 storage/app/google-analytics/service-account-credentials.json"
echo "      chown www-data:www-data storage/app/google-analytics/service-account-credentials.json"
echo "   3. Verifică .env: GA_PROPERTY_ID trebuie setat (ex: 281678807)"
echo ""
