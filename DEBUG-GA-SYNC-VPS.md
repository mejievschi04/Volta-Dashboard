# Debug Sincronizare GA4 pe VPS

## Verificare Rapidă

### 1. Verifică Log-urile Laravel

```bash
cd /var/www/volta-dashboard
tail -n 200 storage/logs/laravel.log | grep -A 30 "GA Sync"
```

Sau pentru ultimele erori:
```bash
tail -n 100 storage/logs/laravel.log | grep -i "ga\|google\|analytics" -A 10
```

### 2. Verifică Fișierul de Credențiale

```bash
# Verifică existența
ls -la storage/app/google-analytics/service-account-credentials.json

# Verifică permisiunile (ar trebui să fie -rw-------)
stat storage/app/google-analytics/service-account-credentials.json

# Verifică conținutul (primele linii)
head -3 storage/app/google-analytics/service-account-credentials.json

# Verifică că JSON-ul este valid
python3 -m json.tool storage/app/google-analytics/service-account-credentials.json > /dev/null && echo "✅ JSON valid" || echo "❌ JSON invalid"
```

### 3. Verifică Configurația .env

```bash
cat .env | grep GA_PROPERTY_ID
cat .env | grep APP_DEBUG
```

Ar trebui să vezi:
```
GA_PROPERTY_ID=281678807
APP_DEBUG=false
```

### 4. Testează Direct în Tinker

```bash
php artisan tinker
```

În tinker, rulează:
```php
// Test 1: Verifică configurația
config('google-analytics.property_id');
config('google-analytics.credentials_path');

// Test 2: Verifică existența fișierului
file_exists(config('google-analytics.credentials_path'));

// Test 3: Verifică dacă poate citi fișierul
is_readable(config('google-analytics.credentials_path'));

// Test 4: Încearcă să încarce serviciul
$service = app(\App\Services\GoogleAnalyticsService::class);

// Test 5: Încearcă să obțină token (va arăta eroarea exactă)
try {
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('getAccessToken');
    $method->setAccessible(true);
    $token = $method->invoke($service);
    echo "Token obținut: " . substr($token, 0, 20) . "...\n";
} catch (Exception $e) {
    echo "Eroare: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Test 6: Încearcă să extragă date
try {
    $data = $service->fetchTrafficData('2026-01-01', '2026-01-31');
    echo "Date extrase cu succes!\n";
    print_r(array_keys($data));
} catch (Exception $e) {
    echo "Eroare la extragere date: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

exit
```

### 5. Testează API-ul Direct cu curl

```bash
# Obține token CSRF din browser (inspect element -> Network -> Headers)
# Apoi testează:
curl -X POST "http://stats.volta.md/api/ga/sync?start_date=2026-01-01&end_date=2026-01-31" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: YOUR_CSRF_TOKEN" \
  -b "laravel_session=YOUR_SESSION_COOKIE" \
  -v
```

### 6. Verifică Permisiunile Storage

```bash
ls -la storage/app/google-analytics/
chown -R www-data:www-data storage/app/google-analytics/
chmod 600 storage/app/google-analytics/service-account-credentials.json
```

### 7. Verifică PHP Extensions

```bash
php -m | grep -E "openssl|curl|json"
```

Ar trebui să vezi:
- openssl
- curl (sau libcurl)
- json

### 8. Verifică Conectivitatea la Google

```bash
curl -I https://oauth2.googleapis.com/token
curl -I https://analyticsdata.googleapis.com
```

### 9. Activează APP_DEBUG Temporar (pentru mai multe detalii)

```bash
nano .env
# Schimbă:
APP_DEBUG=true

# Clear cache
php artisan config:clear
php artisan cache:clear
```

**⚠️ IMPORTANT:** După debugging, schimbă înapoi la `APP_DEBUG=false`!

### 10. Verifică Service Account în Google Analytics

1. Accesează [Google Analytics](https://analytics.google.com/)
2. Mergi la **Admin** (⚙️)
3. Selectează property-ul (ID: 281678807)
4. În **Property**, click pe **Property access management**
5. Verifică că există: `ga-data-fetcher@micro-verbena-476807-j9.iam.gserviceaccount.com`
6. Verifică că are rolul **Viewer** sau **Analyst**

## Erori Comune și Soluții

### Eroare: "Fișierul de credențiale nu există"
```bash
# Verifică calea
ls -la storage/app/google-analytics/service-account-credentials.json

# Dacă nu există, creează-l (vezi CREATE-GA-CREDENTIALS-MANUAL.md)
```

### Eroare: "Permission denied"
```bash
chown www-data:www-data storage/app/google-analytics/service-account-credentials.json
chmod 600 storage/app/google-analytics/service-account-credentials.json
```

### Eroare: "Property ID nu este configurat"
```bash
# Verifică .env
cat .env | grep GA_PROPERTY_ID

# Dacă nu există, adaugă:
echo "GA_PROPERTY_ID=281678807" >> .env
php artisan config:clear
```

### Eroare: "Eroare la obținerea token-ului"
- Verifică că OpenSSL este activat: `php -m | grep openssl`
- Verifică că fișierul JSON este valid
- Verifică că private_key este corect formatat în JSON

### Eroare: "Eroare cURL" sau "SSL verify failed"
```bash
# Adaugă în .env:
echo "GA_SSL_VERIFY=false" >> .env
php artisan config:clear
```

### Eroare: "403 Forbidden" sau "Access denied"
- Verifică că service account-ul este adăugat în Google Analytics
- Verifică că are rolul corect (Viewer sau Analyst)
- Verifică că Property ID este corect

## Export Log-uri pentru Analiză

```bash
# Export ultimele 500 de linii cu erori GA
tail -n 500 storage/logs/laravel.log | grep -i "ga\|google\|analytics" > /tmp/ga-errors.log

# Verifică fișierul
cat /tmp/ga-errors.log
```

## Contact pentru Suport

Dacă problema persistă, trimite:
1. Output-ul din `tail -n 200 storage/logs/laravel.log | grep -A 30 "GA Sync"`
2. Output-ul din testele tinker
3. Rezultatul verificărilor de mai sus
