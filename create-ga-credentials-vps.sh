#!/bin/bash

# Script pentru creare manuală fișier credențiale GA pe VPS
# Rulează: bash create-ga-credentials-vps.sh

echo "=========================================="
echo "Creare Manuală Credențiale Google Analytics"
echo "=========================================="
echo ""

# Verifică dacă suntem în directorul corect
if [ ! -f "artisan" ]; then
    echo "❌ Eroare: Rulează acest script din directorul rădăcină al aplicației Laravel"
    echo "   cd /var/www/volta-dashboard"
    exit 1
fi

# Creează directorul
echo "📁 Creează directorul..."
mkdir -p storage/app/google-analytics
chown -R www-data:www-data storage/app/google-analytics
chmod -R 775 storage/app/google-analytics

# Verifică dacă fișierul există deja
if [ -f "storage/app/google-analytics/service-account-credentials.json" ]; then
    echo "⚠️  Fișierul există deja!"
    read -p "Vrei să-l suprascrii? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Anulat."
        exit 0
    fi
    rm storage/app/google-analytics/service-account-credentials.json
fi

echo ""
echo "📝 Deschide editorul nano pentru a crea fișierul..."
echo "   După ce lipești conținutul JSON:"
echo "   - Salvează: Ctrl+O, apoi Enter"
echo "   - Ieși: Ctrl+X"
echo ""
read -p "Apasă Enter pentru a continua..."

# Deschide nano
nano storage/app/google-analytics/service-account-credentials.json

# Verifică dacă fișierul a fost creat
if [ ! -f "storage/app/google-analytics/service-account-credentials.json" ]; then
    echo "❌ Fișierul nu a fost creat!"
    exit 1
fi

# Verifică dacă JSON-ul este valid
if command -v python3 &> /dev/null; then
    if python3 -m json.tool storage/app/google-analytics/service-account-credentials.json > /dev/null 2>&1; then
        echo "✅ JSON valid"
    else
        echo "⚠️  JSON-ul pare invalid. Verifică manual:"
        echo "   python3 -m json.tool storage/app/google-analytics/service-account-credentials.json"
    fi
fi

# Setează permisiunile
echo ""
echo "🔐 Setează permisiunile..."
chmod 600 storage/app/google-analytics/service-account-credentials.json
chown www-data:www-data storage/app/google-analytics/service-account-credentials.json

# Verifică
echo ""
echo "✅ Fișier creat!"
echo ""
echo "📋 Verificare:"
ls -lh storage/app/google-analytics/service-account-credentials.json
echo ""
echo "📝 Următorii pași:"
echo "   1. Verifică că .env conține: GA_PROPERTY_ID=281678807"
echo "   2. Adaugă service account-ul în Google Analytics (vezi CREATE-GA-CREDENTIALS-MANUAL.md)"
echo "   3. Rulează: php artisan config:clear"
echo "   4. Testează sincronizarea din interfață"
echo ""
