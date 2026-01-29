# Comenzi Directe pentru Debug GA4 Sync pe VPS

## 1. Verifică Fișierul de Credențiale

```bash
cd /var/www/volta-dashboard

# Verifică existența
ls -la storage/app/google-analytics/service-account-credentials.json

# Verifică permisiunile
stat storage/app/google-analytics/service-account-credentials.json

# Verifică conținutul (primele linii)
head -3 storage/app/google-analytics/service-account-credentials.json

# Verifică că JSON-ul este valid
python3 -m json.tool storage/app/google-analytics/service-account-credentials.json > /dev/null && echo "JSON valid" || echo "JSON invalid"
```

## 2. Verifică Configurația .env

```bash
cat .env | grep GA_PROPERTY_ID
cat .env | grep APP_DEBUG
```

## 3. Verifică Log-urile pentru Eroarea Exactă

```bash
# Ultimele erori GA Sync
tail -n 200 storage/logs/laravel.log | grep -A 30 "GA Sync error"

# Sau toate erorile recente GA
tail -n 100 storage/logs/laravel.log | grep -i "ga\|google\|analytics" -A 10

# Ultimele 50 de linii din log
tail -n 50 storage/logs/laravel.log
```

## 4. Testează Direct în Tinker

```bash
php artisan tinker
```

Apoi în tinker, rulează pas cu pas:

```php
// Pas 1: Verifică configurația
config('google-analytics.property_id');
config('google-analytics.credentials_path');

// Pas 2: Verifică existența fișierului
file_exists(config('google-analytics.credentials_path'));

// Pas 3: Verifică dacă poate citi
is_readable(config('google-analytics.credentials_path'));

// Pas 4: Încearcă să încarce serviciul
$service = app(\App\Services\GoogleAnalyticsService::class);

// Pas 5: Încearcă să extragă date (va arăta eroarea exactă)
try {
    $data = $service->fetchTrafficData('2026-01-01', '2026-01-31');
    echo "Success! Keys: " . implode(', ', array_keys($data)) . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace: " . substr($e->getTraceAsString(), 0, 500) . "\n";
}

exit
```

## 5. Verifică Permisiunile și Corectează dacă E Nevoie

```bash
# Verifică owner și permisiuni
ls -la storage/app/google-analytics/

# Corectează permisiunile
chown www-data:www-data storage/app/google-analytics/service-account-credentials.json
chmod 600 storage/app/google-analytics/service-account-credentials.json

# Verifică din nou
ls -la storage/app/google-analytics/service-account-credentials.json
```

## 6. Verifică PHP Extensions

```bash
php -m | grep -E "openssl|curl|json"
```

## 7. Activează APP_DEBUG Temporar pentru Mai Multe Detalii

```bash
# Editează .env
nano .env

# Schimbă linia:
APP_DEBUG=true

# Salvează (Ctrl+O, Enter, Ctrl+X)

# Clear cache
php artisan config:clear
php artisan cache:clear
```

Apoi încearcă din nou sincronizarea din interfață - vei vedea mai multe detalii în răspunsul JSON.

**⚠️ IMPORTANT:** După debugging, schimbă înapoi:
```bash
nano .env
# Schimbă:
APP_DEBUG=false
php artisan config:clear
```

## 8. Verifică Conectivitatea la Google

```bash
curl -I https://oauth2.googleapis.com/token
curl -I https://analyticsdata.googleapis.com
```

## 9. Testează API-ul Direct (cu curl)

```bash
# Obține token CSRF și session din browser (inspect element -> Network -> Headers)
# Apoi rulează:
curl -X POST "http://stats.volta.md/api/ga/sync?start_date=2026-01-01&end_date=2026-01-31" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: YOUR_CSRF_TOKEN_HERE" \
  -b "laravel_session=YOUR_SESSION_COOKIE_HERE" \
  -v
```

## 10. Verifică Dacă Service Account Este în Google Analytics

1. Accesează: https://analytics.google.com/
2. Mergi la **Admin** (⚙️)
3. Selectează property-ul (ID: 281678807)
4. În **Property**, click pe **Property access management**
5. Verifică că există: `ga-data-fetcher@micro-verbena-476807-j9.iam.gserviceaccount.com`
6. Verifică că are rolul **Viewer**

## Comenzi Rapide de Verificare (Copy-Paste)

```bash
cd /var/www/volta-dashboard && \
echo "=== Fișier credențiale ===" && \
ls -la storage/app/google-analytics/service-account-credentials.json && \
echo "" && \
echo "=== Configurație .env ===" && \
grep "GA_PROPERTY_ID\|APP_DEBUG" .env && \
echo "" && \
echo "=== Ultimele erori GA ===" && \
tail -n 100 storage/logs/laravel.log | grep -i "ga sync error" -A 10 | tail -n 20
```

## Soluții Rapide pentru Probleme Comune

### Dacă fișierul nu există:
```bash
mkdir -p storage/app/google-analytics
# Apoi creează manual cu nano (vezi CREATE-GA-CREDENTIALS-MANUAL.md)
nano storage/app/google-analytics/service-account-credentials.json
# Lipește conținutul, salvează (Ctrl+O, Enter, Ctrl+X)
chmod 600 storage/app/google-analytics/service-account-credentials.json
chown www-data:www-data storage/app/google-analytics/service-account-credentials.json
```

### Dacă permisiunile sunt greșite:
```bash
chown -R www-data:www-data storage/app/google-analytics/
chmod 600 storage/app/google-analytics/service-account-credentials.json
```

### Dacă GA_PROPERTY_ID lipsește:
```bash
echo "GA_PROPERTY_ID=281678807" >> .env
php artisan config:clear
```

### Dacă apare eroare SSL:
```bash
echo "GA_SSL_VERIFY=false" >> .env
php artisan config:clear
```
