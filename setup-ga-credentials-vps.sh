#!/bin/bash

# Script pentru setup credențiale Google Analytics pe VPS
# Rulează: bash setup-ga-credentials-vps.sh

echo "=========================================="
echo "Setup Credențiale Google Analytics pe VPS"
echo "=========================================="
echo ""

# Verifică dacă suntem în directorul corect
if [ ! -f "artisan" ]; then
    echo "❌ Eroare: Rulează acest script din directorul rădăcină al aplicației Laravel"
    echo "   cd /var/www/volta-dashboard"
    exit 1
fi

# Creează directorul
echo "📁 Creează directorul pentru credențiale..."
mkdir -p storage/app/google-analytics

# Setează permisiunile
echo "🔐 Setează permisiunile..."
chown -R www-data:www-data storage/app/google-analytics
chmod -R 775 storage/app/google-analytics

# Verifică dacă fișierul există
if [ -f "storage/app/google-analytics/service-account-credentials.json" ]; then
    echo "✅ Fișierul de credențiale există deja"
    chmod 600 storage/app/google-analytics/service-account-credentials.json
    chown www-data:www-data storage/app/google-analytics/service-account-credentials.json
else
    echo "⚠️  Fișierul service-account-credentials.json NU există!"
    echo ""
    echo "📋 Pași următori:"
    echo "   1. Obține fișierul de credențiale din Google Cloud Console"
    echo "   2. Transferă-l pe VPS:"
    echo "      scp service-account-credentials.json root@IP_VPS:/var/www/volta-dashboard/storage/app/google-analytics/"
    echo "   3. Setează permisiunile:"
    echo "      chmod 600 storage/app/google-analytics/service-account-credentials.json"
    echo "      chown www-data:www-data storage/app/google-analytics/service-account-credentials.json"
    echo ""
    echo "   Vezi SETUP-GA-CREDENTIALS-VPS.md pentru instrucțiuni detaliate"
fi

# Verifică configurația .env
echo ""
echo "🔍 Verifică configurația .env..."
if grep -q "GA_PROPERTY_ID" .env; then
    echo "✅ GA_PROPERTY_ID este setat în .env"
    grep "GA_PROPERTY_ID" .env
else
    echo "⚠️  GA_PROPERTY_ID nu este setat în .env"
    echo "   Adaugă în .env: GA_PROPERTY_ID=281678807"
fi

# Clear cache
echo ""
echo "🧹 Clear cache Laravel..."
php artisan config:clear
php artisan cache:clear

echo ""
echo "=========================================="
echo "✅ Setup complet!"
echo "=========================================="
echo ""
echo "📝 Verifică:"
echo "   ls -la storage/app/google-analytics/"
echo ""
